@extends('layouts.member')
@section('title', 'Dashboard')
@section('memberContent')

<style>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: white; border-radius: 10px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.08); border-left: 4px solid; transition: transform 0.2s; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.12); }
.stat-card .number { font-size: 2.5rem; font-weight: 700; margin-bottom: 0.25rem; }
.stat-card .label { font-size: 0.85rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-total { border-left-color: #6366f1; }
.stat-total .number { color: #6366f1; }
.stat-todo { border-left-color: #0d6efd; }
.stat-todo .number { color: #0d6efd; }
.stat-progress { border-left-color: #ffc107; }
.stat-progress .number { color: #ffc107; }
.stat-review { border-left-color: #fd7e14; }
.stat-review .number { color: #fd7e14; }
.stat-done { border-left-color: #198754; }
.stat-done .number { color: #198754; }
.task-card { border-left: 4px solid #dee2e6; transition: all 0.2s; }
.task-card:hover { border-left-color: #6366f1; background-color: #f8f9fa; }
.status-badge { font-size: 0.75rem; padding: 0.35rem 0.75rem; font-weight: 600; border-radius: 6px; }
.priority-badge { font-size: 0.7rem; padding: 0.25rem 0.5rem; }
</style>

<div class="mb-4">
  <h3 class="mb-3">📋 Tugas Saya</h3>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card stat-total">
      <div class="number">{{ $stats['total'] }}</div>
      <div class="label">Total Tasks</div>
    </div>
    <div class="stat-card stat-todo">
      <div class="number">{{ $stats['todo'] }}</div>
      <div class="label">To Do</div>
    </div>
    <div class="stat-card stat-progress">
      <div class="number">{{ $stats['in_progress'] }}</div>
      <div class="label">In Progress</div>
    </div>
    <div class="stat-card stat-review">
      <div class="number">{{ $stats['review'] }}</div>
      <div class="label">Review</div>
    </div>
    <div class="stat-card stat-done">
      <div class="number">{{ $stats['done'] }}</div>
      <div class="label">Done</div>
    </div>
  </div>
</div>

<!-- Tasks List -->
<div class="card shadow-sm">
  <div class="card-header bg-white">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Daftar Tugas</h5>
      <span class="badge bg-primary">{{ $myTasks->count() }} tasks</span>
    </div>
  </div>
  <div class="card-body p-0">
    @forelse($myTasks as $task)
      <div class="task-card p-3 border-bottom">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div class="flex-grow-1">
            <h6 class="mb-1 fw-semibold">{{ $task->card_title }}</h6>
            @if($task->description)
              <p class="text-muted small mb-2">{{ Str::limit($task->description, 100) }}</p>
            @endif
            <div class="d-flex gap-2 align-items-center flex-wrap">
              <span class="status-badge
                @if($task->status === 'done') bg-success text-white
                @elseif($task->status === 'in_progress') bg-warning text-dark
                @elseif($task->status === 'code_review') bg-info text-white
                @else bg-secondary text-white
                @endif">
                {{ str_replace('_', ' ', strtoupper($task->status)) }}
              </span>

              <span class="priority-badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
                {{ strtoupper($task->priority) }}
              </span>

              @if($task->board)
                <span class="badge bg-light text-dark border">
                  📋 {{ $task->board->board_name }}
                </span>
              @endif

              @if($task->board && $task->board->project)
                <span class="badge bg-light text-dark border">
                  📁 {{ $task->board->project->project_name }}
                </span>
              @endif

              @if($task->due_date)
                @php
                  $dueDate = \Carbon\Carbon::parse($task->due_date);
                  $isOverdue = $dueDate->isPast() && $task->status !== 'done';
                @endphp
                <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-light text-dark border' }}">
                  📅 {{ $dueDate->format('d M Y') }}
                  @if($isOverdue) - OVERDUE @endif
                </span>
              @endif
            </div>
          </div>
          <div class="ms-3">
            <a href="{{ route('member.cards.show', $task) }}" class="btn btn-sm btn-primary">
              Lihat Detail
            </a>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center text-muted p-5">
        <div style="font-size: 3rem; opacity: 0.3;">📭</div>
        <p class="mb-0">Belum ada tugas yang ditugaskan</p>
        <small class="text-muted">Tugas akan muncul di sini ketika Team Lead menugaskan Anda</small>
      </div>
    @endforelse
  </div>
</div>
@endsection
