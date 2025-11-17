@extends('layouts.lead')
@section('title','Tambah Card')
@section('leadContent')
<div class="mb-3">
  <a href="{{ route('lead.cards.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>
<h3 class="mb-3">Tambah Card</h3>
<form method="POST" action="{{ route('lead.cards.store') }}" class="card p-3 shadow-sm">
  @csrf
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Project</label>
      <input type="hidden" name="project_id" id="project_id" value="{{ old('project_id', $defaultProjectId) }}">
      <input type="text" class="form-control" value="{{ $projects->first()->project_name ?? '' }}" readonly>
      <small class="form-text text-muted">Project otomatis dipilih berdasarkan assignment Anda</small>
    </div>
    <div class="col-md-6">
      <label class="form-label">Judul</label>
      <input name="card_title" class="form-control" value="{{ old('card_title') }}" required>
    </div>
    <div class="col-12">
      <label class="form-label">Deskripsi</label>
      <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
    </div>
    <div class="col-md-4">
      <label class="form-label">Due Date</label>
      <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        @foreach($statuses as $s)
          @php($label = match($s){
            'todo' => 'To Do (Default)',
            'in_progress' => 'In Progress',
            'review' => 'Review',
            'done' => 'Done',
            default => ucfirst(str_replace('_',' ',$s))
          })
          <option value="{{ $s }}" @selected(old('status', 'todo')===$s)>{{ $label }}</option>
        @endforeach
      </select>
      <small class="form-text text-muted">Default: To Do</small>
    </div>
    <div class="col-md-4">
      <label class="form-label">Priority</label>
      <select name="priority" class="form-select" required>
        @foreach($priorities as $p)
          <option value="{{ $p }}" @selected(old('priority')===$p)>{{ ucfirst($p) }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Estimasi Jam</label>
      <input type="number" step="0.01" min="0" name="estimated_hours" class="form-control" value="{{ old('estimated_hours') }}">
    </div>
    <div class="col-12">
      <label class="form-label">Assign ke User (Pilih satu atau lebih)</label>
      <select name="assigned_users[]" id="assigned_users" class="form-select" multiple size="5" disabled>
        <option disabled>Pilih Project terlebih dahulu</option>
      </select>
      <small class="form-text text-muted">Hanya menampilkan member project dan belum memiliki tugas aktif.</small>
    </div>
  </div>
  <input type="hidden" name="assignment_status" value="assigned">
  <div class="mt-3">
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('lead.cards.index') }}" class="btn btn-secondary">Batal</a>
  </div>
</form>

<script>
// Data projects dengan project members
const projectsData = [
    @foreach($projects as $p)
    {
        id: {{ $p->id }},
        project_id: {{ $p->id }},
        members: [
            @foreach($p->members as $m)
            {
                user_id: {{ $m->user->id }},
                fullname: "{{ $m->user->fullname ?? $m->user->username }}",
                role: "{{ $m->user->role }}",
                has_tasks: {{ $m->user->hasTasks() ? 'true' : 'false' }},
                task_count: {{ $m->user->getTasksCount() }}
            },
            @endforeach
        ]
    },
    @endforeach
];

console.log('Projects Data:', projectsData);

// Filter users berdasarkan project yang dipilih
document.getElementById('project_id').addEventListener('change', function() {
    const projectId = parseInt(this.value);
    const userSelect = document.getElementById('assigned_users');
    
    console.log('Project ID selected:', projectId);
    
    if (!projectId) {
        userSelect.disabled = true;
        userSelect.innerHTML = '<option disabled>Pilih project terlebih dahulu</option>';
        return;
    }
    
    // Find project data
    const projectData = projectsData.find(b => b.id === projectId);
    console.log('Project data found:', projectData);
    
    if (!projectData || !projectData.members || projectData.members.length === 0) {
        userSelect.disabled = true;
        userSelect.innerHTML = '<option disabled>Tidak ada member di project ini</option>';
        return;
    }
    
    // Filter members yang belum punya tugas aktif
    const availableMembers = projectData.members.filter(m => !m.has_tasks);
    console.log('Available members:', availableMembers);
    
    if (availableMembers.length === 0) {
        userSelect.disabled = true;
        userSelect.innerHTML = '<option disabled>Semua member sudah memiliki tugas aktif</option>';
        return;
    }
    
    // Populate options
    userSelect.disabled = false;
    userSelect.innerHTML = '';
    
    availableMembers.forEach(member => {
        const option = document.createElement('option');
        option.value = member.user_id;
        option.textContent = `${member.fullname} - ${member.role.toUpperCase()} (Tasks: ${member.task_count})`;
        userSelect.appendChild(option);
    });
});

// Trigger on page load if project already selected (for old input)
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_id');
    if (projectSelect.value) {
        projectSelect.dispatchEvent(new Event('change'));
    }
});
</script>

@endsection
