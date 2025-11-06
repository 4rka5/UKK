<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();

        return view('admin.dashboard', [
            'userCount' => $users->count(),
            'projectCount' => Project::count(),
            'projects' => Project::with('owner')->latest()->take(10)->get(),
            'users' => $users,
        ]);
    }
}
