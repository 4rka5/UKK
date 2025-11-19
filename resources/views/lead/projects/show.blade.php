@extends('layouts.lead')

@section('title', 'Detail Project')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-folder2-open"></i> Detail Project</h1>
            <p class="text-muted mb-0">Informasi lengkap project</p>
        </div>
        <a href="{{ route('lead.projects.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
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
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="bi bi-calendar"></i> Deadline:</strong><br>
                                <span class="badge bg-{{ $project->deadline < now() ? 'danger' : 'info' }}">
                                    {{ $project->deadline->format('d F Y') }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="bi bi-flag"></i> Status:</strong><br>
                                @if($project->status === 'draft')
                                    <span class="badge bg-secondary"><i class="bi bi-file-earmark"></i> Draft</span>
                                @elseif($project->status === 'pending')
                                    <span class="badge bg-warning"><i class="bi bi-clock-history"></i> Pending Approval</span>
                                @elseif($project->status === 'approved')
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved</span>
                                @elseif($project->status === 'rejected')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rejected</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong><i class="bi bi-file-text"></i> Deskripsi:</strong>
                        <p class="mt-2 text-muted">{{ $project->description }}</p>
                    </div>

                    @if($project->status === 'rejected' && $project->rejection_reason)
                        <div class="alert alert-danger">
                            <strong><i class="bi bi-exclamation-triangle"></i> Alasan Penolakan:</strong>
                            <p class="mb-0 mt-2">{{ $project->rejection_reason }}</p>
                        </div>
                    @endif

                    @if($project->reviewed_by && $project->reviewed_at)
                        <div class="mt-3 p-3 bg-light rounded">
                            <p class="mb-1">
                                <strong><i class="bi bi-person-check"></i> Direview oleh:</strong> 
                                {{ $project->reviewer->fullname ?? $project->reviewer->username }}
                            </p>
                            <p class="mb-0 text-muted">
                                <small><i class="bi bi-clock"></i> {{ $project->reviewed_at->format('d F Y H:i') }}</small>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            @if(in_array($project->status, ['draft', 'rejected']))
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-tools"></i> Aksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="{{ route('lead.projects.edit', $project) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Project
                            </a>
                            
                            <form action="{{ route('lead.projects.submit', $project) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Ajukan project ini untuk approval admin?')">
                                    <i class="bi bi-send"></i> Ajukan untuk Approval
                                </button>
                            </form>
                            
                            <form action="{{ route('lead.projects.destroy', $project) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Yakin hapus project ini?')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
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
                    <ul class="timeline">
                        <li class="timeline-item">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <p class="mb-1 small"><strong>Project Dibuat</strong></p>
                                <p class="mb-0 text-muted small">{{ $project->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </li>
                        
                        @if($project->status === 'pending')
                            <li class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>Menunggu Review Admin</strong></p>
                                    <p class="mb-0 text-muted small">{{ $project->updated_at->format('d M Y H:i') }}</p>
                                </div>
                            </li>
                        @endif

                        @if($project->status === 'approved')
                            <li class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>Project Disetujui</strong></p>
                                    <p class="mb-0 text-muted small">{{ $project->reviewed_at->format('d M Y H:i') }}</p>
                                </div>
                            </li>
                        @endif

                        @if($project->status === 'rejected')
                            <li class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>Project Ditolak</strong></p>
                                    <p class="mb-0 text-muted small">{{ $project->reviewed_at->format('d M Y H:i') }}</p>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            @if($project->status === 'pending')
                <div class="alert alert-warning">
                    <i class="bi bi-clock-history"></i> <strong>Menunggu Approval</strong>
                    <p class="mb-0 mt-2 small">Project sedang direview oleh admin. Anda akan menerima notifikasi setelah direview.</p>
                </div>
            @endif
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
