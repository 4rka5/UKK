<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectBoard;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoardController extends Controller
{
    public function index($projectId)
    {
        $boards = ManagementProjectBoard::where('project_id', $projectId)
            ->with('cards')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $boards
        ]);
    }

    public function store(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::findOrFail($projectId);

        // Check if user is team lead member of this project
        $isTeamLead = \App\Models\ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->where('role', 'team_lead')
            ->exists();

        if (!$isTeamLead) {
            return response()->json([
                'success' => false,
                'message' => 'Only team lead member of this project can create boards'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'board_name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $board = ManagementProjectBoard::create([
            'project_id' => $projectId,
            'board_name' => $request->board_name,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Board created successfully',
            'data' => $board
        ], 201);
    }

    public function show($projectId, $id)
    {
        $board = ManagementProjectBoard::where('project_id', $projectId)
            ->with('cards.assignees', 'cards.subtasks')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $board
        ]);
    }

    public function update(Request $request, $projectId, $id)
    {
        $user = $request->user();
        $project = Project::findOrFail($projectId);
        $board = ManagementProjectBoard::where('project_id', $projectId)->findOrFail($id);

        // Check if user is team lead member of this project
        $isTeamLead = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->where('role', 'team_lead')
            ->exists();

        if (!$isTeamLead) {
            return response()->json([
                'success' => false,
                'message' => 'Only team lead member of this project can update boards'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'board_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $board->update($request->only(['board_name', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'Board updated successfully',
            'data' => $board
        ]);
    }

    public function destroy(Request $request, $projectId, $id)
    {
        $user = $request->user();
        $project = Project::findOrFail($projectId);
        $board = ManagementProjectBoard::where('project_id', $projectId)->findOrFail($id);

        // Check if user is team lead member of this project
        $isTeamLead = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->where('role', 'team_lead')
            ->exists();

        if (!$isTeamLead) {
            return response()->json([
                'success' => false,
                'message' => 'Only team lead member of this project can delete boards'
            ], 403);
        }

        $board->delete();

        return response()->json([
            'success' => true,
            'message' => 'Board deleted successfully'
        ]);
    }
}
