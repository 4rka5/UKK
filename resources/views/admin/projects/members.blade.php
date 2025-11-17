@extends('layouts.admin')
@section('title', 'Kelola Anggota Project')
@section('adminContent')

<style>
.member-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    border-left: 4px solid;
    transition: all 0.2s;
}
.member-card:hover {
    background: #e9ecef;
    transform: translateX(2px);
}
.member-card.team-lead { border-left-color: #0d6efd; }
.member-card.developer { border-left-color: #198754; }
.member-card.designer { border-left-color: #dc3545; }
.role-badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
}
</style>

<div class="mb-3">
    <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Project
    </a>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-1">👥 Anggota Project</h3>
        <p class="text-muted mb-0">{{ $project->project_name }}</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
        <i class="bi bi-person-plus"></i> Tambah Anggota
    </button>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Daftar Anggota ({{ $members->count() }})</h5>
            </div>
            <div class="card-body">
                @forelse($members as $member)
                    <div class="member-card {{ $member->role }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <strong>{{ $member->user->fullname ?: $member->user->username }}</strong>
                                    <span class="role-badge bg-{{ $member->role === 'team_lead' ? 'primary' : ($member->role === 'developer' ? 'success' : 'danger') }} text-white">
                                        {{ $member->role === 'team_lead' ? 'Team Lead' : ucfirst($member->role) }}
                                    </span>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-envelope"></i> {{ $member->user->email }}
                                    @if($member->joined_at)
                                        | <i class="bi bi-calendar"></i> Bergabung: {{ \Carbon\Carbon::parse($member->joined_at)->format('d M Y') }}
                                    @endif
                                </small>
                            </div>
                            <div>
                                <form action="{{ route('admin.projects.removeMember', [$project, $member->id]) }}" method="POST" onsubmit="return confirm('Hapus {{ $member->user->fullname ?: $member->user->username }} dari project?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-circle"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted p-4">
                        <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mb-0 mt-2">Belum ada anggota di project ini</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Statistik</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>Total Anggota</span>
                        <strong class="badge bg-primary">{{ $members->count() }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>Team Lead</span>
                        <strong class="badge bg-primary">{{ $members->where('role', 'team_lead')->count() }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>Developer</span>
                        <strong class="badge bg-success">{{ $members->where('role', 'developer')->count() }}</strong>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>Designer</span>
                        <strong class="badge bg-danger">{{ $members->where('role', 'designer')->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Info Project</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Nama Project</small>
                    <p class="mb-0 fw-semibold">{{ $project->project_name }}</p>
                </div>
                @if($project->deadline)
                    <div class="mb-2">
                        <small class="text-muted">Deadline</small>
                        <p class="mb-0">{{ \Carbon\Carbon::parse($project->deadline)->format('d M Y') }}</p>
                    </div>
                @endif
                <div>
                    <small class="text-muted">Owner</small>
                    <p class="mb-0">{{ $project->owner->fullname ?? $project->owner->username }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.projects.addMember', $project) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Anggota Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih User <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih User --</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->fullname ?: $user->username }} ({{ ucfirst($user->role) }})
                                </option>
                            @endforeach
                        </select>
                        @if($availableUsers->count() === 0)
                            <small class="text-muted">Semua user sudah menjadi anggota project ini</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role di Project <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="team_lead">Team Lead</option>
                            <option value="developer">Developer</option>
                            <option value="designer">Designer</option>
                        </select>
                    </div>
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            User akan ditambahkan sebagai anggota project dengan role yang dipilih.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" @if($availableUsers->count() === 0) disabled @endif>
                        <i class="bi bi-plus-circle"></i> Tambah Anggota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
