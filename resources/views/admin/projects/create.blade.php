@extends('layouts.admin')
@section('title','Tambah Project')
@section('adminContent')

<div class="mb-3">
  <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>

<style>
.member-row { background: #f8f9fa; border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; border: 1px solid #dee2e6; }
.member-row:hover { background: #e9ecef; }
.role-badge { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; }
.team-lead-badge { background: #0d6efd; color: white; }
.developer-badge { background: #198754; color: white; }
.designer-badge { background: #dc3545; color: white; }
</style>

<h3 class="mb-3">Tambah Project</h3>
<form method="POST" action="{{ route('admin.projects.store') }}" class="card p-4 shadow-sm">
  @csrf
  
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <label class="form-label fw-semibold">Nama Project <span class="text-danger">*</span></label>
      <input name="project_name" class="form-control" required value="{{ old('project_name') }}" placeholder="Website Sekolah">
    </div>
    <div class="col-md-3">
      <label class="form-label fw-semibold">Deadline</label>
      <input type="date" name="deadline" class="form-control" value="{{ old('deadline') }}">
      <small class="text-muted">Wajib di Isi</small>
    </div>
    <div class="col-md-3">
      <label class="form-label fw-semibold">Owner (Team Lead) <span class="text-danger">*</span></label>
      <select name="created_by" class="form-select" required>
        <option value="">-- Pilih Team Lead --</option>
        @forelse($owners as $o)
          <option value="{{ $o->id }}" @selected(old('created_by')==$o->id)>
            {{ $o->fullname ?: $o->username }}
            @if(method_exists($o, 'getTasksCount'))
              (Projects: {{ $o->getTasksCount() }})
            @endif
          </option>
        @empty
          <option disabled>Semua Team Lead sudah memiliki project</option>
        @endforelse
      </select>
      <small class="text-muted">Hanya Team Lead yang belum punya project</small>
    </div>
    <div class="col-12">
      <label class="form-label fw-semibold">Deskripsi</label>
      <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi project...">{{ old('description') }}</textarea>
    </div>
  </div>

  <hr class="my-4">

  <!-- Member Selection -->
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h5 class="mb-1">👥 Anggota Project</h5>
        <small class="text-muted">Tambahkan Developer dan Designer (Team Lead otomatis dari Owner)</small>
      </div>
      <button type="button" class="btn btn-sm btn-primary" onclick="addMember()">
        <i class="bi bi-plus-circle"></i> Tambah Anggota
      </button>
    </div>

    <div id="members-container">
      <!-- Member rows will be added here -->
      @if(old('members'))
        @foreach(old('members') as $index => $member)
          <div class="member-row" data-index="{{ $index }}">
            <div class="row align-items-center">
              <div class="col-md-6">
                <label class="form-label small mb-1">User</label>
                <select name="members[{{ $index }}][user_id]" class="form-select form-select-sm member-select" required onchange="checkDuplicate()">
                  <option value="">-- Pilih User --</option>
                  @forelse($users as $user)
                    <option value="{{ $user->id }}" @selected($member['user_id'] == $user->id)>
                      {{ $user->fullname ?: $user->username }} ({{ ucfirst($user->role) }})
                    </option>
                  @empty
                    <option disabled>Semua user sudah ditugaskan ke project lain</option>
                  @endforelse
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small mb-1">Role di Project</label>
                <select name="members[{{ $index }}][role]" class="form-select form-select-sm" required>
                  <option value="developer" @selected($member['role'] == 'developer')>Developer</option>
                  <option value="designer" @selected($member['role'] == 'designer')>Designer</option>
                </select>
              </div>
              <div class="col-md-2 text-end">
                <label class="form-label small mb-1 d-block">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeMember(this)">
                  <i class="bi bi-x-circle"></i> Hapus
                </button>
              </div>
            </div>
          </div>
        @endforeach
      @endif
    </div>

    <div class="alert alert-info mt-3 mb-0">
      <small>
        <strong>ℹ️ Catatan:</strong>
        <ul class="mb-0 mt-1">
          <li><strong>Team Lead</strong> otomatis ditambahkan dari Owner</li>
          <li>Tambahkan Developer dan Designer sesuai kebutuhan project</li>
          <li>User yang sama tidak bisa ditambahkan 2 kali</li>
        </ul>
      </small>
    </div>
  </div>

  <div class="mt-4">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save"></i> Simpan Project
    </button>
    <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
      <i class="bi bi-x-circle"></i> Batal
    </a>
  </div>
</form>

<script>
let memberIndex = {{ old('members') ? count(old('members')) : 0 }};

function addMember() {
  const container = document.getElementById('members-container');
  const memberRow = document.createElement('div');
  memberRow.className = 'member-row';
  memberRow.setAttribute('data-index', memberIndex);
  
  memberRow.innerHTML = `
    <div class="row align-items-center">
      <div class="col-md-6">
        <label class="form-label small mb-1">User</label>
        <select name="members[${memberIndex}][user_id]" class="form-select form-select-sm member-select" required onchange="checkDuplicate()">
          <option value="">-- Pilih User --</option>
          @forelse($users as $user)
            <option value="{{ $user->id }}">
              {{ $user->fullname ?: $user->username }} ({{ ucfirst($user->role) }})
            </option>
          @empty
            <option disabled>Semua user sudah ditugaskan ke project lain</option>
          @endforelse
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small mb-1">Role di Project</label>
        <select name="members[${memberIndex}][role]" class="form-select form-select-sm" required>
          <option value="developer">Developer</option>
          <option value="designer">Designer</option>
        </select>
      </div>
      <div class="col-md-2 text-end">
        <label class="form-label small mb-1 d-block">&nbsp;</label>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeMember(this)">
          <i class="bi bi-x-circle"></i> Hapus
        </button>
      </div>
    </div>
  `;
  
  container.appendChild(memberRow);
  memberIndex++;
}

function removeMember(btn) {
  btn.closest('.member-row').remove();
  checkDuplicate();
}

function checkDuplicate() {
  const selects = document.querySelectorAll('.member-select');
  const values = [];
  const ownerSelect = document.querySelector('select[name="created_by"]');
  const ownerValue = ownerSelect ? ownerSelect.value : '';
  
  selects.forEach(select => {
    if (select.value) {
      // Cek duplikat dengan owner
      if (select.value === ownerValue) {
        select.setCustomValidity('Owner sudah otomatis menjadi Team Lead!');
        select.style.borderColor = 'red';
      }
      // Cek duplikat dengan member lain
      else if (values.includes(select.value)) {
        select.setCustomValidity('User sudah dipilih!');
        select.style.borderColor = 'red';
      } else {
        select.setCustomValidity('');
        select.style.borderColor = '';
        values.push(select.value);
      }
    }
  });
}
</script>

@endsection
