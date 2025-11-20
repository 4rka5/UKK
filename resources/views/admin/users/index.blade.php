@extends('layouts.admin')
@section('title','Kelola User')
@section('adminContent')
<style>
.bg-purple {
  background-color: #6f42c1 !important;
}
.stat-card {
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid #e5e7eb;
  transition: all 0.2s ease;
}
.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}
.user-card {
  transition: all 0.2s ease;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
}
.user-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.25rem;
  flex-shrink: 0;
}
.user-info {
  min-width: 0;
}
.user-name {
  font-weight: 600;
  font-size: 1rem;
  color: #1f2937;
  margin-bottom: 0.25rem;
}
.user-email {
  font-size: 0.875rem;
  color: #6b7280;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
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
}
.search-filter-bar {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
}
@media (max-width: 768px) {
  .stat-card {
    margin-bottom: 0.75rem;
  }
}
@media (max-width: 576px) {
  .user-card .row > div {
    width: 100%;
    margin-bottom: 0.5rem;
  }
  .action-btns {
    width: 100%;
  }
  .action-btns .btn {
    flex: 1;
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
    <h3 class="mb-1">👥 Kelola User</h3>
    <p class="text-muted mb-0 small">Manajemen pengguna sistem</p>
  </div>
  <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Tambah User
  </a>
</div>

@php
  $totalUsers = $users->total();
  $adminCount = \App\Models\User::where('role', 'admin')->count();
  $leadCount = \App\Models\User::where('role', 'team_lead')->count();
  $devCount = \App\Models\User::where('role', 'developer')->count();
  $designerCount = \App\Models\User::where('role', 'designer')->count();
@endphp

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card bg-white">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
          <i class="bi bi-people-fill"></i>
        </div>
        <div>
          <div class="text-muted small">Total User</div>
          <div class="h4 mb-0 fw-bold">{{ $totalUsers }}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card bg-white">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
          <i class="bi bi-shield-fill-check"></i>
        </div>
        <div>
          <div class="text-muted small">Admin</div>
          <div class="h4 mb-0 fw-bold">{{ $adminCount }}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card bg-white">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-info bg-opacity-10 text-info">
          <i class="bi bi-code-slash"></i>
        </div>
        <div>
          <div class="text-muted small">Developer</div>
          <div class="h4 mb-0 fw-bold">{{ $devCount }}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card bg-white">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-purple bg-opacity-10 text-purple">
          <i class="bi bi-palette-fill"></i>
        </div>
        <div>
          <div class="text-muted small">Designer</div>
          <div class="h4 mb-0 fw-bold">{{ $designerCount }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Search & Filter Bar -->
<div class="search-filter-bar">
  <form method="GET" action="{{ route('admin.users.index') }}">
    <div class="row g-3 align-items-end">
      <div class="col-12 col-md-5">
        <label class="form-label small fw-semibold mb-1">
          <i class="bi bi-search"></i> Cari User
        </label>
        <input type="text" name="search" class="form-control" 
               placeholder="Cari nama, username, atau email..." 
               value="{{ request('search') }}">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold mb-1">
          <i class="bi bi-funnel"></i> Filter Role
        </label>
        <select name="role" class="form-select">
          <option value="">Semua Role</option>
          <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
          <option value="team_lead" {{ request('role') === 'team_lead' ? 'selected' : '' }}>Team Lead</option>
          <option value="developer" {{ request('role') === 'developer' ? 'selected' : '' }}>Developer</option>
          <option value="designer" {{ request('role') === 'designer' ? 'selected' : '' }}>Designer</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small fw-semibold mb-1">
          <i class="bi bi-activity"></i> Status
        </label>
        <select name="has_tasks" class="form-select">
          <option value="">Semua Status</option>
          <option value="1" {{ request('has_tasks') === '1' ? 'selected' : '' }}>Ada Tugas</option>
          <option value="0" {{ request('has_tasks') === '0' ? 'selected' : '' }}>Tersedia</option>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">
            <i class="bi bi-search"></i> <span class="d-none d-md-inline">Cari</span>
          </button>
          <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
            <i class="bi bi-arrow-clockwise"></i>
          </a>
        </div>
      </div>
    </div>
  </form>
</div>

<!-- User List -->
<div class="row g-3">
  @forelse($users as $u)
    <div class="col-12">
      <div class="user-card bg-white p-3">
        <div class="row align-items-center g-3">
          <!-- Avatar & Info -->
          <div class="col-12 col-md-4">
            <div class="d-flex align-items-center gap-3">
              @php
                $avatarBg = match($u->role) {
                  'admin' => 'bg-danger',
                  'team_lead' => 'bg-primary',
                  'developer' => 'bg-info',
                  'designer' => 'bg-purple',
                  default => 'bg-secondary'
                };
                $initials = strtoupper(substr($u->fullname ?: $u->username, 0, 2));
              @endphp
              <div class="user-avatar {{ $avatarBg }} text-white">
                {{ $initials }}
              </div>
              <div class="user-info flex-grow-1">
                <div class="user-name">{{ $u->fullname ?: $u->username }}</div>
                <div class="user-email">
                  <i class="bi bi-envelope me-1"></i>{{ $u->email }}
                </div>
                <div class="d-md-none mt-1">
                  <small class="text-muted">@{{ $u->username }}</small>
                </div>
              </div>
            </div>
          </div>

          <!-- Username (Desktop only) -->
          <div class="col-md-2 d-none d-md-block">
            <small class="text-muted d-block mb-1">Username</small>
            <span class="fw-semibold">{{ $u->username }}</span>
          </div>

          <!-- Role -->
          <div class="col-6 col-md-2">
            <small class="text-muted d-block mb-1">Role</small>
            @php
              $roleBadge = match($u->role) {
                'admin' => 'bg-danger',
                'team_lead' => 'bg-primary',
                'developer' => 'bg-info',
                'designer' => 'bg-purple',
                default => 'bg-secondary'
              };
              $roleIcon = match($u->role) {
                'admin' => 'shield-fill-check',
                'team_lead' => 'person-badge-fill',
                'developer' => 'code-slash',
                'designer' => 'palette-fill',
                default => 'person-fill'
              };
            @endphp
            <span class="badge {{ $roleBadge }} text-white">
              <i class="bi bi-{{ $roleIcon }} me-1"></i>
              {{ ucfirst(str_replace('_', ' ', $u->role)) }}
            </span>
          </div>

          <!-- Status -->
          <div class="col-6 col-md-2">
            <small class="text-muted d-block mb-1">Status</small>
            @if($u->isAdmin())
              <span class="badge bg-secondary">
                <i class="bi bi-gear-fill me-1"></i>{{ ucfirst($u->status) }}
              </span>
            @elseif($u->hasTasks())
              @php
                $activeProjectCount = $u->getTasksCount();
              @endphp
              <span class="badge bg-warning text-dark">
                <i class="bi bi-briefcase-fill me-1"></i>{{ $activeProjectCount }} Project Aktif
              </span>
            @else
              <span class="badge bg-success">
                <i class="bi bi-check-circle-fill me-1"></i>Tersedia
              </span>
            @endif
          </div>

          <!-- Actions -->
          <div class="col-12 col-md-2">
            <div class="action-btns {{ $loop->first && $users->count() > 1 ? 'justify-content-md-end' : 'justify-content-end' }}">
              <a href="{{ route('admin.users.edit',$u) }}" class="btn btn-sm btn-outline-primary" title="Edit User">
                <i class="bi bi-pencil-fill"></i>
                <span class="d-md-none d-lg-inline ms-1">Edit</span>
              </a>
              @if(!$u->isAdmin() || \App\Models\User::where('role', 'admin')->count() > 1)
              <form action="{{ route('admin.users.destroy',$u) }}" method="POST" class="d-inline" 
                    onsubmit="return confirm('⚠️ Hapus user {{ $u->fullname ?: $u->username }}?\n\nData yang akan dihapus:\n- Semua tugas yang ditugaskan\n- Riwayat notifikasi\n- Data terkait lainnya\n\nApakah Anda yakin?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" title="Hapus User" 
                        {{ $u->isAdmin() && \App\Models\User::where('role', 'admin')->count() <= 1 ? 'disabled' : '' }}>
                  <i class="bi bi-trash-fill"></i>
                  <span class="d-md-none d-lg-inline ms-1">Hapus</span>
                </button>
              </form>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12">
      <div class="text-center py-5 bg-white rounded-3 border">
        <i class="bi bi-people" style="font-size: 4rem; color: #e5e7eb;"></i>
        <p class="text-muted mt-3 mb-2 fw-semibold">Tidak ada user ditemukan</p>
        @if(request()->hasAny(['search', 'role', 'has_tasks']))
          <p class="text-muted small mb-3">Coba ubah filter pencarian Anda</p>
          <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-clockwise"></i> Reset Filter
          </a>
        @else
          <p class="text-muted small mb-3">Belum ada user yang terdaftar</p>
          <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah User Pertama
          </a>
        @endif
      </div>
    </div>
  @endforelse
</div>

@if($users->hasPages())
  <div class="d-flex justify-content-center mt-4">
    {{ $users->links('pagination::bootstrap-5') }}
  </div>
@endif

@endsection
