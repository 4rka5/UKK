<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectCard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Get cards assigned to this user
        $myTasks = ManagementProjectCard::with(['board.project', 'creator'])
            ->whereHas('assignees', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->orWhere('created_by', $userId)
            ->orderByDesc('id')
            ->get();

        // Group by status for statistics
        $stats = [
            'total' => $myTasks->count(),
            'todo' => $myTasks->whereIn('status', ['backlog', 'todo'])->count(),
            'in_progress' => $myTasks->where('status', 'in_progress')->count(),
            'review' => $myTasks->where('status', 'code_review')->count(),
            'done' => $myTasks->where('status', 'done')->count(),
        ];

        return view('member.dashboard', compact('myTasks', 'stats'));
    }
}
