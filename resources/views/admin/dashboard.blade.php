@extends('layouts.admin')
@section('title','Dashboard')
@section('adminContent')

<style>
.stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px; padding: 2rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.stats-card h1 { font-size: 3rem; font-weight: 700; margin: 0; }
.stats-card .label { opacity: 0.9; font-size: 1rem; margin-top: 0.5rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
.stat-box { background: white; border-radius: 12px; padding: 1.75rem; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 5px solid; transition: all 0.3s; }
.stat-box:hover { transform: translateY(-5px); box-shadow: 0 6px 16px rgba(0,0,0,0.15); }
.stat-box .icon { font-size: 2.5rem; margin-bottom: 1rem; }
.stat-box .number { font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem; }
.stat-box .label { font-size: 0.9rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-users { border-left-color: #667eea; }
.stat-users .icon { color: #667eea; }
.stat-users .number { color: #667eea; }
.stat-projects { border-left-color: #06b6d4; }
.stat-projects .icon { color: #06b6d4; }
.stat-projects .number { color: #06b6d4; }
.stat-leads { border-left-color: #f59e0b; }
.stat-leads .icon { color: #f59e0b; }
.stat-leads .number { color: #f59e0b; }
.stat-members { border-left-color: #10b981; }
.stat-members .icon { color: #10b981; }
.stat-members .number { color: #10b981; }
.data-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
.data-card-header { background: linear-gradient(to right, #f8f9fa, #ffffff); padding: 1.25rem 1.5rem; border-bottom: 2px solid #e9ecef; display: flex; justify-content: between; align-items: center; }
.data-card-header h5 { margin: 0; font-weight: 600; color: #1f2937; }
.table-modern { margin: 0; }
.table-modern thead { background: #f8f9fa; }
.table-modern thead th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; color: #6c757d; padding: 1rem 1.25rem; border: none; }
.table-modern tbody td { padding: 1rem 1.25rem; vertical-align: middle; border-color: #f0f0f0; }
.table-modern tbody tr:hover { background-color: #f8f9fa; }
.badge-role { padding: 0.4rem 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: 6px; }
.quick-action { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: #667eea; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.2s; }
.quick-action:hover { background: #5568d3; color: white; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(102,126,234,0.3); }
</style>

<!-- Welcome Header -->
<div class="stats-card">
  <div class="row align-items-center">
    <div class="col-md-8">
      <h1>👋 Selamat Datang, Admin!</h1>
      <div class="label">{{ now()->translatedFormat('l, d F Y') }}</div>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
      <div class="d-flex gap-2 justify-content-end">
        <a href="{{ route('admin.reports.index') }}" class="quick-action" style="background: #10b981;">
          <span>📊</span> Laporan
        </a>
        <a href="{{ route('admin.users.create') }}" class="quick-action">
          <span>➕</span> Tambah User
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Statistics Grid -->
<div class="stats-grid">
  <div class="stat-box stat-users">
    <div class="icon">👥</div>
    <div class="number">{{ $userCount }}</div>
    <div class="label">Total Users</div>
  </div>

  <div class="stat-box stat-projects">
    <div class="icon">📁</div>
    <div class="number">{{ $projectCount }}</div>
    <div class="label">Total Projects</div>
  </div>

  <div class="stat-box stat-leads">
    <div class="icon">👨‍💼</div>
    <div class="number">{{ $users->where('role', 'team_lead')->count() }}</div>
    <div class="label">Team Leads</div>
  </div>

  <div class="stat-box stat-members">
    <div class="icon">🎨</div>
    <div class="number">{{ $users->whereIn('role', ['designer', 'developer'])->count() }}</div>
    <div class="label">Members</div>
  </div>
</div>

<!-- Recent Projects -->
<div class="data-card mb-4">
  <div class="data-card-header">
    <h5>📋 Project Terbaru</h5>
    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-sm">+ Project Baru</a>
  </div>
  
  <!-- Desktop Table View -->
  <div class="table-responsive d-none d-md-block">
    <table class="table table-modern">
      <thead>
        <tr>
          <th>Nama Project</th>
          <th>Owner</th>
          <th>Deadline</th>
          <th>Deskripsi</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($projects as $p)
          <tr>
            <td><strong>{{ $p->project_name }}</strong></td>
            <td>
              @if($p->owner)
                <div class="d-flex align-items-center gap-2">
                  <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                    {{ strtoupper(substr($p->owner->fullname ?? $p->owner->username, 0, 1)) }}
                  </div>
                  <div>
                    <div class="small fw-semibold">{{ $p->owner->fullname ?? $p->owner->username }}</div>
                    <div class="text-muted" style="font-size: 0.7rem;">{{ ucfirst($p->owner->role) }}</div>
                  </div>
                </div>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              @if($p->deadline)
                {{ \Carbon\Carbon::parse($p->deadline)->format('d M Y') }}
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              <small class="text-muted">{{ Str::limit($p->description ?? '-', 50) }}</small>
            </td>
            <td class="text-end">
              <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.projects.edit', $p) }}" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-pencil-fill"></i>
                  <span class="d-none d-lg-inline ms-1">Edit</span>
                </a>
                <form action="{{ route('admin.projects.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus project ini?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash-fill"></i>
                    <span class="d-none d-lg-inline ms-1">Hapus</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted p-5">
              <div style="font-size: 3rem; opacity: 0.3;">📭</div>
              <p class="mb-0">Belum ada project</p>
              <small>Klik tombol "+ Project Baru" untuk membuat project</small>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  <!-- Mobile Card View -->
  <div class="d-md-none p-3">
    @forelse($projects as $p)
      <div class="card mb-3 border">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="mb-0 fw-bold">{{ $p->project_name }}</h6>
            @if($p->deadline)
              @php
                $deadline = \Carbon\Carbon::parse($p->deadline);
                $isOverdue = $deadline->isPast();
              @endphp
              <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-success' }} small">
                <i class="bi bi-calendar3"></i> {{ $deadline->format('d M Y') }}
              </span>
            @endif
          </div>
          
          @if($p->owner)
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.9rem;">
                {{ strtoupper(substr($p->owner->fullname ?? $p->owner->username, 0, 1)) }}
              </div>
              <div>
                <div class="small fw-semibold">{{ $p->owner->fullname ?? $p->owner->username }}</div>
                <div class="text-muted" style="font-size: 0.75rem;">
                  <i class="bi bi-person-badge"></i> {{ ucfirst($p->owner->role) }}
                </div>
              </div>
            </div>
          @endif
          
          @if($p->description)
            <p class="small text-muted mb-3">{{ Str::limit($p->description, 80) }}</p>
          @endif
          
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.projects.edit', $p) }}" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-pencil-fill"></i> Edit
            </a>
            <form action="{{ route('admin.projects.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus project ini?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash-fill"></i> Hapus
              </button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center text-muted py-5">
        <div style="font-size: 3rem; opacity: 0.3;">📭</div>
        <p class="mb-0">Belum ada project</p>
        <small>Klik tombol "+ Project Baru" untuk membuat project</small>
      </div>
    @endforelse
  </div>
</div>

<!-- Recent Users -->
<div class="data-card">
  <div class="data-card-header">
    <h5>👥 User Terbaru</h5>
    <a href="{{ route('admin.users.create') }}" class="btn btn-success btn-sm">+ User Baru</a>
  </div>
  <div class="table-responsive">
    <table class="table table-modern">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users->take(10) as $u)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1rem; flex-shrink: 0;">
                  {{ strtoupper(substr($u->fullname ?? $u->username, 0, 1)) }}
                </div>
                <strong>{{ $u->fullname ?? '-' }}</strong>
              </div>
            </td>
            <td class="py-3"><code class="bg-light px-2 py-1 rounded">{{ $u->username }}</code></td>
            <td class="py-3"><small class="text-muted">{{ $u->email }}</small></td>
            <td class="py-3">
              <span class="badge-role
                @if($u->role === 'admin') bg-danger text-white
                @elseif($u->role === 'team_lead') bg-warning text-dark
                @elseif($u->role === 'designer') bg-info text-white
                @else bg-success text-white
                @endif">
                {{ ucfirst(str_replace('_', ' ', $u->role)) }}
              </span>
            </td>
            <td class="py-3">
              <span class="badge {{ $u->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                {{ ucfirst($u->status ?? 'idle') }}
              </span>
            </td>
            <td class="text-end py-3">
              <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-pencil-fill"></i>
                  <span class="d-none d-lg-inline ms-1">Edit</span>
                </a>
                @if($u->id !== auth()->id())
                  <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash-fill"></i>
                      <span class="d-none d-lg-inline ms-1">Hapus</span>
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted p-5">
              <div style="font-size: 3rem; opacity: 0.3;">👤</div>
              <p class="mb-0">Belum ada user</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($users->count() > 10)
    <div class="card-footer text-center bg-white">
      <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
        Lihat Semua Users ({{ $users->count() }}) →
      </a>
    </div>
  @endif
</div>
@endsection
