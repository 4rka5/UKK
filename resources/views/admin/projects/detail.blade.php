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
                                @if($project->status === 'pending')
                                    <span class="badge bg-warning"><i class="bi bi-clock-history"></i> Pending</span>
                                @elseif($project->status === 'approved')
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved</span>
                                @elseif($project->status === 'rejected')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rejected</span>
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

            @if($project->status === 'pending')
                <!-- Verification Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-check2-square\"></i> Verifikasi Project Selesai</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="bi bi-info-circle"></i> Team lead mengajukan project ini sebagai selesai. Verifikasi apakah project benar-benar sudah selesai atau perlu perbaikan.
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <form action="{{ route('admin.projects.markCompleted', $project) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Tandai project ini sebagai selesai? Team lead akan kembali idle.')">
                                        <i class="bi bi-check-circle"></i> Tandai Selesai
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="bi bi-arrow-counterclockwise"></i> Minta Perbaikan
                                </button>
                            </div>
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
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <p class="mb-1 small"><strong>Project Dibuat</strong></p>
                                <p class="mb-0 text-muted small">{{ $project->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($project->status === 'pending')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>Diajukan untuk Review</strong></p>
                                    <p class="mb-0 text-muted small">{{ $project->updated_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($project->status === 'approved')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>Disetujui</strong></p>
                                    <p class="mb-0 text-muted small">{{ $project->reviewed_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($project->status === 'rejected')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <p class="mb-1 small"><strong>Ditolak</strong></p>
                                    <p class="mb-0 text-muted small">{{ $project->reviewed_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Completion Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.projects.rejectCompletion', $project) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise"></i> Minta Perbaikan Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Anda akan meminta team lead untuk memperbaiki project: <strong>{{ $project->project_name }}</strong>
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Feedback / Alasan Perbaikan <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  id="rejection_reason" 
                                  name="rejection_reason" 
                                  rows="4" 
                                  placeholder="Jelaskan apa yang perlu diperbaiki..."
                                  required></textarea>
                        <small class="text-muted">Team lead akan melihat feedback ini dan melanjutkan project.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise"></i> Kirim Feedback
                    </button>
                </div>
            </form>
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
