@extends('layouts.lead')

@section('title', 'Project Saya')

@section('leadContent')
<style>
.project-item {
  transition: all 0.2s ease;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  background: white;
}
.project-item:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.project-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}
.project-title-text {
  font-weight: 600;
  font-size: 1rem;
  color: #1f2937;
  margin-bottom: 0.25rem;
}
.project-meta {
  font-size: 0.875rem;
  color: #6b7280;
}
@media (max-width: 768px) {
  .project-item .row > div {
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
  <h3 class="mb-1">📂 Project Saya</h3>
  <p class="text-muted mb-0 small">Kelola dan ajukan project yang sudah selesai untuk direview admin</p>
</div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Aktif</p>
                            <h3 class="mb-0 text-primary">{{ $stats['active'] }}</h3>
                            <small class="text-muted">Sedang dikerjakan</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-play-circle-fill text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Done</p>
                            <h3 class="mb-0 text-success">{{ $stats['done'] }}</h3>
                            <small class="text-muted">Sudah selesai</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <small class="text-muted">
                Menampilkan {{ $projects->firstItem() ?? 0 }} - {{ $projects->lastItem() ?? 0 }} dari {{ $projects->total() }} project
            </small>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($projects->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 35%;">Nama Project</th>
                                <th style="width: 15%;">Deadline</th>
                                <th style="width: 15%;" class="text-center">Status</th>
                                <th style="width: 15%;">Direview</th>
                                <th style="width: 10%;">Tanggal</th>
                                <th style="width: 10%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="project-icon bg-primary bg-opacity-10 text-primary me-3">
                                                <i class="bi bi-folder-fill"></i>
                                            </div>
                                            <div>
                                                <div class="project-title-text">{{ $project->project_name }}</div>
                                                <small class="text-muted">{{ Str::limit($project->description, 60) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $project->deadline < now() ? 'danger' : 'info' }}">
                                            <i class="bi bi-calendar"></i> {{ $project->deadline->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($project->status === 'active')
                                            <span class="badge bg-primary"><i class="bi bi-play-circle"></i> Aktif</span>
                                        @elseif($project->status === 'done')
                                            @if($project->reviewed_by)
                                                <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Disetujui</span>
                                            @else
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock-history"></i> Menunggu Review</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->reviewer)
                                            <small>{{ $project->reviewer->fullname ?? $project->reviewer->username }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->reviewed_at)
                                            <small class="text-muted">{{ $project->reviewed_at->format('d M Y') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('lead.projects.show', $project) }}" class="btn btn-outline-info btn-sm" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            @if($project->status === 'active')
                                                <form action="{{ route('lead.projects.submitProject', $project) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning btn-sm" title="Ajukan Project" onclick="return confirm('Ajukan project ini untuk direview admin?')">
                                                        <i class="bi bi-send-check"></i>
                                                    </button>
                                                </form>
                                            @elseif($project->status === 'done' && $project->reviewed_by)
                                                <span class="badge bg-success ms-1"><i class="bi bi-check-circle-fill"></i> Selesai</span>
                                            @elseif($project->status === 'done' && !$project->reviewed_by)
                                                <span class="badge bg-warning text-dark ms-1"><i class="bi bi-hourglass-split"></i> Direview</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-muted">Belum ada project yang ditugaskan kepada Anda.</p>
                    <p class="text-muted small">Hubungi admin untuk mendapatkan project baru.</p>
                </div>
            @endif
        </div>
        @if($projects->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
@endsection
