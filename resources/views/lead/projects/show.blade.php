@extends('layouts.lead')

@section('title', 'Detail Project')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-folder2-open"></i> Detail Project</h1>
            <p class="text-muted mb-0">Informasi lengkap project yang ditugaskan</p>
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
                                @if($project->status === 'pending')
                                    <span class="badge bg-warning"><i class="bi bi-clock-history"></i> Menunggu Verifikasi</span>
                                @elseif($project->status === 'active')
                                    <span class="badge bg-primary"><i class="bi bi-play-circle"></i> Aktif</span>
                                @elseif($project->status === 'approved')
                                    <span class="badge bg-info"><i class="bi bi-check"></i> Approved</span>
                                @elseif($project->status === 'completed')
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Selesai</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong><i class="bi bi-file-text"></i> Deskripsi:</strong>
                        <p class="mt-2 text-muted">{{ $project->description }}</p>
                    </div>

                    @if($project->status === 'active' && $project->rejection_reason)
                        <div class="alert alert-warning mt-3">
                            <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Feedback dari Admin</h6>
                            <p class="mb-0">{{ $project->rejection_reason }}</p>
                            <hr>
                            <p class="mb-0 small">
                                <i class="bi bi-arrow-right"></i> Lanjutkan perbaikan project sesuai feedback ini.
                            </p>
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
                            <p class="mb-1 small"><strong>Project Diajukan</strong></p>
                            <p class="mb-0 text-muted small">{{ $project->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </li>                        @if($project->status === 'pending')
                            <li class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>Menunggu Verifikasi</strong></p>
                                    <p class="mb-0 text-muted small">Sedang diverifikasi admin...</p>
                                </div>
                            </li>
                        @endif

                        @if(in_array($project->status, ['active', 'approved']))
                            <li class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>Project Aktif</strong></p>
                                    <p class="mb-0 text-muted small">Sedang dikerjakan...</p>
                                </div>
                            </li>
                        @endif

                        @if($project->status === 'completed')
                            <li class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>Selesai</strong></p>
                                    <p class="mb-0 text-muted small">{{ $project->reviewed_at ? $project->reviewed_at->format('d M Y H:i') : '-' }}</p>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            @if($project->status === 'pending')
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> <strong>Status: Menunggu Verifikasi</strong>
                    <p class="mb-0 mt-2 small">Project Anda sedang diverifikasi oleh admin. Anda akan menerima notifikasi saat project ditandai selesai atau perlu perbaikan.</p>
                </div>
            @elseif(in_array($project->status, ['active', 'approved']))
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-check-circle"></i> Ajukan Project Selesai</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Jika project sudah selesai dikerjakan, ajukan ke admin untuk verifikasi:</p>
                        <form action="{{ route('lead.projects.submitCompletion', $project) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Ajukan project ini sebagai selesai?')">
                                <i class="bi bi-send-check"></i> Ajukan Sebagai Selesai
                            </button>
                        </form>
                    </div>
                </div>
            @elseif($project->status === 'completed')
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <strong>Project Selesai</strong>
                    <p class="mb-0 mt-2 small">Selamat! Project telah selesai. Status Anda kembali idle dan dapat menerima project baru dari admin.</p>
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
