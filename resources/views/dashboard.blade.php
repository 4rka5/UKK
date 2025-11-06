{{-- resources/views/projects/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Dashboard Proyek</h1>
        </div>
    </div>

    <!-- Debug info -->
    <div class="row mb-3">
        <div class="col-12">
            @if(isset($projects) && $projects->count() > 0)
                <div class="alert alert-success">
                    Total Projects Found: {{ $projects->count() }}
                </div>
            @else
                <div class="alert alert-warning">
                    No projects found or variable $projects is not set.
                </div>
            @endif
        </div>
    </div>

    @if(isset($projects) && $projects->count() > 0)
    <div class="row">
        <!-- Project Statistics -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Proyek
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $projects->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-folder fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Board
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $projects->sum('boards_count') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-columns fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Projects -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Proyek Saya</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Proyek</th>
                                    <th>Jumlah Board</th>
                                    <th>Anggota</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $project)
                                <tr>
                                    <td>{{ $project->project_name }}</td>
                                    <td>{{ $project->boards_count ?? 0 }}</td>
                                    <td>{{ $project->members_count ?? 0 }}</td>
                                    <td>{{ $project->created_at->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('projects.show', $project->project_id_0) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        @if(isset($recentCards) && $recentCards->count() > 0)
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aktivitas Terbaru</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($recentCards as $card)
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img class="rounded-circle"
                                         src="https://ui-avatars.com/api/?name={{ urlencode($card->createdBy->full_name ?? 'User') }}&background=random"
                                         width="40" height="40">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">{{ $card->card_title }}</h6>
                                    <p class="mb-0 text-muted small">
                                        {{ $card->createdBy->full_name ?? 'User' }} •
                                        {{ $card->created_at->diffForHumans() }}
                                    </p>
                                    <span class="badge bg-light text-dark">
                                        {{ $card->board->project->project_name ?? 'Unknown Project' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Belum ada proyek</h4>
                    <p class="text-muted">Anda belum terdaftar di proyek manapun</p>
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Proyek Pertama
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
