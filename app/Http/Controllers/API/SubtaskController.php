<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ManagementProjectSubtask;
use Illuminate\Support\Facades\Validator;

class SubtaskController extends Controller
{
    public function index($cardId)
    {
        $subtasks = ManagementProjectSubtask::where('card_id', $cardId)->get();

        return response()->json([
            'success' => true,
            'data' => $subtasks
        ]);
    }

    public function store(Request $request, $cardId)
    {
        $validator = Validator::make($request->all(), [
            'subtask_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,done',
            'estimated_hours' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $subtask = ManagementProjectSubtask::create([
            'card_id' => $cardId,
            'subtask_title' => $request->subtask_title,
            'description' => $request->description,
            'status' => $request->status ?? 'todo',
            'estimated_hours' => $request->estimated_hours
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subtask created successfully',
            'data' => $subtask
        ], 201);
    }

    public function show($cardId, $id)
    {
        $subtask = ManagementProjectSubtask::where('card_id', $cardId)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $subtask
        ]);
    }

    public function update(Request $request, $cardId, $id)
    {
        $subtask = ManagementProjectSubtask::where('card_id', $cardId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'subtask_title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,done',
            'estimated_hours' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $subtask->update($request->only([
            'subtask_title', 'description', 'status', 'estimated_hours'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Subtask updated successfully',
            'data' => $subtask
        ]);
    }

    public function destroy($cardId, $id)
    {
        $subtask = ManagementProjectSubtask::where('card_id', $cardId)->findOrFail($id);
        $subtask->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subtask deleted successfully'
        ]);
    }
}
