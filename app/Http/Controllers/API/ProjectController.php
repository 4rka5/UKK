<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ROLE-BASED PROJECT ACCESS
        if ($user->role === 'admin') {
            // Admin melihat semua project yang dia buat
            $projects = Project::where('created_by', $user->id)
                ->with(['owner', 'assignedTo'])
                ->latest()
                ->get();
        } elseif ($user->role === 'team_lead') {
            // Team Lead hanya melihat project yang di-assign ke mereka oleh admin
            $projects = Project::where('assigned_to', $user->id)
                ->with(['owner', 'assignedTo'])
                ->latest()
                ->get();
        } else {
            // Member melihat project dari card yang di-assign ke mereka
            $projects = Project::whereHas('boards.cards', function($q) use ($user) {
                $q->where('assigned_to', $user->id);
            })->with(['owner', 'assignedTo'])->latest()->get();
        }

        return response()->json([
            'success' => true,
            'data' => $projects,
            'meta' => [
                'role' => $user->role,
                'total' => $projects->count()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Only admin can create projects
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can create projects'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id' // ID team lead
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validasi bahwa assigned_to adalah team_lead
        if ($request->assigned_to) {
            $teamLead = User::find($request->assigned_to);
            if ($teamLead->role !== 'team_lead') {
                return response()->json([
                    'success' => false,
                    'message' => 'Project can only be assigned to team lead'
                ], 422);
            }
        }

        $project = Project::create([
            'project_name' => $request->project_name,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'created_by' => $user->id,
            'assigned_to' => $request->assigned_to
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project created and assigned successfully',
            'data' => $project->load(['owner', 'assignedTo'])
        ], 201);
    }

    public function show($id)
    {
        $project = Project::with(['owner', 'boards.cards'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $project
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $project = Project::findOrFail($id);

        // Only admin (creator) can update projects
        if ($user->role !== 'admin' || $project->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin who created the project can update it'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'project_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validasi bahwa assigned_to adalah team_lead
        if ($request->has('assigned_to') && $request->assigned_to) {
            $teamLead = User::find($request->assigned_to);
            if ($teamLead->role !== 'team_lead') {
                return response()->json([
                    'success' => false,
                    'message' => 'Project can only be assigned to team lead'
                ], 422);
            }
        }

        $project->update($request->only(['project_name', 'description', 'deadline', 'assigned_to']));

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'data' => $project->load(['owner', 'assignedTo'])
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Check authorization
        if ($request->user()->role !== 'admin' && $project->created_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully'
        ]);
    }
}
