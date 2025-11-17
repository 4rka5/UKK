<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectCard as Card;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $leadId = auth()->id();
        
        // Get projects untuk filter dropdown
        if (auth()->user()->role === 'admin') {
            $projects = Project::orderBy('project_name')->get();
        } else {
            $projects = Project::whereHas('members', function($q) use ($leadId) {
                    $q->where('user_id', $leadId)->where('role', 'team_lead');
                })
                ->orderBy('project_name')
                ->get();
        }
        
        // Query cards
        $cardsQuery = Card::with(['project','assignees','creator']);
        
        // Admin punya akses ke semua cards
        if (auth()->user()->role !== 'admin') {
            $cardsQuery->whereHas('project.members', function($q) use ($leadId) {
                $q->where('user_id', $leadId)->where('role', 'team_lead');
            });
        }
        
        // Search by card title
        if ($request->filled('search')) {
            $search = $request->input('search');
            $cardsQuery->where('card_title', 'like', "%{$search}%");
        }
        
        // Filter by project_id
        if ($request->filled('project_id')) {
            $cardsQuery->where('project_id', $request->input('project_id'));
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $cardsQuery->where('status', $request->input('status'));
        }
        
        // Filter by priority
        if ($request->filled('priority')) {
            $cardsQuery->where('priority', $request->input('priority'));
        }
        
        $cards = $cardsQuery->orderByDesc('id')->paginate(15)->withQueryString();
        
        $priorities = ['low','medium','high'];
        $statuses = ['todo','in_progress','review','done'];
        return view('lead.cards.index', compact('cards','projects','priorities','statuses'));
    }

    public function create()
    {
        $leadId = auth()->id();
        
        // Admin punya akses ke semua projects
        if (auth()->user()->role === 'admin') {
            $projects = Project::with('members.user')->get();
        } else {
            $projects = Project::with('members.user')
                ->whereHas('members', function($q) use ($leadId) {
                    $q->where('user_id', $leadId)->where('role', 'team_lead');
                })
                ->get();
        }
        
        // Set default project jika hanya punya 1 project
        $defaultProjectId = $projects->count() === 1 ? $projects->first()->id : null;
        
        // Get ALL users (designer/developer only) untuk ditampilkan via JavaScript
        // Nanti akan difilter berdasarkan project yang dipilih
        $allUsers = \App\Models\User::whereNotIn('role', ['admin', 'team_lead'])
            ->orderBy('fullname')
            ->get();
        
        // Filter users yang belum punya tugas aktif
        $users = $allUsers->filter(function($user) {
            return !$user->hasTasks();
        });
        
        $priorities = ['low','medium','high'];
        $statuses = ['todo','in_progress','review','done'];
        return view('lead.cards.create', compact('projects','users','allUsers','priorities','statuses','defaultProjectId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => ['required','exists:projects,id'],
            'card_title' => ['required','string','max:200'],
            'description' => ['nullable','string'],
            'due_date' => ['nullable','date'],
            'status' => ['nullable','in:todo,in_progress,review,done'],
            'priority' => ['required','in:low,medium,high'],
            'estimated_hours' => ['nullable','numeric','min:0'],
            'assigned_users' => ['nullable','array'],
            'assigned_users.*' => ['exists:users,id'],
            'assignment_status' => ['nullable','in:assigned,in_progress,completed'],
        ]);
        
        // Validasi: User yang di-assign harus member dari project
        if ($request->filled('assigned_users')) {
            $projectId = $request->project_id;
            
            // Get project member IDs
            $projectMemberIds = \App\Models\ProjectMember::where('project_id', $projectId)
                ->pluck('user_id')
                ->toArray();
            
            // Cek apakah semua assigned users adalah member project
            foreach ($request->assigned_users as $userId) {
                if (!in_array($userId, $projectMemberIds)) {
                    $user = \App\Models\User::find($userId);
                    return back()->withInput()->with('error', "User {$user->fullname} bukan anggota project ini. Hanya member project yang bisa di-assign ke card.");
                }
            }
        }
        
        // Set default status to 'todo' jika tidak diisi
        $data['status'] = $data['status'] ?? 'todo';
        $data['created_by'] = Auth::id();
        $card = Card::create($data);
        
        // Assign users jika ada
        if ($request->filled('assigned_users') && is_array($request->input('assigned_users'))) {
            $assignmentStatus = $request->input('assignment_status', 'assigned');
            $assignmentData = [];
            
            foreach ($request->input('assigned_users') as $userId) {
                $assignmentData[$userId] = [
                    'assignment_status' => $assignmentStatus,
                    'assigned_at' => now(),
                ];
            }
            
            $card->assignees()->attach($assignmentData);
            
            // Kirim notifikasi ke setiap user yang di-assign
            foreach ($request->input('assigned_users') as $userId) {
                NotificationHelper::notifyTaskAssigned(
                    $userId,
                    $card->id,
                    $card->card_title,
                    $card->project->project_name
                );
            }
        }
        
        return redirect()->route('lead.cards.index')->with('status','Card dibuat dan user berhasil di-assign.');
    }

    public function edit(Card $card)
    {
        $this->authorizeCard($card);
        $leadId = Auth::id();
        
        // Admin punya akses ke semua projects
        if (auth()->user()->role === 'admin') {
            $projects = Project::orderByDesc('id')->get();
        } else {
            $projects = Project::whereHas('members', function($q) use ($leadId) {
                    $q->where('user_id', $leadId)
                      ->where('role', 'team_lead');
                })
                ->orderByDesc('id')->get();
        }
        
        // Set default project jika hanya punya 1 project
        $defaultProjectId = $projects->count() === 1 ? $projects->first()->id : $card->project_id;
        
        // Get users yang belum punya tugas + user yang sudah assigned ke card ini
        $allUsers = \App\Models\User::whereNotIn('role', ['admin', 'team_lead'])->get();
        $currentAssignees = $card->assignees->pluck('id')->toArray();
        
        $users = $allUsers->filter(function($user) use ($currentAssignees) {
            // Tampilkan jika user sudah assigned ke card ini ATAU belum punya tugas
            return in_array($user->id, $currentAssignees) || !$user->hasTasks();
        });
        
        $priorities = ['low','medium','high'];
        $statuses = ['todo','in_progress','review','done'];
        return view('lead.cards.edit', compact('card','projects','users','priorities','statuses','defaultProjectId'));
    }

    public function update(Request $request, Card $card)
    {
        $this->authorizeCard($card);
        
        // Team Lead tidak bisa edit tugas yang sudah selesai
        if ($card->status === 'done') {
            return back()->with('error', 'Tidak bisa edit tugas yang sudah selesai (done). Tugas yang sudah selesai tidak dapat diubah.');
        }
        
        $data = $request->validate([
            'project_id' => ['required','exists:projects,id'],
            'card_title' => ['required','string','max:200'],
            'description' => ['nullable','string'],
            'due_date' => ['nullable','date'],
            'status' => ['required','in:todo,in_progress,review,done'],
            'priority' => ['required','in:low,medium,high'],
            'estimated_hours' => ['nullable','numeric','min:0'],
            'actual_hours' => ['nullable','numeric','min:0'],
            'assigned_users' => ['nullable','array'],
            'assigned_users.*' => ['exists:users,id'],
            'assignment_status' => ['nullable','in:assigned,in_progress,completed'],
        ]);
        
        // Validasi: User yang di-assign harus member dari project
        if ($request->filled('assigned_users')) {
            $projectId = $request->project_id;
            
            // Get project member IDs
            $projectMemberIds = \App\Models\ProjectMember::where('project_id', $projectId)
                ->pluck('user_id')
                ->toArray();
            
            // Cek apakah semua assigned users adalah member project
            foreach ($request->assigned_users as $userId) {
                if (!in_array($userId, $projectMemberIds)) {
                    $user = \App\Models\User::find($userId);
                    return back()->withInput()->with('error', "User {$user->fullname} bukan anggota project ini. Hanya member project yang bisa di-assign ke card.");
                }
            }
        }
        
        $card->update($data);
        
        // Update assignments
        if ($request->has('assigned_users')) {
            $currentAssignees = $card->assignees->pluck('id')->toArray();
            $newAssignees = $request->input('assigned_users', []);
            $assignmentStatus = $request->input('assignment_status', 'assigned');
            
            // Prepare sync data
            $syncData = [];
            foreach ($newAssignees as $userId) {
                // Jika user sudah ada, pertahankan status mereka
                if (in_array($userId, $currentAssignees)) {
                    $existingPivot = $card->assignees()->where('user_id', $userId)->first()->pivot;
                    $syncData[$userId] = [
                        'assignment_status' => $existingPivot->assignment_status,
                        'assigned_at' => $existingPivot->assigned_at,
                    ];
                } else {
                    // User baru, gunakan status dari input
                    $syncData[$userId] = [
                        'assignment_status' => $assignmentStatus,
                        'assigned_at' => now(),
                    ];
                }
            }
            
            $card->assignees()->sync($syncData);
            
            // Kirim notifikasi ke user baru yang di-assign (yang tidak ada di currentAssignees)
            $addedUsers = array_diff($newAssignees, $currentAssignees);
            foreach ($addedUsers as $userId) {
                NotificationHelper::notifyTaskAssigned(
                    $userId,
                    $card->id,
                    $card->card_title,
                    $card->board->board_name
                );
            }
        }
        
        return redirect()->route('lead.cards.index')->with('status','Card diperbarui.');
    }

    public function destroy(Card $card)
    {
        $this->authorizeCard($card);
        $card->delete();
        return back()->with('status','Card dihapus.');
    }

    public function move(Request $request, Card $card)
    {
        $this->authorizeCard($card);
        $request->validate([
            'status' => ['required','in:todo,in_progress,review,done']
        ]);
        $card->update(['status' => $request->input('status')]);
        return back()->with('status','Status card diperbarui.');
    }

    protected function authorizeCard(\App\Models\ManagementProjectCard $card)
    {
        // Admin punya akses penuh
        if (auth()->user()->role === 'admin') {
            return;
        }
        
        $isTeamLead = \App\Models\ProjectMember::where('project_id', $card->project_id)
            ->where('user_id', auth()->id())
            ->where('role', 'team_lead')
            ->exists();
        if (!$isTeamLead) abort(403, 'Hanya team lead dari project ini yang bisa akses.');
    }

    public function detail(Card $card)
    {
        $this->authorizeCard($card);
        $card->load(['project', 'assignees', 'creator', 'comments.user']);
        
        // Get timer info for assigned users
        foreach ($card->assignees as $assignee) {
            $pivot = $assignee->pivot;
            $totalSeconds = $pivot->total_work_seconds ?? 0;
            $assignee->work_hours = number_format($totalSeconds / 3600, 2);
        }
        
        $html = view('lead.cards.detail-modal', compact('card'))->render();
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function approve(Card $card)
    {
        $this->authorizeCard($card);
        
        // Update status ke done
        $card->update(['status' => 'done']);
        
        // Add comment
        \App\Models\ManagementProjectComment::create([
            'card_id' => $card->id,
            'user_id' => auth()->id(),
            'comment_text' => '✅ Tugas telah disetujui oleh Team Lead. Status: DONE',
            'created_at' => now()
        ]);
        
        // Kirim notifikasi ke semua assigned users
        foreach ($card->assignees as $assignee) {
            NotificationHelper::notifyTaskApproved(
                $assignee->id,
                $card->id,
                $card->card_title
            );
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil disetujui'
        ]);
    }

    public function reject(Request $request, Card $card)
    {
        $this->authorizeCard($card);
        
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        $reason = $request->input('reason');
        
        // Update status kembali ke in_progress
        $card->update(['status' => 'in_progress']);
        
        // Add comment
        \App\Models\ManagementProjectComment::create([
            'card_id' => $card->id,
            'user_id' => auth()->id(),
            'comment_text' => '❌ Tugas ditolak oleh Team Lead. Alasan: ' . $reason,
            'created_at' => now()
        ]);
        
        // Kirim notifikasi ke semua assigned users
        foreach ($card->assignees as $assignee) {
            NotificationHelper::notifyTaskRejected(
                $assignee->id,
                $card->id,
                $card->card_title,
                $reason
            );
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil ditolak'
        ]);
    }
    
    /**
     * Approve extension request
     */
    public function approveExtension(Request $request, Card $card)
    {
        $this->authorizeCard($card);
        
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        
        $userId = $request->user_id;
        
        // Update extension approval
        $card->assignees()->updateExistingPivot($userId, [
            'extension_approved' => true,
            'extension_approved_by' => auth()->id(),
            'extension_approved_at' => now()
        ]);
        
        // Send notification to member
        NotificationHelper::notifyExtensionApproved($userId, $card->id, $card->card_title);
        
        return response()->json([
            'success' => true,
            'message' => 'Extension request berhasil disetujui.'
        ]);
    }
    
    /**
     * Reject extension request
     */
    public function rejectExtension(Request $request, Card $card)
    {
        $this->authorizeCard($card);
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500'
        ]);
        
        $userId = $request->user_id;
        $reason = $request->input('reason', 'Tidak ada alasan diberikan');
        
        // Update extension rejection
        $card->assignees()->updateExistingPivot($userId, [
            'extension_approved' => false,
            'extension_approved_by' => auth()->id(),
            'extension_approved_at' => now()
        ]);
        
        // Send notification to member
        NotificationHelper::notifyExtensionRejected($userId, $card->id, $card->card_title, $reason);
        
        return response()->json([
            'success' => true,
            'message' => 'Extension request berhasil ditolak.'
        ]);
    }
    
    public function addComment(Request $request, Card $card)
    {
        $request->validate([
            'comment_text' => 'required|string|max:1000'
        ]);
        
        $comment = \App\Models\ManagementProjectComment::create([
            'card_id' => $card->id,
            'user_id' => auth()->id(),
            'comment_text' => $request->comment_text
        ]);
        
        // Send notification to all assignees
        $assignees = $card->assignees;
        $commenter = auth()->user();
        
        foreach ($assignees as $assignee) {
            \App\Models\Notification::create([
                'user_id' => $assignee->id,
                'type' => 'comment',
                'title' => 'Komentar dari Team Lead',
                'message' => $commenter->fullname . ' (Team Lead) menambahkan komentar di card "' . $card->card_title . '"',
                'related_id' => $card->id,
                'related_type' => 'Card'
            ]);
        }
        
        return redirect()->back()->with('status', 'Komentar berhasil ditambahkan.');
    }
}

