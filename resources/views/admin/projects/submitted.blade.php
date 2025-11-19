@extends('layouts.admin')
@section('title','Project Diajukan')
@section('adminContent')

<style>
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
</style>

<div class="mb-3">
  <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
  <div>
    <h3 class="mb-1">📋 Project Diajukan</h3>
    <p class="text-muted mb-0 small">Daftar project yang diajukan oleh Team Lead untuk dikerjakan</p>
  </div>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Search & Filter -->
<div class="search-filter-bar">
  <form method="GET" action="{{ route('admin.projects.submitted') }}" class="row g-3">
    <div class="col-md-10">
      <label class="form-label small text-muted mb-1">
        <i class="bi bi-search"></i> Cari Project
      </label>
      <input 
        type="text" 
        name="search" 
        class="form-control" 
        placeholder="Nama project atau team lead..." 
        value="{{ request('search') }}"
      >
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-search"></i> Cari
      </button>
    </div>
  </form>
</div>

<!-- Projects List -->
@if($projects->count() > 0)
  <div class="mb-3">
    <small class="text-muted">Menampilkan {{ $projects->firstItem() ?? 0 }} - {{ $projects->lastItem() ?? 0 }} dari {{ $projects->total() }} project diajukan</small>
  </div>

  <div class="row g-3">
    @foreach($projects as $project)
      <div class="col-12">
        <div class="project-card p-3">
          <div class="row align-items-center g-3">
            <!-- Project Icon & Info -->
            <div class="col-12 col-md-5">
              <div class="d-flex align-items-start gap-3">
                <div class="project-icon text-white">
                  <i class="bi bi-folder-fill"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="project-name">{{ $project->project_name }}</div>
                  <div class="project-meta">
                    <i class="bi bi-person-badge"></i>
                    {{ $project->owner->fullname ?? $project->owner->username }}
                  </div>
                  @if($project->description)
                    <div class="project-meta mt-1">
                      <i class="bi bi-text-paragraph"></i>
                      {{ Str::limit($project->description, 80) }}
                    </div>
                  @endif
                </div>
              </div>
            </div>

            <!-- Deadline & Status -->
            <div class="col-6 col-md-3">
              <div class="mb-2">
                <small class="text-muted d-block mb-1">
                  <i class="bi bi-calendar-event"></i> Deadline
                </small>
                @if($project->deadline)
                  <span class="badge bg-{{ $project->deadline < now() ? 'danger' : 'info' }}">
                    {{ $project->deadline->format('d M Y') }}
                  </span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </div>
              <div>
                <small class="text-muted d-block mb-1">
                  <i class="bi bi-clock-history"></i> Diajukan
                </small>
                <small>{{ $project->reviewed_at ? $project->reviewed_at->diffForHumans() : '-' }}</small>
              </div>
            </div>

            <!-- Actions -->
            <div class="col-6 col-md-4 d-flex align-items-center justify-content-end">
              <div class="action-btns">
                <a href="{{ route('admin.projects.members', $project) }}" class="btn btn-sm btn-outline-info" title="Lihat Anggota">
                  <i class="bi bi-people-fill"></i>
                  <span>Anggota</span>
                </a>
                <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-outline-secondary" title="Edit Project">
                  <i class="bi bi-pencil-square"></i>
                  <span>Edit</span>
                </a>
                <a href="{{ route('admin.projects.report', $project) }}" class="btn btn-sm btn-outline-primary" title="Lihat Laporan">
                  <i class="bi bi-file-earmark-text"></i>
                  <span>Laporan</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <!-- Pagination -->
  <div class="mt-4">
    {{ $projects->links() }}
  </div>
@else
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
      <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
      </div>
      <p class="text-muted mb-2">Belum ada project yang diajukan</p>
      <small class="text-muted">Project dengan status "Aktif" akan muncul di sini</small>
    </div>
  </div>
@endif

@endsection
