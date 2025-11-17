<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Helpers\NotificationHelper;

class DesignerController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        // Hanya tampilkan tugas desain yang di-assign ke designer ini
        // Order: status 'done' di bawah, lalu by ID
        $cards = ManagementProjectCard::with(['project', 'assignees', 'creator'])
            ->whereHas('assignees', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->where(function($q) {
                // Filter hanya tugas yang berkaitan dengan desain
                $q->where('card_title', 'like', '%desain%')
                  ->orWhere('card_title', 'like', '%design%')
                  ->orWhere('card_title', 'like', '%UI%')
                  ->orWhere('card_title', 'like', '%UX%')
                  ->orWhere('description', 'like', '%desain%')
                  ->orWhere('description', 'like', '%design%');
            })
            ->orderByRaw("CASE WHEN status = 'done' THEN 1 ELSE 0 END ASC")
            ->orderByDesc('id')
            ->paginate(10);

        return view('member.designer.index', compact('cards'));
    }

    public function show(ManagementProjectCard $card)
    {
        $userId = Auth::id();
        
        // Hanya designer yang di-assign bisa akses
        $isAssigned = $card->assignees()->where('users.id', $userId)->exists();

        if (!$isAssigned) {
            abort(403, 'Anda hanya bisa mengakses tugas desain yang di-assign kepada Anda.');
        }

        $card->load(['project', 'creator', 'assignees', 'subtasks', 'comments.user']);

        return view('member.designer.show', compact('card'));
    }

    public function uploadDesign(Request $request, ManagementProjectCard $card)
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
            'design_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,svg,ai,psd,fig', 'max:10240'], // max 10MB
            'description' => ['nullable', 'string', 'max:500']
        ]);

        if ($request->hasFile('design_file')) {
            $file = $request->file('design_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('designs', $filename, 'public');

            // Simpan ke comments sebagai attachment
            \App\Models\ManagementProjectComment::create([
                'card_id' => $card->id,
                'user_id' => $userId,
                'comment_text' => $request->description ?? 'Design file uploaded: ' . $filename,
                'attachment' => $path,
                'created_at' => now()
            ]);

            return back()->with('status', 'Design file berhasil diupload.');
        }

        return back()->with('error', 'Gagal upload file.');
    }

    public function requestReview(Request $request, ManagementProjectCard $card)
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
            return back()->with('error', 'Tugas ini sudah selesai. Review tidak dapat direquest lagi.');
        }

        // Update status ke 'review' (untuk design review)
        $card->update(['status' => 'review']);
        
        // Update assignment status user menjadi 'completed' dan stop timer
        $card->assignees()->updateExistingPivot($userId, [
            'assignment_status' => 'completed',
            'is_working' => false
        ]);

        // Tambahkan comment
        \App\Models\ManagementProjectComment::create([
            'card_id' => $card->id,
            'user_id' => $userId,
            'comment_text' => '✅ Design review requested. Menunggu approval Team Lead.',
            'created_at' => now()
        ]);

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

        return back()->with('status', 'Design review request berhasil dikirim. Status card diubah ke REVIEW.');
    }
}
