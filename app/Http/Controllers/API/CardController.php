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
        $query = ManagementProjectCard::where('board_id', $boardId)
            ->with(['assignees', 'subtasks', 'board.project']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by assigned user
        if ($request->has('assigned_to_me') && $request->assigned_to_me) {
            $query->whereHas('assignees', function($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }

        $cards = $query->get();

        return response()->json([
            'success' => true,
            'data' => $cards
        ]);
    }

    public function store(Request $request, $boardId)
    {
        $validator = Validator::make($request->all(), [
            'card_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:backlog,todo,in_progress,code_review,testing,done',
            'estimated_hours' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $card = ManagementProjectCard::create([
            'board_id' => $boardId,
            'card_title' => $request->card_title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority ?? 'medium',
            'status' => $request->status ?? 'backlog',
            'estimated_hours' => $request->estimated_hours,
            'created_by' => $request->user()->id
        ]);

        // Assign users if provided
        if ($request->has('assignees')) {
            $card->assignees()->attach($request->assignees);
        }

        return response()->json([
            'success' => true,
            'message' => 'Card created successfully',
            'data' => $card->load(['assignees', 'subtasks'])
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
        $card = ManagementProjectCard::where('board_id', $boardId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'card_title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:backlog,todo,in_progress,code_review,testing,done',
            'estimated_hours' => 'nullable|numeric',
            'actual_hours' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $card->update($request->only([
            'card_title', 'description', 'due_date', 'priority', 
            'status', 'estimated_hours', 'actual_hours'
        ]));

        // Update assignees if provided
        if ($request->has('assignees')) {
            $card->assignees()->sync($request->assignees);
        }

        return response()->json([
            'success' => true,
            'message' => 'Card updated successfully',
            'data' => $card->load(['assignees', 'subtasks'])
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
        $card = ManagementProjectCard::findOrFail($cardId);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:backlog,todo,in_progress,code_review,testing,done'
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
            'message' => 'Card status updated successfully',
            'data' => $card
        ]);
    }
}