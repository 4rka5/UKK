@extends('layouts.admin')
@section('title','Kelola Project')
@section('adminContent')

<style>
.stat-card {
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid #e5e7eb;
  transition: all 0.2s ease;
}
.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}
.project-card {
  transition: all 0.2s ease;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  background: white;
}
.project-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.project-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  flex-shrink: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.project-name {
  font-weight: 600;
  font-size: 1.125rem;
  color: #1f2937;
  margin-bottom: 0.25rem;
}
.project-meta {
  font-size: 0.875rem;
  color: #6b7280;
}
.action-btns {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}
.action-btns .btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.875rem;
  white-space: nowrap;
}
.search-filter-bar {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
}
/* Responsive Adjustments */
@media (max-width: 991px) {
  .project-icon {
    width: 48px;
    height: 48px;
    font-size: 1.5rem;
  }
  .project-name {
    font-size: 1rem;
  }
}
@media (max-width: 768px) {
  .stat-card {
    margin-bottom: 0.75rem;
  }
}
@media (max-width: 767px) {
  .project-card {
    padding: 1rem !important;
  }
  .project-icon {
    width: 40px;
    height: 40px;
    font-size: 1.25rem;
  }
  .action-btns {
    width: 100%;
    justify-content: stretch;
  }
  .action-btns .btn {
    flex: 1;
    justify-content: center;
    padding: 0.5rem 0.25rem;
  }
  .action-btns .btn span {
    display: none;
  }
  .action-btns .btn i {
    margin: 0 !important;
  }
}
@media (max-width: 575px) {
  .project-card .col-6 {
    width: 50% !important;
  }
}
</style>

<div class="mb-3">
  <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
  <div>
    <h3 class="mb-1">📁 Kelola Project</h3>
    <p class="text-muted mb-0 small">Manajemen project tim</p>
  </div>
  <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Tambah Project
  </a>
</div>

@php
  $totalProjects = $projects->total();
  $activeProjects = \App\Models\Project::whereHas('boards', function($q) {
    $q->whereHas('cards', function($cardQuery) {
      $cardQuery->whereIn('status', ['todo', 'in_progress']);
    });
  })->count();
  $completedProjects = \App\Models\Project::whereDoesntHave('boards', function($q) {
    $q->whereHas('cards', function($cardQuery) {
      $cardQuery->whereIn('status', ['todo', 'in_progress']);
    });
  })->whereHas('boards.cards')->count();
  $overdueProjects = \App\Models\Project::where('deadline', '<', now())->count();
@endphp

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
  <div class="col-6">
    <div class="stat-card bg-white">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
          <i class="bi bi-play-circle-fill"></i>
        </div>
        <div>
          <div class="text-muted small">Aktif</div>
          <div class="h4 mb-0 fw-bold">{{ $stats['active'] }}</div>
          <small class="text-muted">Sedang dikerjakan</small>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6">
    <div class="stat-card bg-white">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-success bg-opacity-10 text-success">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
          <div class="text-muted small">Done</div>
          <div class="h4 mb-0 fw-bold">{{ $stats['done'] }}</div>
          <small class="text-muted">Sudah selesai</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Search & Filter Bar -->
<div class="search-filter-bar">
  <form method="GET" action="{{ route('admin.projects.index') }}">
    <div class="row g-3 align-items-end">
      <div class="col-12 col-md-5">
        <label class="form-label small fw-semibold mb-1">
          <i class="bi bi-search"></i> Cari Project
        </label>
        <input type="text" name="search" class="form-control" 
               placeholder="Cari nama project atau owner..." 
               value="{{ request('search') }}">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold mb-1">
          <i class="bi bi-calendar-event"></i> Deadline
        </label>
        <select name="deadline_status" class="form-select">
          <option value="">Semua</option>
          <option value="overdue" {{ request('deadline_status') === 'overdue' ? 'selected' : '' }}>Terlambat</option>
          <option value="upcoming" {{ request('deadline_status') === 'upcoming' ? 'selected' : '' }}>7 Hari Lagi</option>
          <option value="active" {{ request('deadline_status') === 'active' ? 'selected' : '' }}>Masih Aman</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small fw-semibold mb-1">
          <i class="bi bi-activity"></i> Status
        </label>
        <select name="status" class="form-select">
          <option value="">Semua</option>
          <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
          <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">
            <i class="bi bi-search"></i> <span class="d-none d-md-inline">Cari</span>
          </button>
          <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
            <i class="bi bi-arrow-clockwise"></i>
          </a>
        </div>
      </div>
    </div>
  </form>
</div>

<div class="row g-3">
  @forelse($projects as $p)
    <div class="col-12">
      <div class="project-card p-3">
        <div class="row align-items-center g-3">
          <!-- Project Icon & Name -->
          <div class="col-12 col-md-5">
            <div class="d-flex align-items-center gap-3">
              <div class="project-icon text-white">
                📁
              </div>
              <div class="flex-grow-1">
                <div class="project-name">{{ $p->project_name }}</div>
                <div class="project-meta">
                  <i class="bi bi-person-circle me-1"></i>{{ $p->owner->fullname ?? $p->owner->username ?? '-' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Deadline & Status -->
          <div class="col-6 col-md-2">
            <small class="text-muted d-block mb-1">Deadline</small>
            @if($p->deadline)
              @php
                $deadline = \Carbon\Carbon::parse($p->deadline);
                $isOverdue = $deadline->isPast();
              @endphp
              <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-info' }}">
                <i class="bi bi-calendar-event me-1"></i>{{ $deadline->format('d M Y') }}
              </span>
            @else
              <span class="badge bg-secondary">No deadline</span>
            @endif
            <div class="mt-2">
              <small class="text-muted d-block mb-1">Status</small>
              @if($p->status === 'active')
                <span class="badge bg-primary"><i class="bi bi-play-circle"></i> Aktif</span>
              @elseif($p->status === 'done')
                @if($p->reviewed_by)
                  <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Disetujui</span>
                @else
                  <span class="badge bg-warning text-dark"><i class="bi bi-clock-history"></i> Menunggu Review</span>
                @endif
              @endif
            </div>
          </div>

          <!-- Members Count -->
          <div class="col-6 col-md-2">
            <small class="text-muted d-block mb-1">Anggota</small>
            <span class="fw-semibold">
              <i class="bi bi-people-fill text-primary me-1"></i>{{ $p->members->count() ?? 0 }} orang
            </span>
          </div>

          <!-- Actions -->
          <div class="col-12 col-md-3">
            <div class="action-btns justify-content-end">
              @if($p->status === 'done' && !$p->reviewed_by)
                <form action="{{ route('admin.projects.approve', $p) }}" method="POST" style="display: inline;">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success" title="Setujui Project" onclick="return confirm('Setujui project ini? Semua anggota tim akan menjadi idle dan subtasks menjadi done.')">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Setujui</span>
                  </button>
                </form>
              @endif
              <a href="{{ route('admin.projects.report', $p) }}" class="btn btn-sm btn-outline-primary" title="Lihat Laporan">
                <i class="bi bi-file-earmark-text"></i>
                <span>Laporan</span>
              </a>
              <a href="{{ route('admin.projects.members', $p) }}" class="btn btn-sm btn-outline-info" title="Kelola Anggota">
                <i class="bi bi-people-fill"></i>
                <span class="d-none d-lg-inline">Anggota</span>
              </a>
              <a href="{{ route('admin.projects.edit',$p) }}" class="btn btn-sm btn-outline-secondary" title="Edit Project">
                <i class="bi bi-pencil-fill"></i>
                <span class="d-none d-lg-inline">Edit</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12">
      <div class="text-center py-5 bg-white rounded-3 border">
        <i class="bi bi-folder-x" style="font-size: 4rem; color: #e5e7eb;"></i>
        <p class="text-muted mt-3 mb-2 fw-semibold">Tidak ada project ditemukan</p>
        @if(request()->hasAny(['search', 'deadline_status', 'status']))
          <p class="text-muted small mb-3">Coba ubah filter pencarian Anda</p>
          <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-clockwise"></i> Reset Filter
          </a>
        @else
          <p class="text-muted small mb-3">Belum ada project yang terdaftar</p>
          <a href="{{ route('admin.projects.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Project Pertama
          </a>
        @endif
      </div>
    </div>
  @endforelse
</div>

@if($projects->hasPages())
  <div class="d-flex justify-content-center mt-4">
    {{ $projects->links('pagination::bootstrap-5') }}
  </div>
@endif

<script>
function deleteProject(id, name) {
  if (confirm('Yakin hapus project "' + name + '"?\n\nProject dengan data historis (boards/members) tidak bisa dihapus.')) {
    // Create form dynamically
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/projects/' + id;
    
    // CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Method DELETE
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    form.submit();
  }
}
</script>

@endsection
