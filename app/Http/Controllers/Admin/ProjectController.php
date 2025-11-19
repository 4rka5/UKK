<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('owner', 'members');

        // Search by project name or owner name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($ownerQuery) use ($search) {
                      $ownerQuery->where('fullname', 'like', "%{$search}%")
                                 ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by project status (pending, approved, rejected, etc)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by deadline status
        if ($request->filled('deadline_status')) {
            switch ($request->deadline_status) {
                case 'overdue':
                    $query->where('deadline', '<', now());
                    break;
                case 'upcoming':
                    $query->where('deadline', '>=', now())
                          ->where('deadline', '<=', now()->addDays(7));
                    break;
                case 'active':
                    $query->where('deadline', '>', now()->addDays(7))
                          ->orWhereNull('deadline');
                    break;
            }
        }

        $projects = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        
        // Statistics - hanya 2 status
        $stats = [
            'active' => Project::where('status', 'active')->count(),
            'done' => Project::where('status', 'done')->count(),
        ];
        
        return view('admin.projects.index', compact('projects', 'stats'));
    }

    /**
     * Show submitted projects (projects with status 'done')
     */
    public function submitted(Request $request)
    {
        $query = Project::where('status', 'done')
            ->with(['owner', 'members']);

        // Search by project name or owner name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($ownerQuery) use ($search) {
                      $ownerQuery->where('fullname', 'like', "%{$search}%")
                                 ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $projects = $query->orderBy('reviewed_at', 'desc')->paginate(10)->withQueryString();
        
        return view('admin.projects.submitted', compact('projects'));
    }

    public function create()
    {
        // Hanya tampilkan team lead yang belum punya project
        $allTeamLeads = User::where('role', 'team_lead')->orderBy('fullname')->get();
        $owners = $allTeamLeads->filter(function($user) {
            return !$user->hasTasks();
        });
        
        // Hanya tampilkan developer/designer yang belum jadi member project lain
        $allUsers = User::whereIn('role', ['developer', 'designer'])
            ->orderBy('fullname')
            ->get();
        
        $users = $allUsers->reject(function($user) {
            // Cek apakah user sudah jadi member di project manapun
            return \App\Models\ProjectMember::where('user_id', $user->id)->exists();
        });
        
        return view('admin.projects.create', compact('owners', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_name' => ['required','string','max:150'],
            'description'  => ['nullable','string'],
            'deadline'     => ['nullable','date'],
            'created_by'   => ['required','exists:users,id'],
            'members'      => ['nullable','array'],
            'members.*.user_id' => ['required','exists:users,id'],
            'members.*.role' => ['required','in:developer,designer'],
        ]);
        
        // Validasi: created_by harus role team_lead
        $owner = User::find($request->created_by);
        if (!$owner || $owner->role !== 'team_lead') {
            return back()->withInput()->with('error', 'Owner project harus memiliki role Team Lead!');
        }
        
        // Validasi: Team lead tidak boleh memiliki project lain (hasTasks)
        if ($owner->hasTasks()) {
            $projectCount = Project::where('created_by', $owner->id)->count();
            return back()->withInput()->with('error', "Team Lead {$owner->fullname} sudah memiliki {$projectCount} project aktif. Satu team lead hanya boleh mengelola satu project.");
        }
        
        $members = $request->input('members', []);
        
        // Validasi: cek duplikat user_id dalam members array
        $userIds = collect($members)->pluck('user_id')->all();
        if (count($userIds) !== count(array_unique($userIds))) {
            return back()->withInput()->with('error', 'Tidak boleh menambahkan user yang sama lebih dari 1 kali!');
        }
        
        // Validasi: cek apakah owner juga ada di members
        if (in_array($request->created_by, $userIds)) {
            return back()->withInput()->with('error', 'Owner sudah otomatis menjadi Team Lead, tidak perlu ditambahkan lagi di member!');
        }
        
        // Buat project baru dengan status active
        $project = Project::create([
            'project_name' => $data['project_name'],
            'description' => $data['description'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'created_by' => $data['created_by'],
            'status' => 'active',
        ]);
        
        // Ubah status owner project menjadi 'idle' otomatis
        if ($owner) {
            $owner->update(['status' => 'idle']);
        }
        
        // Tambahkan owner sebagai team lead
        \App\Models\ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $data['created_by'],
            'role' => 'team_lead',
            'joined_at' => now()
        ]);
        
        // Tambahkan members lainnya (developer/designer) ke project
        if (!empty($members)) {
            foreach ($members as $member) {
                \App\Models\ProjectMember::create([
                    'project_id' => $project->id,
                    'user_id' => $member['user_id'],
                    'role' => $member['role'],
                    'joined_at' => now()
                ]);
            }
        }
        
        // Kirim notifikasi ke Team Lead
        if ($owner) {
            NotificationHelper::notifyProjectAssigned(
                $owner->id,
                $project->id,
                $project->project_name
            );
        }
        
        $memberCount = count($members) + 1; // +1 untuk team lead (owner)
        return redirect()->route('admin.projects.index')->with('status', "Project dibuat dengan {$memberCount} anggota (1 Team Lead, " . count($members) . " lainnya).");
    }

    public function edit(Project $project)
    {
        $owners = User::orderBy('fullname')->get();
        return view('admin.projects.edit', compact('project','owners'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'project_name' => ['required','string','max:150'],
            'description'  => ['nullable','string'],
            'deadline'     => ['nullable','date'],
            'created_by'   => ['nullable','exists:users,id'],
        ]);
        
        // Jika owner berubah, ubah status owner baru menjadi 'idle'
        if (isset($data['created_by']) && $data['created_by'] != $project->created_by) {
            $newOwner = User::find($data['created_by']);
            if ($newOwner) {
                $newOwner->update(['status' => 'idle']);
            }
        }
        
        $project->update($data);
        return redirect()->route('admin.projects.index')->with('status','Project diperbarui.');
    }

    public function destroy(Project $project)
    {
        // Admin bisa menghapus project beserta semua data terkait
        // Hapus semua data terkait secara cascade
        
        // 1. Hapus semua cards di semua boards project ini
        foreach ($project->boards as $board) {
            // Hapus card assignments
            foreach ($board->cards as $card) {
                $card->assignees()->detach(); // Hapus relasi many-to-many
                $card->comments()->delete(); // Hapus comments
                $card->subtasks()->delete(); // Hapus subtasks
            }
            $board->cards()->delete(); // Hapus cards
        }
        
        // 2. Hapus boards
        $project->boards()->delete();
        
        // 3. Hapus project members
        $project->members()->delete();
        
        // 4. Hapus project
        $project->delete();
        
        return back()->with('status', 'Project dan semua data terkait berhasil dihapus.');
    }

    public function members(Project $project)
    {
        $members = $project->members()->with('user')->get();
        $allUsers = \App\Models\User::whereNotIn('id', $members->pluck('user_id'))
            ->where('role', '!=', 'admin')
            ->where('role', '!=', 'team_lead')
            ->orderBy('fullname')
            ->get();
        
        // Filter hanya user yang tidak memiliki tugas aktif
        $availableUsers = $allUsers->reject(function($user) {
            return $user->hasTasks();
        });
        
        return view('admin.projects.members', compact('project', 'members', 'availableUsers'));
    }

    public function addMember(Request $request, Project $project)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'in:team_lead,developer,designer']
        ]);

        // Cek apakah user sudah menjadi member
        $exists = \App\Models\ProjectMember::where('project_id', $project->id)
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'User sudah menjadi anggota project ini.');
        }
        
        // Validasi: user tidak boleh memiliki tugas aktif
        $user = User::find($data['user_id']);
        if ($user && $user->hasTasks()) {
            return back()->with('error', 'User ' . ($user->fullname ?: $user->username) . ' sudah memiliki tugas aktif dan tidak dapat ditambahkan ke project baru.');
        }

        \App\Models\ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $data['user_id'],
            'role' => $data['role'],
            'joined_at' => now()
        ]);

        return back()->with('status', 'Anggota berhasil ditambahkan.');
    }

    public function removeMember(Project $project, $memberId)
    {
        $member = \App\Models\ProjectMember::where('project_id', $project->id)
            ->where('id', $memberId)
            ->firstOrFail();

        // Validasi: project harus punya minimal 1 team lead
        if ($member->role === 'team_lead') {
            $teamLeadCount = \App\Models\ProjectMember::where('project_id', $project->id)
                ->where('role', 'team_lead')
                ->count();
            
            if ($teamLeadCount <= 1) {
                return back()->with('error', 'Tidak bisa hapus Team Lead terakhir! Project harus memiliki minimal 1 Team Lead.');
            }
        }

        $member->delete();
        return back()->with('status', 'Anggota berhasil dihapus dari project.');
    }

    public function generateReport(Project $project)
    {
        $project->load([
            'boards.cards.assignees',
            'members.user',
            'owner'
        ]);

        $totalCards = $project->boards->sum(fn($b) => $b->cards->count());
        $completedCards = $project->boards->sum(fn($b) => $b->cards->where('status', 'done')->count());
        $progress = $totalCards > 0 ? round(($completedCards / $totalCards) * 100, 2) : 0;

        return view('admin.projects.report', compact('project', 'totalCards', 'completedCards', 'progress'));
    }

    /**
     * Admin bisa override rules tertentu jika diperlukan
     * Contoh: edit tugas yang sudah done, assign multiple active tasks, dll
     */
    public function overrideRule(Request $request, $resourceType, $resourceId)
    {
        $data = $request->validate([
            'rule_type' => ['required', 'in:edit_done_task,multiple_active_tasks,force_assign'],
            'reason' => ['required', 'string', 'max:500']
        ]);

        // Log override action untuk audit trail
        \Log::info('Admin Override Rule', [
            'admin_id' => auth()->id(),
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'rule_type' => $data['rule_type'],
            'reason' => $data['reason'],
            'timestamp' => now()
        ]);

        return back()->with('status', 'Override rule berhasil diterapkan. Alasan: ' . $data['reason']);
    }

    /**
     * Mark project as completed (from team lead submission)
     */
    public function markAsCompleted(Project $project)
    {
        if ($project->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Hanya project dengan status pending yang dapat ditandai sebagai selesai.');
        }

        $project->update([
            'status' => 'completed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Update status team lead menjadi idle
        $teamLead = $project->owner;
        if ($teamLead) {
            $teamLead->update(['status' => 'idle']);
        }

        // Send notification to team lead
        \App\Models\Notification::create([
            'user_id' => $project->created_by,
            'type' => 'project_completed',
            'title' => 'Project Selesai - Status Idle',
            'message' => 'Project "' . $project->project_name . '" telah ditandai selesai. Status Anda kembali idle dan dapat menerima project baru.',
            'related_type' => 'Project',
            'related_id' => $project->id,
            'is_read' => false,
        ]);

        return redirect()->back()
            ->with('success', 'Project berhasil ditandai sebagai selesai. Team lead kembali idle.');
    }

    /**
     * Reject project completion (ask team lead to continue)
     */
    public function rejectCompletion(Request $request, Project $project)
    {
        if ($project->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Hanya project dengan status pending yang dapat direject.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $project->update([
            'status' => 'active', // Kembali ke active
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Send notification to team lead
        \App\Models\Notification::create([
            'user_id' => $project->created_by,
            'type' => 'project_completion_rejected',
            'title' => 'Project Belum Selesai',
            'message' => 'Project "' . $project->project_name . '" belum dapat ditandai selesai. Alasan: ' . $validated['rejection_reason'],
            'related_type' => 'Project',
            'related_id' => $project->id,
            'is_read' => false,
        ]);

        return redirect()->back()
            ->with('success', 'Project completion direject. Team lead akan melanjutkan project.');
    }

    /**
     * Approve/Mark project as completed (done stays done, set all members to idle)
     */
    public function approve(Project $project)
    {
        // Setujui project yang done
        if ($project->status !== 'done') {
            return redirect()->back()
                ->with('error', 'Hanya project dengan status done yang dapat disetujui.');
        }
        
        $project->update([
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Set semua member project (termasuk team lead) menjadi idle
        $projectMembers = \App\Models\ProjectMember::where('project_id', $project->id)->get();
        foreach ($projectMembers as $member) {
            $user = User::find($member->user_id);
            if ($user) {
                $user->update(['status' => 'idle']);
            }
        }

        // Set semua subtasks dari cards project ini menjadi done
        $cards = \App\Models\ManagementProjectCard::where('project_id', $project->id)->get();
        foreach ($cards as $card) {
            \App\Models\ManagementProjectSubtask::where('card_id', $card->id)
                ->update(['status' => 'done']);
        }

        // Send notification to team lead
        \App\Models\Notification::create([
            'user_id' => $project->created_by,
            'type' => 'project_approved',
            'title' => 'Project Disetujui - Status Idle',
            'message' => 'Project "' . $project->project_name . '" telah disetujui oleh admin. Status Anda dan semua anggota tim kembali idle.',
            'related_type' => 'Project',
            'related_id' => $project->id,
            'is_read' => false,
        ]);

        // Send notification to all members
        foreach ($projectMembers as $member) {
            if ($member->user_id !== $project->created_by) {
                \App\Models\Notification::create([
                    'user_id' => $member->user_id,
                    'type' => 'project_approved',
                    'title' => 'Project Selesai - Status Idle',
                    'message' => 'Project "' . $project->project_name . '" telah selesai dan disetujui admin. Status Anda kembali idle dan semua tugas telah diselesaikan.',
                    'related_type' => 'Project',
                    'related_id' => $project->id,
                    'is_read' => false,
                ]);
            }
        }

        return redirect()->back()
            ->with('success', 'Project berhasil disetujui. Semua anggota tim sekarang idle dan semua subtasks diselesaikan.');
    }

    /**
     * Reject project submission from team lead
     */
    public function reject(Request $request, Project $project)
    {
        // Method ini tidak dipakai lagi karena approve/reject diganti dengan markAsCompleted/rejectCompletion
        return redirect()->back()->with('error', 'Method deprecated.');
    }

    /**
     * Show project detail for review
     */
    public function detail(Project $project)
    {
        $project->load(['owner', 'reviewer', 'members']);
        return view('admin.projects.detail', compact('project'));
    }
}
