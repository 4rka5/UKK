@extends('layouts.lead')
@section('title','Cards')
@section('leadContent')
<style>
.bg-purple {
  background-color: #6f42c1 !important;
}
.card-item {
  transition: all 0.2s ease;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  background: white;
}
.card-item:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}
.card-title-text {
  font-weight: 600;
  font-size: 1rem;
  color: #1f2937;
  margin-bottom: 0.25rem;
}
.card-meta {
  font-size: 0.875rem;
  color: #6b7280;
}
.priority-badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-weight: 500;
}
@media (max-width: 768px) {
  .card-item .row > div {
    width: 100%;
    margin-bottom: 0.5rem;
  }
}
</style>

<div class="mb-3">
  <a href="{{ route('lead.dashboard') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>

<div class="mb-4">
  <h3 class="mb-1">📋 Manage Cards</h3>
  <p class="text-muted mb-0 small">Kelola semua cards dalam project Anda</p>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="text-muted mb-1 small">Total Cards</p>
            <h3 class="mb-0">{{ $cards->total() }}</h3>
          </div>
          <div class="bg-primary bg-opacity-10 p-3 rounded">
            <i class="bi bi-clipboard-check-fill text-primary fs-4"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="text-muted mb-1 small">In Progress</p>
            <h3 class="mb-0 text-warning">{{ $cards->where('status', 'in_progress')->count() }}</h3>
          </div>
          <div class="bg-warning bg-opacity-10 p-3 rounded">
            <i class="bi bi-hourglass-split text-warning fs-4"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="text-muted mb-1 small">Completed</p>
            <h3 class="mb-0 text-success">{{ $cards->where('status', 'done')->count() }}</h3>
          </div>
          <div class="bg-success bg-opacity-10 p-3 rounded">
            <i class="bi bi-check-circle-fill text-success fs-4"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="text-muted mb-1 small">High Priority</p>
            <h3 class="mb-0 text-danger">{{ $cards->where('priority', 'high')->count() }}</h3>
          </div>
          <div class="bg-danger bg-opacity-10 p-3 rounded">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Search & Filter Bar -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('lead.cards.index') }}" class="row g-3">
      <!-- Search -->
      <div class="col-md-4">
        <label class="form-label small text-muted mb-1">
          <i class="bi bi-search"></i> Search Card
        </label>
        <input type="text" name="search" class="form-control" placeholder="Cari nama card..." value="{{ request('search') }}">
      </div>

      <!-- Filter Project -->
      <div class="col-md-3">
        <label class="form-label small text-muted mb-1">
          <i class="bi bi-folder"></i> Project
        </label>
        <select name="project_id" class="form-select">
          <option value="">Semua Project</option>
          @foreach($projects as $p)
            <option value="{{ $p->id }}" @selected((string)request('project_id')===(string)$p->id)>{{ $p->project_name }}</option>
          @endforeach
        </select>
      </div>

      <!-- Filter Status -->
      <div class="col-md-2">
        <label class="form-label small text-muted mb-1">
          <i class="bi bi-flag"></i> Status
        </label>
        <select name="status" class="form-select">
          <option value="">Semua Status</option>
          <option value="todo" @selected(request('status')==='todo')>To Do</option>
          <option value="in_progress" @selected(request('status')==='in_progress')>In Progress</option>
          <option value="review" @selected(request('status')==='review')>Review</option>
          <option value="done" @selected(request('status')==='done')>Done</option>
        </select>
      </div>

      <!-- Filter Priority -->
      <div class="col-md-2">
        <label class="form-label small text-muted mb-1">
          <i class="bi bi-lightning"></i> Priority
        </label>
        <select name="priority" class="form-select">
          <option value="">Semua Priority</option>
          <option value="high" @selected(request('priority')==='high')>High</option>
          <option value="medium" @selected(request('priority')==='medium')>Medium</option>
          <option value="low" @selected(request('priority')==='low')>Low</option>
        </select>
      </div>

      <!-- Buttons -->
      <div class="col-md-1 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-funnel"></i>
        </button>
        @if(request()->hasAny(['search', 'project_id', 'status', 'priority']))
          <a href="{{ route('lead.cards.index') }}" class="btn btn-outline-secondary" title="Reset">
            <i class="bi bi-x-lg"></i>
          </a>
        @endif
      </div>
    </form>
  </div>
</div>

<!-- Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <small class="text-muted">
      Menampilkan {{ $cards->firstItem() ?? 0 }} - {{ $cards->lastItem() ?? 0 }} dari {{ $cards->total() }} cards
    </small>
  </div>
  @php
    // Cek apakah ada project yang active (tidak done)
    $hasActiveProject = \App\Models\Project::whereHas('members', function($q) {
        $q->where('user_id', auth()->id())->where('role', 'team_lead');
    })->where('status', '!=', 'done')->exists();
  @endphp
  @if(auth()->user()->role === 'admin' || $hasActiveProject)
    <a href="{{ route('lead.cards.create') }}" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-circle"></i> Tambah Card
    </a>
  @else
    <button class="btn btn-secondary btn-sm" disabled title="Project sudah selesai">
      <i class="bi bi-plus-circle"></i> Tambah Card
    </button>
  @endif
</div>

<div class="row g-3">
  @forelse($cards as $c)
    <div class="col-12">
      <div class="card-item p-3">
        <div class="row align-items-center g-3">
          <!-- Card Icon & Title -->
          <div class="col-12 col-md-4">
            <div class="d-flex align-items-center gap-3">
              @php
                $iconBg = match(data_get($c,'status')) {
                  'todo' => 'bg-secondary',
                  'in_progress' => 'bg-warning',
                  'review' => 'bg-purple',
                  'done' => 'bg-success',
                  default => 'bg-secondary'
                };
              @endphp
              <div class="card-icon {{ $iconBg }} text-white">
                📝
              </div>
              <div class="flex-grow-1">
                <div class="card-title-text">{{ data_get($c,'card_title') }}</div>
                <div class="card-meta">
                  <i class="bi bi-folder me-1"></i>{{ data_get($c,'project.project_name','-') }}
                </div>
              </div>
            </div>
          </div>

          <!-- Assigned To -->
          <div class="col-6 col-md-2">
            <small class="text-muted d-block mb-1">Assigned To</small>
            @if($c->assignees && $c->assignees->count() > 0)
              @foreach($c->assignees as $assignee)
                <span class="badge bg-info text-dark d-block mb-1" title="Status: {{ $assignee->pivot->assignment_status }}">
                  <i class="bi bi-person-fill me-1"></i>{{ $assignee->fullname }}
                </span>
              @endforeach
            @else
              <span class="text-muted">Belum ditugaskan</span>
            @endif
          </div>

          <!-- Status & Priority -->
          <div class="col-6 col-md-2">
            <small class="text-muted d-block mb-1">Status</small>
            @php
              $statusBadge = match(data_get($c,'status')) {
                'todo' => 'bg-secondary',
                'in_progress' => 'bg-warning text-dark',
                'review' => 'bg-purple text-white',
                'done' => 'bg-success',
                default => 'bg-secondary'
              };
              $statusLabel = match(data_get($c,'status')) {
                'todo' => 'To Do',
                'in_progress' => 'In Progress',
                'review' => 'Review',
                'done' => 'Done',
                default => ucfirst(str_replace('_',' ',data_get($c,'status')))
              };
            @endphp
            <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
          </div>

          <div class="col-6 col-md-2">
            <small class="text-muted d-block mb-1">Priority & Due Date</small>
            @php
              $priorityColor = match(data_get($c,'priority')) {
                'high' => 'danger',
                'medium' => 'warning',
                'low' => 'success',
                default => 'secondary'
              };
            @endphp
            <span class="badge bg-{{ $priorityColor }} priority-badge">{{ ucfirst(data_get($c,'priority','')) }}</span>
            <div class="small text-muted mt-1">
              <i class="bi bi-calendar3"></i> {{ data_get($c,'due_date','-') }}
            </div>
          </div>

          <!-- Actions -->
          <div class="col-12 col-md-2">
            <div class="d-flex gap-2 justify-content-end flex-wrap">
              <form action="{{ route('lead.cards.move',$c) }}" method="POST" class="flex-grow-1">
                @csrf @method('PATCH')
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" title="Change Status">
                  @foreach($statuses as $k)
                    @php($label = match($k){
                      'todo' => 'To Do',
                      'in_progress' => 'In Progress',
                      'review' => 'Review',
                      'done' => 'Done',
                      default => ucfirst(str_replace('_',' ',$k))
                    })
                    <option value="{{ $k }}" @selected(data_get($c,'status')===$k)>{{ $label }}</option>
                  @endforeach
                </select>
              </form>
              <a href="{{ route('lead.cards.edit',$c) }}" class="btn btn-sm btn-outline-primary" title="Edit Card">
                <i class="bi bi-pencil-fill"></i>
              </a>
              <form action="{{ route('lead.cards.destroy',$c) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus card {{ data_get($c,'card_title') }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" title="Hapus Card">
                  <i class="bi bi-trash-fill"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12">
      <div class="text-center py-5">
        <i class="bi bi-clipboard-x" style="font-size: 4rem; color: #e5e7eb;"></i>
        <p class="text-muted mt-3 mb-0">Belum ada card</p>
      </div>
    </div>
  @endforelse
</div>

@if($cards->hasPages())
  <div class="mt-4">
    {{ $cards->links() }}
  </div>
@endif

@endsection
