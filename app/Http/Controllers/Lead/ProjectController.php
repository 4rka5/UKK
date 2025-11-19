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

        // Statistics
        $stats = [
            'total' => Project::where('created_by', Auth::id())->count(),
            'pending' => Project::where('created_by', Auth::id())->where('status', 'pending')->count(),
            'approved' => Project::where('created_by', Auth::id())->where('status', 'approved')->count(),
            'active' => Project::where('created_by', Auth::id())->where('status', 'active')->count(),
        ];

        return view('lead.projects.index', compact('projects', 'stats'));
    }

    /**
     * Submit project as completed (request admin to mark as done)
     */
    public function submitCompletion(Project $project)
    {
        // Check if user is the owner
        if ($project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Can only submit if status is approved or active
        if (!in_array($project->status, ['approved', 'active'])) {
            return redirect()->back()
                ->with('error', 'Hanya project dengan status approved atau active yang dapat diajukan sebagai selesai.');
        }

        $project->update([
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        // Send notification to all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'project_completion_submitted',
                'title' => 'Project Selesai - Menunggu Verifikasi',
                'message' => Auth::user()->name . ' mengajukan project "' . $project->project_name . '" sebagai selesai.',
                'related_type' => 'Project',
                'related_id' => $project->id,
                'is_read' => false,
            ]);
        }

        return redirect()->route('lead.projects.index')
            ->with('success', 'Project berhasil diajukan sebagai selesai. Menunggu verifikasi admin.');
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

