<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManagementProjectComment;
use Illuminate\Support\Facades\Validator;

class CommentsController extends Controller
{
    public function index($cardId)
    {
        $comments = ManagementProjectComment::where('card_id', $cardId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    public function store(Request $request, $cardId)
    {
        $validator = Validator::make($request->all(), [
            'comment_text' => 'required|string',
            'subtask_id' => 'nullable|exists:subtasks,id',
            'comment_type' => 'nullable|in:card,subtask'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $comment = ManagementProjectComment::create([
            'card_id' => $cardId,
            'subtask_id' => $request->subtask_id,
            'user_id' => $request->user()->id,
            'comment_text' => $request->comment_text,
            'comment_type' => $request->comment_type ?? 'card'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully',
            'data' => $comment->load('user')
        ], 201);
    }

    public function show($cardId, $id)
    {
        $comment = ManagementProjectComment::where('card_id', $cardId)
            ->with('user')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $comment
        ]);
    }

    public function update(Request $request, $cardId, $id)
    {
        $comment = ManagementProjectComment::where('card_id', $cardId)->findOrFail($id);

        // Check if user owns the comment
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'comment_text' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $comment->update(['comment_text' => $request->comment_text]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => $comment->load('user')
        ]);
    }

    public function destroy(Request $request, $cardId, $id)
    {
        $comment = ManagementProjectComment::where('card_id', $cardId)->findOrFail($id);

        // Check if user owns the comment
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }
}
