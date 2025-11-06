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
        
        // Admin sees all, Team Lead sees their projects, Members see assigned projects
        if ($user->role === 'admin') {
            $projects = Project::with('owner')->latest()->get();
        } elseif ($user->role === 'team_lead') {
            $projects = Project::where('created_by', $user->id)->with('owner')->latest()->get();
        } else {
            // Get projects where user is assigned to cards
            $projects = Project::whereHas('boards.cards.assignees', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->with('owner')->latest()->get();
        }

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $project = Project::create([
            'project_name' => $request->project_name,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'created_by' => $request->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'data' => $project->load('owner')
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
        $project = Project::findOrFail($id);
        
        // Check authorization
        if ($request->user()->role !== 'admin' && $project->created_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'project_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $project->update($request->only(['project_name', 'description', 'deadline']));

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'data' => $project->load('owner')
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
