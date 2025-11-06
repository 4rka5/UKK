@extends('layouts.member')
@section('title', 'Detail Card')
@section('memberContent')

<style>
.detail-card { background: white; border-radius: 10px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
.detail-header { border-bottom: 2px solid #e9ecef; padding-bottom: 1rem; margin-bottom: 1.5rem; }
.info-row { display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; }
.info-row:last-child { border-bottom: none; }
.info-label { font-weight: 600; color: #6c757d; }
.status-select { max-width: 300px; }
</style>

<div class="mb-3">
  <a href="{{ route('member.dashboard') }}" class="btn btn-outline-secondary btn-sm">
    ← Kembali ke Dashboard
  </a>
</div>

<!-- Card Header -->
<div class="detail-card">
  <div class="detail-header">
    <h3 class="mb-2">{{ $card->card_title }}</h3>
    <div class="d-flex gap-2 flex-wrap">
      <span class="badge bg-{{ $card->priority === 'high' ? 'danger' : ($card->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
        Priority: {{ strtoupper($card->priority) }}
      </span>
      <span class="badge
        @if($card->status === 'done') bg-success
        @elseif($card->status === 'in_progress') bg-warning text-dark
        @elseif($card->status === 'code_review') bg-info
        @else bg-secondary
        @endif">
        {{ str_replace('_', ' ', strtoupper($card->status)) }}
      </span>
      @if($card->board)
        <span class="badge bg-light text-dark border">📋 {{ $card->board->board_name }}</span>
      @endif
      @if($card->board && $card->board->project)
        <span class="badge bg-light text-dark border">📁 {{ $card->board->project->project_name }}</span>
      @endif
    </div>
  </div>

  <!-- Card Info -->
  <div class="info-row">
    <div class="info-label">Deskripsi</div>
    <div>{{ $card->description ?: '-' }}</div>
  </div>

  <div class="info-row">
    <div class="info-label">Due Date</div>
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

  <div class="info-row">
    <div class="info-label">Estimasi Jam</div>
    <div>{{ $card->estimated_hours ? number_format($card->estimated_hours, 2) . ' jam' : '-' }}</div>
  </div>

  <div class="info-row">
    <div class="info-label">Aktual Jam</div>
    <div>{{ $card->actual_hours ? number_format($card->actual_hours, 2) . ' jam' : '-' }}</div>
  </div>

  <div class="info-row">
    <div class="info-label">Dibuat Oleh</div>
    <div>{{ $card->creator->fullname ?? $card->creator->username ?? '-' }}</div>
  </div>

  <div class="info-row">
    <div class="info-label">Ditugaskan Ke</div>
    <div>
      @if($card->assignees && $card->assignees->count() > 0)
        @foreach($card->assignees as $assignee)
          <span class="badge bg-primary me-1">{{ $assignee->fullname ?? $assignee->username }}</span>
        @endforeach
      @else
        <span class="text-muted">Belum ada yang ditugaskan</span>
      @endif
    </div>
  </div>
</div>

<!-- Update Status -->
<div class="detail-card">
  <h5 class="mb-3">Update Status</h5>
  <form method="POST" action="{{ route('member.cards.updateStatus', $card) }}">
    @csrf
    @method('PATCH')
    <div class="row align-items-end">
      <div class="col-md-6">
        <label class="form-label">Status Pekerjaan</label>
        <select name="status" class="form-select status-select" required>
          <option value="backlog" @selected($card->status === 'backlog')>Backlog</option>
          <option value="todo" @selected($card->status === 'todo')>To Do</option>
          <option value="in_progress" @selected($card->status === 'in_progress')>In Progress</option>
          <option value="code_review" @selected($card->status === 'code_review')>Code Review</option>
          <option value="testing" @selected($card->status === 'testing')>Testing</option>
          <option value="done" @selected($card->status === 'done')>Done</option>
        </select>
      </div>
      <div class="col-md-6">
        <button type="submit" class="btn btn-primary">Update Status</button>
      </div>
    </div>
  </form>
</div>

<!-- Comments Section (jika ada) -->
@if($card->comments && $card->comments->count() > 0)
<div class="detail-card">
  <h5 class="mb-3">💬 Comments ({{ $card->comments->count() }})</h5>
  @foreach($card->comments as $comment)
    <div class="border-bottom pb-3 mb-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <strong>{{ $comment->user->fullname ?? $comment->user->username ?? 'User' }}</strong>
        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
      </div>
      <p class="mb-0">{{ $comment->comment_text }}</p>
    </div>
  @endforeach
</div>
@endif

<!-- Subtasks Section (jika ada) -->
@if($card->subtasks && $card->subtasks->count() > 0)
<div class="detail-card">
  <h5 class="mb-3">✓ Subtasks ({{ $card->subtasks->count() }})</h5>
  <ul class="list-group">
    @foreach($card->subtasks as $subtask)
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <strong>{{ $subtask->subtask_title }}</strong>
          @if($subtask->description)
            <p class="text-muted small mb-0">{{ $subtask->description }}</p>
          @endif
        </div>
        <span class="badge bg-{{ $subtask->status === 'completed' ? 'success' : 'secondary' }}">
          {{ ucfirst($subtask->status) }}
        </span>
      </li>
    @endforeach
  </ul>
</div>
@endif

@endsection
