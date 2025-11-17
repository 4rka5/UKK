<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CardController extends Controller
{
    public function index(Request $request, $boardId)
    {
        $user = $request->user();
        $query = ManagementProjectCard::where('board_id', $boardId)
            ->with(['assignedTo', 'creator', 'subtasks', 'board.project']);

        // ROLE-BASED FILTERING
        // Member (designer/developer) hanya bisa lihat task yang di-assign ke mereka
        if (in_array($user->role, ['designer', 'developer'])) {
            $query->where('assigned_to', $user->id);
        }
        // Team lead/admin bisa lihat semua task

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $cards = $query->get();

        return response()->json([
            'success' => true,
            'data' => $cards,
            'meta' => [
                'total' => $cards->count(),
                'role' => $user->role
            ]
        ]);
    }

    public function store(Request $request, $boardId)
    {
        $user = $request->user();

        // Only team lead and admin can create tasks
        if (!in_array($user->role, ['team_lead', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only team lead can create tasks'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'card_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:todo,in_progress,review,done',
            'estimated_hours' => 'nullable|numeric',
            'assigned_to' => 'nullable|exists:users,id' // ID member yang akan dikerjakan
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if assigned user is already a project member
        if ($request->assigned_to) {
            $board = \App\Models\ManagementProjectBoard::findOrFail($boardId);
            $project = $board->project;
            
            $isProjectMember = \App\Models\ProjectMember::where('project_id', $project->id)
                ->where('user_id', $request->assigned_to)
                ->exists();

            if ($isProjectMember) {
                $assignedUser = \App\Models\User::find($request->assigned_to);
                return response()->json([
                    'success' => false,
                    'message' => "User {$assignedUser->username} sudah bergabung di project {$project->project_name}. User yang sudah bergabung di project tidak dapat diberikan tugas."
                ], 422);
            }
        }

        $card = ManagementProjectCard::create([
            'board_id' => $boardId,
            'card_title' => $request->card_title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority ?? 'medium',
            'status' => $request->status ?? 'todo',
            'estimated_hours' => $request->estimated_hours,
            'created_by' => $user->id,
            'assigned_to' => $request->assigned_to // Assign ke member
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task created and assigned successfully',
            'data' => $card->load(['assignedTo', 'creator', 'subtasks'])
        ], 201);
    }

    public function show($boardId, $id)
    {
        $card = ManagementProjectCard::where('board_id', $boardId)
            ->with(['assignees', 'subtasks', 'comments.user', 'board.project', 'creator'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $card
        ]);
    }

    public function update(Request $request, $boardId, $id)
    {
        $user = $request->user();
        $card = ManagementProjectCard::where('board_id', $boardId)->findOrFail($id);

        // Member hanya bisa update status task mereka sendiri
        if (in_array($user->role, ['designer', 'developer'])) {
            if ($card->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only update your own assigned tasks'
                ], 403);
            }

            // Member hanya bisa update status dan actual_hours
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|in:todo,in_progress,done',
                'actual_hours' => 'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $card->update($request->only(['status', 'actual_hours']));

        } else {
            // Team lead bisa update semua field
            $validator = Validator::make($request->all(), [
                'card_title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'nullable|date',
                'priority' => 'nullable|in:low,medium,high',
                'status' => 'nullable|in:todo,in_progress,review,done',
                'estimated_hours' => 'nullable|numeric',
                'actual_hours' => 'nullable|numeric',
                'assigned_to' => 'nullable|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if assigned user is already a project member (when updating assignment)
            if ($request->has('assigned_to') && $request->assigned_to != $card->assigned_to) {
                $board = $card->board;
                $project = $board->project;
                
                $isProjectMember = \App\Models\ProjectMember::where('project_id', $project->id)
                    ->where('user_id', $request->assigned_to)
                    ->exists();

                if ($isProjectMember) {
                    $assignedUser = \App\Models\User::find($request->assigned_to);
                    return response()->json([
                        'success' => false,
                        'message' => "User {$assignedUser->username} sudah bergabung di project {$project->project_name}. User yang sudah bergabung di project tidak dapat diberikan tugas."
                    ], 422);
                }
            }

            $card->update($request->only([
                'card_title', 'description', 'due_date', 'priority',
                'status', 'estimated_hours', 'actual_hours', 'assigned_to'
            ]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Card updated successfully',
            'data' => $card->load(['assignedTo', 'creator', 'subtasks'])
        ]);
    }

    public function destroy($boardId, $id)
    {
        $card = ManagementProjectCard::where('board_id', $boardId)->findOrFail($id);
        $card->delete();

        return response()->json([
            'success' => true,
            'message' => 'Card deleted successfully'
        ]);
    }

    public function assignUser(Request $request, $cardId)
    {
        $card = ManagementProjectCard::findOrFail($cardId);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is already a project member
        $board = $card->board;
        $project = $board->project;
        
        $isProjectMember = \App\Models\ProjectMember::where('project_id', $project->id)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($isProjectMember) {
            $user = \App\Models\User::find($request->user_id);
            return response()->json([
                'success' => false,
                'message' => "User {$user->username} sudah bergabung di project {$project->project_name}. User yang sudah bergabung di project tidak dapat diberikan tugas."
            ], 422);
        }

        $card->assignees()->syncWithoutDetaching([$request->user_id => [
            'assigned_at' => now(),
            'assignment_status' => 'active'
        ]]);

        return response()->json([
            'success' => true,
            'message' => 'User assigned successfully',
            'data' => $card->load('assignees')
        ]);
    }

    public function unassignUser($cardId, $userId)
    {
        $card = ManagementProjectCard::findOrFail($cardId);
        $card->assignees()->detach($userId);

        return response()->json([
            'success' => true,
            'message' => 'User unassigned successfully'
        ]);
    }

    public function updateStatus(Request $request, $cardId)
    {
        $user = $request->user();
        $card = ManagementProjectCard::findOrFail($cardId);

        // Member hanya bisa update status task mereka sendiri
        if (in_array($user->role, ['designer', 'developer'])) {
            if ($card->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only update your own assigned tasks'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:todo,in_progress,done'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $card->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully',
            'data' => $card->load(['assignedTo', 'creator'])
        ]);
    }

    // Get single card without board_id (untuk Flutter)
    public function showCard(Request $request, $cardId)
    {
        $user = $request->user();
        $card = ManagementProjectCard::with([
            'board.project',
            'assignedTo',
            'creator',
            'subtasks',
            'comments.user'
        ])->findOrFail($cardId);

        // Member hanya bisa lihat task mereka sendiri
        if (in_array($user->role, ['designer', 'developer'])) {
            if ($card->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view your own assigned tasks'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $card
        ]);
    }

    // Get my assigned tasks (untuk member)
    public function myTasks(Request $request)
    {
        $user = $request->user();

        $query = ManagementProjectCard::where('assigned_to', $user->id)
            ->with(['board.project', 'creator', 'subtasks']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $cards = $query->orderBy('due_date', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $cards,
            'meta' => [
                'total' => $cards->count(),
                'todo' => $cards->where('status', 'todo')->count(),
                'in_progress' => $cards->where('status', 'in_progress')->count(),
                'done' => $cards->where('status', 'done')->count()
            ]
        ]);
    }
}
