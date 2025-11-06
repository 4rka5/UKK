<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectCard;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $leadId = Auth::id();

        $baseQuery = ManagementProjectCard::with(['board.project'])
            ->whereHas('board.project', function ($q) use ($leadId) {
                $q->where('created_by', $leadId);
            });

        $todo = (clone $baseQuery)->whereIn('status', ['backlog','todo'])->latest('id')->limit(20)->get();
        $inProgress = (clone $baseQuery)->where('status', 'in_progress')->latest('id')->limit(20)->get();
        $review = (clone $baseQuery)->where('status', 'code_review')->latest('id')->limit(20)->get();
        $done = (clone $baseQuery)->where('status', 'done')->latest('id')->limit(20)->get();

        return view('lead.dashboard', compact('todo','inProgress','review','done'));
    }
}

