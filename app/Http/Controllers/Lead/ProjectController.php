<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects created by team lead
     */
    public function index()
    {
        $projects = Project::where('created_by', Auth::id())
            ->with(['owner', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Statistics
        $stats = [
            'total' => Project::where('created_by', Auth::id())->count(),
            'pending' => Project::where('created_by', Auth::id())->where('status', 'pending')->count(),
            'approved' => Project::where('created_by', Auth::id())->where('status', 'approved')->count(),
            'rejected' => Project::where('created_by', Auth::id())->where('status', 'rejected')->count(),
        ];

        return view('lead.projects.index', compact('projects', 'stats'));
    }

    /**
     * Show the form for creating a new project (request approval)
     */
    public function create()
    {
        return view('lead.projects.create');
    }

    /**
     * Store and submit project for approval (no draft, directly pending)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date|after:today',
        ]);

        $project = Project::create([
            'project_name' => $validated['project_name'],
            'description' => $validated['description'],
            'deadline' => $validated['deadline'],
            'created_by' => Auth::id(),
            'status' => 'pending', // Langsung pending, tidak ada draft
        ]);

        // Send notification to all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'project_submitted',
                'title' => 'Project Baru Menunggu Approval',
                'message' => Auth::user()->name . ' mengajukan project "' . $project->project_name . '" untuk approval.',
                'related_type' => 'Project',
                'related_id' => $project->id,
                'is_read' => false,
            ]);
        }

        return redirect()->route('lead.projects.index')
            ->with('success', 'Project berhasil diajukan untuk approval.');
    }



    /**
     * Show project detail
     */
    public function show(Project $project)
    {
        // Check if user is the owner
        if ($project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $project->load(['owner', 'reviewer', 'members']);

        return view('lead.projects.show', compact('project'));
    }
}

