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
            'draft' => Project::where('created_by', Auth::id())->where('status', 'draft')->count(),
            'pending' => Project::where('created_by', Auth::id())->where('status', 'pending')->count(),
            'approved' => Project::where('created_by', Auth::id())->where('status', 'approved')->count(),
            'rejected' => Project::where('created_by', Auth::id())->where('status', 'rejected')->count(),
        ];

        return view('lead.projects.index', compact('projects', 'stats'));
    }

    /**
     * Show the form for creating a new project
     */
    public function create()
    {
        return view('lead.projects.create');
    }

    /**
     * Store a newly created project in storage
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
            'status' => 'draft',
        ]);

        return redirect()->route('lead.projects.index')
            ->with('success', 'Project berhasil dibuat sebagai draft.');
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit(Project $project)
    {
        // Check if user is the owner
        if ($project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Can only edit if draft or rejected
        if (!in_array($project->status, ['draft', 'rejected'])) {
            return redirect()->route('lead.projects.index')
                ->with('error', 'Hanya project dengan status draft atau rejected yang dapat diedit.');
        }

        return view('lead.projects.edit', compact('project'));
    }

    /**
     * Update the specified project in storage
     */
    public function update(Request $request, Project $project)
    {
        // Check if user is the owner
        if ($project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Can only update if draft or rejected
        if (!in_array($project->status, ['draft', 'rejected'])) {
            return redirect()->route('lead.projects.index')
                ->with('error', 'Hanya project dengan status draft atau rejected yang dapat diupdate.');
        }

        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date|after:today',
        ]);

        $project->update($validated);

        return redirect()->route('lead.projects.index')
            ->with('success', 'Project berhasil diupdate.');
    }

    /**
     * Submit project for admin approval
     */
    public function submitForApproval(Project $project)
    {
        // Check if user is the owner
        if ($project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$project->canSubmitForApproval()) {
            return redirect()->back()
                ->with('error', 'Project tidak dapat diajukan untuk approval.');
        }

        $project->update([
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
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
     * Remove the specified project from storage
     */
    public function destroy(Project $project)
    {
        // Check if user is the owner
        if ($project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Can only delete if draft or rejected
        if (!in_array($project->status, ['draft', 'rejected'])) {
            return redirect()->route('lead.projects.index')
                ->with('error', 'Hanya project dengan status draft atau rejected yang dapat dihapus.');
        }

        $project->delete();

        return redirect()->route('lead.projects.index')
            ->with('success', 'Project berhasil dihapus.');
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

