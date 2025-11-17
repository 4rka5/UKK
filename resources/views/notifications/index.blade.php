@php
    $layoutMap = [
        'admin' => 'admin',
        'team_lead' => 'lead',
        'developer' => 'member',
        'designer' => 'member'
    ];
    $layout = $layoutMap[auth()->user()->role] ?? 'app';
@endphp

@extends('layouts.' . $layout)

@section('title', 'History Notifikasi')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">📬 History Notifikasi</h2>
            <p class="text-muted mb-0">Semua notifikasi Anda</p>
        </div>
        <div>
            <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            @if($notifications->where('is_read', false)->count() > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-all"></i> Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Notifikasi</p>
                            <h3 class="mb-0">{{ $notifications->total() }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-bell-fill text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Belum Dibaca</p>
                            <h3 class="mb-0 text-warning">{{ $notifications->where('is_read', false)->count() }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-envelope-fill text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Sudah Dibaca</p>
                            <h3 class="mb-0 text-success">{{ $notifications->where('is_read', true)->count() }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-envelope-check-fill text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Hari Ini</p>
                            <h3 class="mb-0 text-info">{{ $notifications->where('created_at', '>=', now()->startOfDay())->count() }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-calendar-check-fill text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($notifications->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                    <div class="list-group-item {{ $notification->is_read ? '' : 'bg-light border-start border-primary border-4' }} py-3">
                        <div class="d-flex align-items-start gap-3">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                @php
                                    $iconMap = [
                                        'project_assigned' => ['icon' => 'bi-folder-plus', 'color' => 'primary'],
                                        'task_assigned' => ['icon' => 'bi-clipboard-check', 'color' => 'success'],
                                        'task_submitted' => ['icon' => 'bi-check-circle', 'color' => 'info'],
                                        'blocker_reported' => ['icon' => 'bi-exclamation-triangle', 'color' => 'danger'],
                                        'comment' => ['icon' => 'bi-chat-dots', 'color' => 'info'],
                                        'comment_added' => ['icon' => 'bi-chat-dots', 'color' => 'info'],
                                        'deadline_reminder' => ['icon' => 'bi-alarm', 'color' => 'warning'],
                                        'status_changed' => ['icon' => 'bi-arrow-repeat', 'color' => 'secondary'],
                                    ];
                                    $iconData = $iconMap[$notification->type] ?? ['icon' => 'bi-bell', 'color' => 'secondary'];
                                @endphp
                                <div class="bg-{{ $iconData['color'] }} bg-opacity-10 p-3 rounded-circle">
                                    <i class="bi {{ $iconData['icon'] }} text-{{ $iconData['color'] }} fs-4"></i>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 fw-bold">
                                            {{ $notification->title }}
                                            @if(!$notification->is_read)
                                                <span class="badge bg-primary ms-2">Baru</span>
                                            @endif
                                        </h6>
                                        <p class="mb-2 text-muted">{{ $notification->message }}</p>
                                        <div class="d-flex gap-3 text-muted small">
                                            <span>
                                                <i class="bi bi-clock"></i>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                            <span>
                                                <i class="bi bi-calendar3"></i>
                                                {{ $notification->created_at->format('d M Y, H:i') }}
                                            </span>
                                            @if($notification->related_type && $notification->related_id)
                                                <span class="badge bg-secondary">
                                                    {{ $notification->related_type }} #{{ $notification->related_id }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="d-flex gap-2 flex-shrink-0">
                                        @if(!$notification->is_read)
                                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Tandai sudah dibaca">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus notifikasi ini?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $notifications->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-bell-slash text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                <h4 class="mt-4 text-muted">Tidak Ada Notifikasi</h4>
                <p class="text-muted">Anda sudah menyelesaikan semua notifikasi!</p>
            </div>
        </div>
    @endif
</div>
@endsection
