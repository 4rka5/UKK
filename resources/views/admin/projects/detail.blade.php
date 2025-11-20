@extends('layouts.admin')

@section('title', 'Detail Project - Review')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-folder2-open"></i> Detail Project - Review</h1>
            <p class="text-muted mb-0">Review dan approval project dari team lead</p>
        </div>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
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

    <div class="row g-4">
        <!-- Project Information -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Project</h5>
                </div>
                <div class="card-body">
                    <h4 class="mb-3">{{ $project->project_name }}</h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong><i class="bi bi-person"></i> Dibuat oleh:</strong><br>
                                {{ $project->owner->fullname ?? $project->owner->username }}
                                <span class="badge bg-info">{{ ucfirst($project->owner->role) }}</span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong><i class="bi bi-calendar"></i> Deadline:</strong><br>
                                <span class="badge bg-{{ $project->deadline < now() ? 'danger' : 'info' }}">
                                    {{ $project->deadline->format('d F Y') }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong><i class="bi bi-flag"></i> Status:</strong><br>
                                @if($project->status === 'active')
                                    <span class="badge bg-primary"><i class="bi bi-play-circle"></i> Aktif</span>
                                @elseif($project->status === 'done')
                                    @if($project->reviewed_by)
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Disetujui</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock-history"></i> Menunggu Review</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($project->status) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong><i class="bi bi-file-text"></i> Deskripsi Project:</strong>
                        <div class="p-3 bg-light rounded mt-2">
                            {{ $project->description }}
                        </div>
                    </div>

                    @if($project->reviewed_by && $project->reviewed_at)
                        <div class="mt-3 p-3 border rounded">
                            <p class="mb-1">
                                <strong><i class="bi bi-person-check"></i> Direview oleh:</strong> 
                                {{ $project->reviewer->fullname ?? $project->reviewer->username }}
                            </p>
                            <p class="mb-0 text-muted">
                                <small><i class="bi bi-clock"></i> {{ $project->reviewed_at->format('d F Y H:i') }}</small>
                            </p>
                            @if($project->rejection_reason)
                                <p class="mb-0 mt-2">
                                    <strong>Alasan Penolakan:</strong><br>
                                    <span class="text-danger">{{ $project->rejection_reason }}</span>
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($project->status === 'done' && !$project->reviewed_by)
                <!-- Approval Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-check2-square"></i> Review Project</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="bi bi-info-circle"></i> Team lead mengajukan project ini untuk review. Setujui untuk menandai selesai.
                        </p>
                        <div class="row g-3">
                            <div class="col-12">
                                <form action="{{ route('admin.projects.approve', $project) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Setujui project ini? Semua anggota tim akan menjadi idle dan subtasks menjadi done.')">
                                        <i class="bi bi-check-circle-fill"></i> Setujui Project
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($project->status === 'done' && $project->reviewed_by)
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> <strong>Project Disetujui</strong>
                    <p class="mb-0 mt-2">Project telah disetujui oleh {{ $project->reviewer->fullname ?? 'Admin' }} pada {{ $project->reviewed_at->format('d M Y H:i') }}</p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history"></i> Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <p class="mb-1 small"><strong>Project Dibuat</strong></p>
                                <p class="mb-0 text-muted small">{{ $project->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($project->status === 'active')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>Project Aktif</strong></p>
                                    <p class="mb-0 text-muted small">Sedang dikerjakan...</p>
                                </div>
                            </div>
                        @endif

                        @if($project->status === 'done')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $project->reviewed_by ? 'success' : 'warning' }}"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>{{ $project->reviewed_by ? 'Disetujui' : 'Menunggu Review' }}</strong></p>
                                    <p class="mb-0 text-muted small">{{ $project->reviewed_at ? $project->reviewed_at->format('d M Y H:i') : 'Diajukan untuk review' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    list-style: none;
    padding-left: 0;
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 30px;
    padding-bottom: 20px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 6px;
    top: 20px;
    bottom: -20px;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 5px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    padding-top: 0;
}
</style>
@endsection
