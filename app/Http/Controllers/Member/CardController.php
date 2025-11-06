<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public function show(ManagementProjectCard $card)
    {
        // Check if user is assigned to this card or created it
        $userId = Auth::id();
        $isAssigned = $card->assignees()->where('users.id', $userId)->exists();
        $isCreator = $card->created_by === $userId;

        if (!$isAssigned && !$isCreator) {
            abort(403, 'Anda tidak memiliki akses ke card ini.');
        }

        $card->load(['board.project', 'creator', 'assignees', 'subtasks', 'comments.user']);

        return view('member.cards.show', compact('card'));
    }

    public function updateStatus(Request $request, ManagementProjectCard $card)
    {
        // Check access
        $userId = Auth::id();
        $isAssigned = $card->assignees()->where('users.id', $userId)->exists();

        if (!$isAssigned && $card->created_by !== $userId) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'in:backlog,todo,in_progress,code_review,testing,done']
        ]);

        $card->update(['status' => $request->status]);

        return back()->with('status', 'Status card diperbarui.');
    }
}
