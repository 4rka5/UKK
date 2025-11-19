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
     * Display a listing of the projects assigned to team lead
     */
    public function index()
    {
        // Team lead hanya bisa lihat project yang ditugaskan kepadanya
        $projects = Project::where('created_by', Auth::id())
            ->with(['owner', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Statistics - hanya 2 status
        $stats = [
            'approved' => Project::where('created_by', Auth::id())->where('status', 'approved')->count(),
            'active' => Project::where('created_by', Auth::id())->where('status', 'active')->count(),
        ];

        return view('lead.projects.index', compact('projects', 'stats'));
    }

    /**
     * Submit project to admin for review (change from approved to active)
     */
    public function submitProject(Project $project)
    {
        // Check if user is the owner
        if ($project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Can only submit if status is approved
        if ($project->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Hanya project dengan status "Disetujui" yang dapat diajukan.');
        }

        $project->update([
            'status' => 'active',
            'reviewed_at' => now(),
        ]);

        // Send notification to all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'project_submitted',
                'title' => 'Project Diajukan oleh Team Lead',
                'message' => Auth::user()->fullname . ' mengajukan project "' . $project->project_name . '" untuk ditinjau.',
                'related_type' => 'Project',
                'related_id' => $project->id,
                'is_read' => false,
            ]);
        }

        return redirect()->route('lead.projects.index')
            ->with('success', 'Project berhasil diajukan kepada admin.');
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

