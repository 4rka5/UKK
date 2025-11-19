@extends('layouts.lead')

@section('title', 'Kelola Project')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-send"></i> Ajukan Project</h1>
            <p class="text-muted mb-0">Ajukan project baru untuk mendapatkan persetujuan admin</p>
        </div>
        <a href="{{ route('lead.projects.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Ajukan Project Baru
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

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                                <i class="bi bi-folder2 fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Total Pengajuan</h6>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Pending</h6>
                            <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Approved</h6>
                            <h4 class="mb-0">{{ $stats['approved'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                                <i class="bi bi-x-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Rejected</h6>
                            <h4 class="mb-0">{{ $stats['rejected'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Project</h5>
        </div>
        <div class="card-body p-0">
            @if($projects->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Project</th>
                                <th>Deadline</th>
                                <th class="text-center">Status</th>
                                <th>Direview Oleh</th>
                                <th>Tanggal Review</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                                <tr>
                                    <td>
                                        <strong>{{ $project->project_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($project->description, 60) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $project->deadline < now() ? 'danger' : 'info' }}">
                                            <i class="bi bi-calendar"></i> {{ $project->deadline->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($project->status === 'pending')
                                            <span class="badge bg-warning"><i class="bi bi-clock-history"></i> Pending</span>
                                        @elseif($project->status === 'approved')
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Approved</span>
                                        @elseif($project->status === 'rejected')
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->reviewer)
                                            <i class="bi bi-person-check"></i> {{ $project->reviewer->fullname ?? $project->reviewer->username }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->reviewed_at)
                                            <small>{{ $project->reviewed_at->format('d M Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('lead.projects.show', $project) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @if($project->status === 'rejected' && $project->rejection_reason)
                                    <tr class="table-warning">
                                        <td colspan="6">
                                            <small><strong><i class="bi bi-exclamation-triangle"></i> Alasan Penolakan:</strong> {{ $project->rejection_reason }}</small>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">Belum ada pengajuan project. Ajukan project baru untuk memulai.</p>
                    <a href="{{ route('lead.projects.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Ajukan Project Baru
                    </a>
                </div>
            @endif
        </div>
        @if($projects->hasPages())
            <div class="card-footer bg-white">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
