@extends('layouts.admin')
@section('title','Tambah User')
@section('adminContent')
<style>
.bg-purple {
  background-color: #6f42c1 !important;
}
.text-purple {
  color: #6f42c1 !important;
}
.form-card {
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}
.form-section {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}
.form-section:last-child {
  border-bottom: none;
}
.form-section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.role-option {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.role-option:hover {
  border-color: #3b82f6;
  background: #eff6ff;
}
.role-option input[type="radio"]:checked + label {
  border-color: #3b82f6;
  background: #eff6ff;
}
.role-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}
</style>

<div class="mb-3">
  <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>

<div class="d-flex align-items-center gap-3 mb-4">
  <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
    <i class="bi bi-person-plus-fill" style="font-size: 2rem;"></i>
  </div>
  <div>
    <h3 class="mb-1">Tambah User Baru</h3>
    <p class="text-muted mb-0 small">Buat akun pengguna baru untuk sistem</p>
  </div>
</div>

<form method="POST" action="{{ route('admin.users.store') }}" class="form-card bg-white shadow-sm">
  @csrf
  
  <!-- Informasi Pribadi -->
  <div class="form-section">
    <div class="form-section-title">
      <i class="bi bi-person-fill text-primary"></i>
      Informasi Pribadi
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">
          Nama Lengkap <span class="text-muted small">(Opsional)</span>
        </label>
        <input name="fullname" class="form-control @error('fullname') is-invalid @enderror" 
               value="{{ old('fullname') }}" placeholder="Masukkan nama lengkap">
        @error('fullname')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Nama lengkap akan ditampilkan di profil</small>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">
          Email <span class="text-danger">*</span>
        </label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
               required value="{{ old('email') }}" placeholder="user@example.com">
        @error('email')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Email harus unik dan valid</small>
      </div>
    </div>
  </div>

  <!-- Informasi Akun -->
  <div class="form-section">
    <div class="form-section-title">
      <i class="bi bi-key-fill text-success"></i>
      Informasi Akun
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">
          Username <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-at"></i></span>
          <input name="username" class="form-control @error('username') is-invalid @enderror" 
                 required value="{{ old('username') }}" placeholder="username">
          @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <small class="text-muted">Username untuk login, minimal 3 karakter</small>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">
          Password <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
          <input type="password" name="password" id="password" 
                 class="form-control @error('password') is-invalid @enderror" 
                 required placeholder="Minimal 6 karakter">
          <button class="btn btn-outline-secondary" type="button" 
                  onclick="togglePassword('password')">
            <i class="bi bi-eye-fill"></i>
          </button>
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <small class="text-muted">Password minimal 6 karakter</small>
      </div>
    </div>
  </div>

  <!-- Role & Status -->
  <div class="form-section">
    <div class="form-section-title">
      <i class="bi bi-shield-fill-check text-warning"></i>
      Role & Status
    </div>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label fw-semibold mb-3">
          Pilih Role <span class="text-danger">*</span>
        </label>
        <div class="row g-2">
          @foreach($roles as $r)
            @php
              $roleConfig = match($r) {
                'admin' => ['color' => 'danger', 'icon' => 'shield-fill-check', 'desc' => 'Akses penuh sistem'],
                'team_lead' => ['color' => 'primary', 'icon' => 'person-badge-fill', 'desc' => 'Kelola project & tim'],
                'developer' => ['color' => 'info', 'icon' => 'code-slash', 'desc' => 'Kerjakan tugas development'],
                'designer' => ['color' => 'purple', 'icon' => 'palette-fill', 'desc' => 'Kerjakan tugas design'],
                default => ['color' => 'secondary', 'icon' => 'person-fill', 'desc' => 'User biasa']
              };
            @endphp
            <div class="col-6 col-md-3">
              <input type="radio" class="btn-check" name="role" id="role_{{ $r }}" 
                     value="{{ $r }}" {{ old('role') === $r ? 'checked' : ($loop->first && !old('role') ? 'checked' : '') }}>
              <label class="role-option w-100" for="role_{{ $r }}">
                <div class="role-icon bg-{{ $roleConfig['color'] }} bg-opacity-10 text-{{ $roleConfig['color'] }}">
                  <i class="bi bi-{{ $roleConfig['icon'] }}"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $r)) }}</div>
                  <small class="text-muted">{{ $roleConfig['desc'] }}</small>
                </div>
              </label>
            </div>
          @endforeach
        </div>
        @error('role')
          <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">
          Status <span class="text-muted small">(Opsional)</span>
        </label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
          <option value="idle" {{ old('status', 'idle') === 'idle' ? 'selected' : '' }}>Idle (Tersedia)</option>
          <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active (Sedang Bekerja)</option>
          <option value="busy" {{ old('status') === 'busy' ? 'selected' : '' }}>Busy (Sibuk)</option>
          <option value="offline" {{ old('status') === 'offline' ? 'selected' : '' }}>Offline</option>
        </select>
        @error('status')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Status default user saat ini</small>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="form-section">
    <div class="d-flex gap-2 justify-content-end">
      <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-circle"></i> Batal
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-circle-fill"></i> Simpan User
      </button>
    </div>
  </div>
</form>

<script>
function togglePassword(id) {
  const input = document.getElementById(id);
  const icon = event.currentTarget.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
  } else {
    input.type = 'password';
    icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
  }
}
</script>
@endsection
