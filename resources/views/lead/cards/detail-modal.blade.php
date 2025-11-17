<div class="card-detail-modal">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h4 class="mb-2">{{ $card->card_title }}</h4>
      <div class="d-flex gap-2 flex-wrap">
        <span class="badge bg-{{ $card->status === 'done' ? 'success' : ($card->status === 'review' ? 'warning' : ($card->status === 'in_progress' ? 'info' : 'secondary')) }}">
          {{ strtoupper(str_replace('_', ' ', $card->status)) }}
        </span>
        <span class="badge bg-{{ $card->priority === 'high' ? 'danger' : ($card->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
          Priority: {{ strtoupper($card->priority) }}
        </span>
        @if($card->project)
          <span class="badge bg-light text-dark border">📋 {{ $card->project->project_name }}</span>
        @endif
      </div>
    </div>
  </div>

  <!-- Info Grid -->
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="info-box p-3 border rounded">
        <small class="text-muted d-block mb-1">Deskripsi</small>
        <div>{{ $card->description ?: '-' }}</div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="info-box p-3 border rounded">
        <small class="text-muted d-block mb-1">Due Date</small>
        <div>
          @if($card->due_date)
            {{ \Carbon\Carbon::parse($card->due_date)->format('d F Y') }}
            @if(\Carbon\Carbon::parse($card->due_date)->isPast() && $card->status !== 'done')
              <span class="badge bg-danger ms-2">OVERDUE</span>
            @endif
          @else
            -
          @endif
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="info-box p-3 border rounded">
        <small class="text-muted d-block mb-1">Estimasi Jam</small>
        <div class="fw-bold">{{ $card->estimated_hours ? number_format($card->estimated_hours, 2) . ' jam' : '-' }}</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="info-box p-3 border rounded">
        <small class="text-muted d-block mb-1">Aktual Jam</small>
        <div class="fw-bold text-primary">{{ $card->actual_hours ? number_format($card->actual_hours, 2) . ' jam' : '-' }}</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="info-box p-3 border rounded">
        <small class="text-muted d-block mb-1">Dibuat Oleh</small>
        <div>{{ $card->creator->fullname ?? $card->creator->username ?? '-' }}</div>
      </div>
    </div>
  </div>

  <!-- Assigned Users -->
  @if($card->assignees && $card->assignees->count() > 0)
    <div class="mb-4">
      <h6 class="mb-3">👥 Assigned To</h6>
      <div class="table-responsive">
        <table class="table table-sm table-bordered">
          <thead>
            <tr>
              <th>User</th>
              <th>Role</th>
              <th>Status</th>
              <th>Work Hours</th>
            </tr>
          </thead>
          <tbody>
            @foreach($card->assignees as $assignee)
              <tr>
                <td>{{ $assignee->fullname ?? $assignee->username }}</td>
                <td><span class="badge bg-secondary">{{ strtoupper($assignee->role) }}</span></td>
                <td><span class="badge bg-info">{{ strtoupper($assignee->pivot->assignment_status ?? 'assigned') }}</span></td>
                <td>{{ $assignee->work_hours ?? '0.00' }} jam</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  <!-- Extension Requests -->
  @php
    $pendingExtensions = $card->assignees->filter(function($assignee) {
      return $assignee->pivot->extension_requested && is_null($assignee->pivot->extension_approved);
    });
    $hasOverdue = $card->isOverdue();
  @endphp
  
  @if($pendingExtensions->count() > 0 || $hasOverdue)
    <div class="mb-4">
      <h6 class="mb-3">⏰ Deadline & Extension Requests</h6>
      
      @if($hasOverdue)
        <div class="alert alert-warning">
          <strong>⚠️ Task Overdue!</strong><br>
          Deadline: {{ \Carbon\Carbon::parse($card->due_date)->format('d M Y') }}
          ({{ \Carbon\Carbon::parse($card->due_date)->diffForHumans() }})
        </div>
      @endif
      
      @if($pendingExtensions->count() > 0)
        <div class="border rounded p-3">
          @foreach($pendingExtensions as $member)
            <div class="extension-request mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <strong>{{ $member->fullname ?? $member->username }}</strong>
                  <span class="badge bg-warning text-dark ms-2">⏳ Pending Approval</span>
                </div>
                <small class="text-muted">{{ \Carbon\Carbon::parse($member->pivot->extension_requested_at)->diffForHumans() }}</small>
              </div>
              
              <div class="bg-light p-2 rounded mb-3">
                <small class="text-muted d-block mb-1"><strong>Alasan:</strong></small>
                <div>{{ $member->pivot->extension_reason }}</div>
              </div>
              
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success" onclick="approveExtension({{ $card->id }}, {{ $member->id }})">
                  ✅ Setujui Perpanjangan
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="rejectExtension({{ $card->id }}, {{ $member->id }})">
                  ❌ Tolak
                </button>
              </div>
            </div>
          @endforeach
        </div>
      @endif
      
      @php
        $approvedExtensions = $card->assignees->filter(function($assignee) {
          return $assignee->pivot->extension_approved === true;
        });
      @endphp
      
      @if($approvedExtensions->count() > 0)
        <div class="mt-3">
          <small class="text-muted d-block mb-2"><strong>Approved Extensions:</strong></small>
          @foreach($approvedExtensions as $member)
            <div class="alert alert-success py-2 mb-1">
              <small>✅ <strong>{{ $member->fullname ?? $member->username }}</strong> - 
              Disetujui {{ \Carbon\Carbon::parse($member->pivot->extension_approved_at)->diffForHumans() }}</small>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  @endif

  <!-- Comments/Activity -->
  <div class="mb-4">
    <h6 class="mb-3">💬 Comments ({{ $card->comments->count() }})</h6>
    
    @if($card->comments && $card->comments->count() > 0)
      <div class="comments-list mb-3" style="max-height: 300px; overflow-y: auto;">
        @foreach($card->comments->sortByDesc('created_at') as $comment)
          <div class="comment-item mb-3 p-3 border rounded bg-light">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <strong>{{ $comment->user->fullname ?? $comment->user->username ?? 'Unknown' }}</strong>
                <span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">{{ ucfirst($comment->user->role) }}</span>
              </div>
              <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
            </div>
            <div class="comment-text">{{ $comment->comment_text }}</div>
            @if($comment->attachment)
              <div class="mt-2">
                <a href="{{ asset('storage/' . $comment->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                  📎 View Attachment
                </a>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    @else
      <div class="alert alert-light mb-3">
        <small class="text-muted">Belum ada komentar. Jadilah yang pertama berkomentar!</small>
      </div>
    @endif
    
    <!-- Add Comment Form -->
    <div class="border rounded p-3 bg-light">
      <form action="{{ route('lead.cards.comment', $card) }}" method="POST" id="commentForm{{ $card->id }}">
        @csrf
        <div class="mb-2">
          <label class="form-label small fw-semibold">Tambah Komentar</label>
          <textarea name="comment_text" class="form-control" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
        </div>
        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-send"></i> Kirim Komentar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Action Buttons -->
  @if($card->status === 'review')
    <div class="border-top pt-3">
      <div class="d-flex gap-2 justify-content-end">
        <button type="button" class="btn btn-danger" onclick="rejectCard({{ $card->id }})">
          ❌ Reject
        </button>
        <button type="button" class="btn btn-success" onclick="approveCard({{ $card->id }})">
          ✅ Approve
        </button>
      </div>
      <div class="mt-2">
        <small class="text-muted">
          <strong>Approve:</strong> Status akan berubah ke DONE<br>
          <strong>Reject:</strong> Status akan kembali ke IN PROGRESS dan member perlu revisi
        </small>
      </div>
    </div>
  @elseif($card->status === 'done')
    <div class="alert alert-success mb-0">
      ✅ Tugas ini sudah selesai dan disetujui
    </div>
  @else
    <div class="alert alert-info mb-0">
      ℹ️ Tugas masih dalam proses. Tombol approve/reject akan muncul ketika status REVIEW.
    </div>
  @endif
</div>

<style>
.card-detail-modal .info-box {
  transition: all 0.2s;
}
.card-detail-modal .info-box:hover {
  background-color: #f8f9fa;
  border-color: #0d6efd !important;
}
.comments-list::-webkit-scrollbar {
  width: 8px;
}
.comments-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}
.comments-list::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}
.comments-list::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>

<script>
// Handle comment form submission in modal
document.addEventListener('DOMContentLoaded', function() {
  const commentForms = document.querySelectorAll('[id^="commentForm"]');
  commentForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';
      
      fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      })
      .then(response => response.text())
      .then(() => {
        // Reload modal content
        const cardId = this.action.match(/cards\/(\d+)\//)[1];
        showCardDetail(cardId);
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Gagal mengirim komentar');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      });
    });
  });
});
</script>

