@extends('layouts.lead')
@section('title', 'Dashboard')
@section('leadContent')

<style>
.stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.stats-card h2 { font-size: 2.5rem; font-weight: 700; margin: 0; }
.stats-card .label { opacity: 0.9; font-size: 0.9rem; margin-top: 0.25rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-box { background: white; border-radius: 10px; padding: 1.25rem; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.08); border-left: 4px solid; transition: transform 0.2s; }
.stat-box:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.12); }
.stat-box .number { font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem; }
.stat-box .label { font-size: 0.85rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-backlog { border-left-color: #6c757d; }
.stat-backlog .number { color: #6c757d; }
.stat-todo { border-left-color: #0d6efd; }
.stat-todo .number { color: #0d6efd; }
.stat-progress { border-left-color: #ffc107; }
.stat-progress .number { color: #ffc107; }
.stat-review { border-left-color: #fd7e14; }
.stat-review .number { color: #fd7e14; }
.stat-done { border-left-color: #198754; }
.stat-done .number { color: #198754; }
.kanban-board { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-top: 1.5rem; }
.kanban-column { background: #f8f9fa; border-radius: 10px; padding: 1rem; min-height: 400px; }
.kanban-header { font-weight: 600; font-size: 1rem; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 2px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; }
.kanban-header .icon { font-size: 1.2rem; }
.kanban-card { background: white; border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.08); border-left: 4px solid; cursor: pointer; transition: all 0.2s; }
.kanban-card:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.15); transform: translateX(2px); }
.kanban-card .title { font-weight: 600; color: #212529; margin-bottom: 0.5rem; font-size: 0.95rem; line-height: 1.4; }
.kanban-card .description { font-size: 0.8rem; color: #6c757d; margin-bottom: 0.75rem; line-height: 1.5; }
.kanban-card .meta { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; font-size: 0.75rem; }
.badge-priority { font-size: 0.7rem; padding: 0.3rem 0.6rem; font-weight: 600; border-radius: 4px; }
.board-tag { display: inline-block; padding: 0.2rem 0.5rem; background: #e9ecef; border-radius: 4px; font-size: 0.7rem; color: #495057; }
.due-date { color: #6c757d; display: inline-flex; align-items: center; gap: 0.25rem; }
.due-date.overdue { color: #dc3545; font-weight: 600; }
.col-backlog { border-top: 3px solid #6c757d; background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%); }
.col-todo { border-top: 3px solid #0d6efd; background: linear-gradient(to bottom, #e7f1ff 0%, #ffffff 100%); }
.col-progress { border-top: 3px solid #ffc107; background: linear-gradient(to bottom, #fff8e1 0%, #ffffff 100%); }
.col-review { border-top: 3px solid #fd7e14; background: linear-gradient(to bottom, #fff0e6 0%, #ffffff 100%); }
.col-done { border-top: 3px solid #198754; background: linear-gradient(to bottom, #e8f5e9 0%, #ffffff 100%); }
.card-backlog { border-left-color: #6c757d; }
.card-todo { border-left-color: #0d6efd; }
.card-progress { border-left-color: #ffc107; }
.card-review { border-left-color: #fd7e14; }
.card-done { border-left-color: #198754; }
.empty-state { text-align: center; padding: 2rem 1rem; color: #adb5bd; }
.empty-state .icon { font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.5; }
</style>

<!-- Summary Stats -->
<div class="stats-card">
  <div class="row align-items-center">
    <div class="col-md-8">
      <h2>{{ $todo->count() + $inProgress->count() + $review->count() + $done->count() }}</h2>
      <div class="label">Total Cards Active</div>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
      <a href="{{ route('lead.cards.create') }}" class="btn btn-light btn-sm">
        <i class="bi bi-plus-lg"></i> New Card
      </a>
      <a href="{{ route('lead.boards.create') }}" class="btn btn-outline-light btn-sm ms-2">
        <i class="bi bi-plus-lg"></i> New Board
      </a>
    </div>
  </div>
</div>

<!-- Status Grid -->
<div class="stats-grid">
  <div class="stat-box stat-backlog">
    <div class="number">{{ $todo->where('status', 'backlog')->count() }}</div>
    <div class="label">Backlog</div>
  </div>
  <div class="stat-box stat-todo">
    <div class="number">{{ $todo->where('status', 'todo')->count() }}</div>
    <div class="label">To Do</div>
  </div>
  <div class="stat-box stat-progress">
    <div class="number">{{ $inProgress->count() }}</div>
    <div class="label">In Progress</div>
  </div>
  <div class="stat-box stat-review">
    <div class="number">{{ $review->count() }}</div>
    <div class="label">Review</div>
  </div>
  <div class="stat-box stat-done">
    <div class="number">{{ $done->count() }}</div>
    <div class="label">Done</div>
  </div>
</div>

<!-- Quick Actions -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-semibold">📋 Kanban Board</h5>
  <div class="d-flex gap-2">
    <a href="{{ route('lead.boards.index') }}" class="btn btn-outline-secondary btn-sm">Manage Boards</a>
    <a href="{{ route('lead.cards.index') }}" class="btn btn-outline-primary btn-sm">View All Cards</a>
  </div>
</div>

<!-- Kanban Columns -->
<div class="kanban-board">
  <!-- Backlog & To Do Column -->
  <div class="kanban-column col-todo">
    <div class="kanban-header">
      <span><span class="icon">📦</span> Backlog & To Do</span>
      <span class="badge bg-primary">{{ $todo->count() }}</span>
    </div>
    @forelse($todo as $c)
      <div class="kanban-card card-{{ $c->status === 'backlog' ? 'backlog' : 'todo' }}">
        <div class="title">{{ $c->card_title }}</div>
        @if($c->description)
          <div class="description">{{ Str::limit($c->description, 80) }}</div>
        @endif
        <div class="meta">
          <span class="badge badge-priority bg-{{ $c->priority === 'high' ? 'danger' : ($c->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
            {{ strtoupper($c->priority) }}
          </span>
          @if($c->board)
            <span class="board-tag">{{ $c->board->board_name }}</span>
          @endif
          @if($c->due_date)
            @php
              $dueDate = \Carbon\Carbon::parse($c->due_date);
              $isOverdue = $dueDate->isPast();
            @endphp
            <span class="due-date {{ $isOverdue ? 'overdue' : '' }}">
              📅 {{ $dueDate->format('M d') }}
            </span>
          @endif
        </div>
      </div>
    @empty
      <div class="empty-state">
        <div class="icon">📭</div>
        <div class="small">No pending tasks</div>
      </div>
    @endforelse
  </div>

  <!-- In Progress Column -->
  <div class="kanban-column col-progress">
    <div class="kanban-header">
      <span><span class="icon">🚧</span> In Progress</span>
      <span class="badge bg-warning text-dark">{{ $inProgress->count() }}</span>
    </div>
    @forelse($inProgress as $c)
      <div class="kanban-card card-progress">
        <div class="title">{{ $c->card_title }}</div>
        @if($c->description)
          <div class="description">{{ Str::limit($c->description, 80) }}</div>
        @endif
        <div class="meta">
          <span class="badge badge-priority bg-{{ $c->priority === 'high' ? 'danger' : ($c->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
            {{ strtoupper($c->priority) }}
          </span>
          @if($c->board)
            <span class="board-tag">{{ $c->board->board_name }}</span>
          @endif
          @if($c->due_date)
            @php
              $dueDate = \Carbon\Carbon::parse($c->due_date);
              $isOverdue = $dueDate->isPast();
            @endphp
            <span class="due-date {{ $isOverdue ? 'overdue' : '' }}">
              📅 {{ $dueDate->format('M d') }}
            </span>
          @endif
        </div>
      </div>
    @empty
      <div class="empty-state">
        <div class="icon">⚙️</div>
        <div class="small">Nothing in progress</div>
      </div>
    @endforelse
  </div>

  <!-- Code Review Column -->
  <div class="kanban-column col-review">
    <div class="kanban-header">
      <span><span class="icon">👀</span> Code Review</span>
      <span class="badge bg-warning">{{ $review->count() }}</span>
    </div>
    @forelse($review as $c)
      <div class="kanban-card card-review">
        <div class="title">{{ $c->card_title }}</div>
        @if($c->description)
          <div class="description">{{ Str::limit($c->description, 80) }}</div>
        @endif
        <div class="meta">
          <span class="badge badge-priority bg-{{ $c->priority === 'high' ? 'danger' : ($c->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
            {{ strtoupper($c->priority) }}
          </span>
          @if($c->board)
            <span class="board-tag">{{ $c->board->board_name }}</span>
          @endif
          @if($c->due_date)
            @php
              $dueDate = \Carbon\Carbon::parse($c->due_date);
              $isOverdue = $dueDate->isPast();
            @endphp
            <span class="due-date {{ $isOverdue ? 'overdue' : '' }}">
              📅 {{ $dueDate->format('M d') }}
            </span>
          @endif
        </div>
      </div>
    @empty
      <div class="empty-state">
        <div class="icon">✨</div>
        <div class="small">No reviews pending</div>
      </div>
    @endforelse
  </div>

  <!-- Done Column -->
  <div class="kanban-column col-done">
    <div class="kanban-header">
      <span><span class="icon">✅</span> Done</span>
      <span class="badge bg-success">{{ $done->count() }}</span>
    </div>
    @forelse($done as $c)
      <div class="kanban-card card-done">
        <div class="title">{{ $c->card_title }}</div>
        @if($c->description)
          <div class="description">{{ Str::limit($c->description, 80) }}</div>
        @endif
        <div class="meta">
          <span class="badge badge-priority bg-{{ $c->priority === 'high' ? 'danger' : ($c->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
            {{ strtoupper($c->priority) }}
          </span>
          @if($c->board)
            <span class="board-tag">{{ $c->board->board_name }}</span>
          @endif
          @if($c->due_date)
            <span class="due-date">
              📅 {{ \Carbon\Carbon::parse($c->due_date)->format('M d') }}
            </span>
          @endif
        </div>
      </div>
    @empty
      <div class="empty-state">
        <div class="icon">🎯</div>
        <div class="small">No completed tasks</div>
      </div>
    @endforelse
  </div>
</div>
@endsection
