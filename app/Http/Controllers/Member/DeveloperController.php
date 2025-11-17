<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class DeveloperController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        // Tampilkan semua tugas yang di-assign ke developer ini
        // Order: status 'done' di bawah, lalu by ID
        $cards = ManagementProjectCard::with(['project', 'assignees', 'creator'])
            ->whereHas('assignees', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->orderByRaw("CASE WHEN status = 'done' THEN 1 ELSE 0 END ASC")
            ->orderByDesc('id')
            ->paginate(10);

        // Hitung tugas aktif
        $activeTasksCount = ManagementProjectCard::whereHas('assignees', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->where('status', 'in_progress')
            ->count();

        return view('member.developer.index', compact('cards', 'activeTasksCount'));
    }

    public function show(ManagementProjectCard $card)
    {
        $userId = Auth::id();
        
        // Hanya developer yang di-assign bisa akses
        $isAssigned = $card->assignees()->where('users.id', $userId)->exists();

        if (!$isAssigned) {
            abort(403, 'Anda hanya bisa mengakses tugas yang di-assign kepada Anda.');
        }

        $card->load(['project', 'creator', 'assignees', 'subtasks', 'comments.user']);

        return view('member.developer.show', compact('card'));
    }

    public function updateProgress(Request $request, ManagementProjectCard $card)
    {
        $userId = Auth::id();
        $isAssigned = $card->assignees()->where('users.id', $userId)->exists();

        if (!$isAssigned) {
            abort(403);
        }

        // Check if task is overdue and user cannot work
        if (!$card->canUserWork($userId)) {
            return back()->with('error', 'Tugas ini sudah melewati deadline. Silakan ajukan perpanjangan ke Team Lead.');
        }

        // Cek jika tugas sudah selesai
        if ($card->status === 'done') {
            return back()->with('error', 'Tugas ini sudah selesai. Progress tidak dapat diupdate.');
        }

        $request->validate([
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'progress_note' => ['nullable', 'string', 'max:1000'],
            'hours_spent' => ['required', 'numeric', 'min:0.1'] // WAJIB time tracking
        ]);

        // Simpan progress sebagai comment
        \App\Models\ManagementProjectComment::create([
            'card_id' => $card->id,
            'user_id' => $userId,
            'comment_text' => 'Progress update: ' . $request->progress_percentage . '% - ' . ($request->progress_note ?? '') . ' (Time: ' . $request->hours_spent . ' hours)',
            'created_at' => now()
        ]);

        // Update actual hours (WAJIB)
        $card->increment('actual_hours', $request->hours_spent);

        // Update status ke 'review' otomatis ketika developer submit progress
        $card->update(['status' => 'review']);

        // Kirim notifikasi ke Team Lead
        $teamLead = $card->project->owner;
        if ($teamLead) {
            NotificationHelper::notifyTaskSubmitted(
                $teamLead->id,
                $card->id,
                Auth::user()->fullname ?? Auth::user()->username,
                $card->card_title
            );
        }

        return back()->with('status', 'Progress berhasil diupdate. Status card diubah ke REVIEW untuk ditinjau Team Lead.');
    }

    public function uploadFile(Request $request, ManagementProjectCard $card)
    {
        $userId = Auth::id();
        $isAssigned = $card->assignees()->where('users.id', $userId)->exists();

        if (!$isAssigned) {
            abort(403);
        }

        // Check if task is overdue and user cannot work
        if (!$card->canUserWork($userId)) {
            return back()->with('error', 'Tugas ini sudah melewati deadline. Silakan ajukan perpanjangan ke Team Lead.');
        }

        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // max 10MB
            'description' => ['nullable', 'string', 'max:500']
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('attachments', $filename, 'public');

            // Simpan ke comments sebagai attachment
            \App\Models\ManagementProjectComment::create([
                'card_id' => $card->id,
                'user_id' => $userId,
                'comment_text' => $request->description ?? 'File uploaded: ' . $filename,
                'attachment' => $path,
                'created_at' => now()
            ]);

            return back()->with('status', 'File berhasil diupload.');
        }

        return back()->with('error', 'Gagal upload file.');
    }

    public function reportBlocker(Request $request, ManagementProjectCard $card)
    {
        $userId = Auth::id();
        $isAssigned = $card->assignees()->where('users.id', $userId)->exists();

        if (!$isAssigned) {
            abort(403);
        }

        // Check if task is overdue and user cannot work
        if (!$card->canUserWork($userId)) {
            return back()->with('error', 'Tugas ini sudah melewati deadline. Silakan ajukan perpanjangan ke Team Lead.');
        }

        $request->validate([
            'blocker_description' => ['required', 'string', 'max:1000']
        ]);

        // Tambahkan comment dengan tag blocker
        \App\Models\ManagementProjectComment::create([
            'card_id' => $card->id,
            'user_id' => $userId,
            'comment_text' => '🚫 BLOCKER: ' . $request->blocker_description,
            'created_at' => now()
        ]);

        // Update status card jika perlu
        if ($card->status !== 'backlog') {
            $card->update(['priority' => 'high']); // Naikkan priority
        }

        // Kirim notifikasi ke Team Lead
        $teamLead = $card->project->owner;
        if ($teamLead) {
            NotificationHelper::notifyBlockerReported(
                $teamLead->id,
                $card->id,
                Auth::user()->fullname ?? Auth::user()->username,
                $card->card_title
            );
        }

        return back()->with('status', 'Blocker berhasil dilaporkan.');
    }

    public function workDocumentation(Request $request, ManagementProjectCard $card)
    {
        $userId = Auth::id();
        $isAssigned = $card->assignees()->where('users.id', $userId)->exists();

        if (!$isAssigned) {
            abort(403);
        }

        // Check if task is overdue and user cannot work
        if (!$card->canUserWork($userId)) {
            return back()->with('error', 'Tugas ini sudah melewati deadline. Silakan ajukan perpanjangan ke Team Lead.');
        }

        $request->validate([
            'documentation' => ['required', 'string', 'max:5000']
        ]);

        // Simpan dokumentasi sebagai comment
        \App\Models\ManagementProjectComment::create([
            'card_id' => $card->id,
            'user_id' => $userId,
            'comment_text' => '📝 DOKUMENTASI:\n\n' . $request->documentation,
            'created_at' => now()
        ]);

        return back()->with('status', 'Dokumentasi kerja berhasil disimpan.');
    }
}
