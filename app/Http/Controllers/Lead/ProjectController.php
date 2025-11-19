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
            'active' => Project::where('created_by', Auth::id())->where('status', 'active')->count(),
            'done' => Project::where('created_by', Auth::id())->where('status', 'done')->count(),
        ];

        return view('lead.projects.index', compact('projects', 'stats'));
    }

    /**
     * Submit project to admin for review (change from active to done)
     */
    public function submitProject(Project $project)
    {
        // Check if user is the owner
        if ($project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Can only submit if status is active
        if ($project->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Hanya project dengan status "Aktif" yang dapat diajukan.');
        }

        $project->update([
            'status' => 'done',
            'reviewed_at' => now(),
        ]);

        // Send notification to all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'project_submitted',
                'title' => 'Project Selesai - Menunggu Review',
                'message' => Auth::user()->fullname . ' telah menyelesaikan project "' . $project->project_name . '" dan mengajukan untuk direview.',
                'related_type' => 'Project',
                'related_id' => $project->id,
                'is_read' => false,
            ]);
        }

        return redirect()->route('lead.projects.index')
            ->with('success', 'Project berhasil diajukan sebagai selesai.');
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

