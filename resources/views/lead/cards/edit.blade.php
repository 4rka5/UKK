@extends('layouts.lead')
@section('title','Edit Card')
@section('leadContent')
<div class="mb-3">
  <a href="{{ route('lead.cards.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>
<h3 class="mb-3">Edit Card</h3>
<form method="POST" action="{{ route('lead.cards.update',$card) }}" class="card p-3 shadow-sm">
  @csrf @method('PUT')
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Project</label>
      <input type="hidden" name="project_id" value="{{ old('project_id', $card->project_id) }}">
      <input type="text" class="form-control" value="{{ $card->project->project_name }}" readonly>
      <small class="form-text text-muted">Project otomatis dipilih berdasarkan assignment Anda</small>
    </div>
    <div class="col-md-6">
      <label class="form-label">Judul</label>
      <input name="card_title" class="form-control" value="{{ old('card_title',$card->card_title) }}" required>
    </div>
    <div class="col-12">
      <label class="form-label">Deskripsi</label>
      <textarea name="description" class="form-control" rows="4">{{ old('description',$card->description) }}</textarea>
    </div>
    <div class="col-md-4">
      <label class="form-label">Due Date</label>
      <input type="date" name="due_date" class="form-control" value="{{ old('due_date', optional($card->due_date)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Status</label>
      <select name="status" class="form-select" required>
        @foreach($statuses as $s)
          @php($label = match($s){
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'review' => 'Review',
            'done' => 'Done',
            default => ucfirst(str_replace('_',' ',$s))
          })
          <option value="{{ $s }}" @selected(old('status',$card->status)===$s)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Priority</label>
      <select name="priority" class="form-select" required>
        @foreach($priorities as $p)
          <option value="{{ $p }}" @selected(old('priority',$card->priority)===$p)>{{ ucfirst($p) }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Estimasi Jam</label>
      <input type="number" step="0.01" min="0" name="estimated_hours" class="form-control" value="{{ old('estimated_hours',$card->estimated_hours) }}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Aktual Jam</label>
      <input type="number" step="0.01" min="0" name="actual_hours" class="form-control" value="{{ old('actual_hours',$card->actual_hours) }}">
    </div>
    <div class="col-12">
      <label class="form-label">Assigned Users (Pilih satu atau lebih)</label>
      <select name="assigned_users[]" class="form-select" multiple size="5">
        @php($currentAssignees = old('assigned_users', $card->assignees->pluck('id')->toArray()))
        @forelse($users as $user)
          <option value="{{ $user->id }}" @selected(in_array($user->id, $currentAssignees))>
            {{ $user->fullname }} - {{ ucfirst($user->role) }}
            @if(method_exists($user, 'hasTasks'))
              (Tasks: {{ $user->getTasksCount() }})
            @endif
          </option>
        @empty
          <option disabled>Tidak ada user yang tersedia</option>
        @endforelse
      </select>
      <small class="form-text text-muted">Hold Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu user. Menampilkan user yang sudah di-assign ke card ini dan user lain yang belum punya tugas aktif.</small>
    </div>
    <div class="col-12">
      <label class="form-label">Status Assignment Default (untuk user baru yang di-assign)</label>
      <select name="assignment_status" class="form-select">
        <option value="assigned" @selected(old('assignment_status')==='assigned')>Assigned (Ditugaskan)</option>
        <option value="in_progress" @selected(old('assignment_status')==='in_progress')>In Progress (Sedang Dikerjakan)</option>
        <option value="completed" @selected(old('assignment_status')==='completed')>Completed (Selesai)</option>
      </select>
      <small class="form-text text-muted">Status ini hanya berlaku untuk user yang baru di-assign. User existing akan mempertahankan status mereka.</small>
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary">Update</button>
    <a href="{{ route('lead.cards.index') }}" class="btn btn-secondary">Batal</a>
  </div>
</form>
@endsection
