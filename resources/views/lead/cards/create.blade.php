@extends('layouts.lead')
@section('title','Tambah Card')
@section('leadContent')
<h3 class="mb-3">Tambah Card</h3>
<form method="POST" action="{{ route('lead.cards.store') }}" class="card p-3 shadow-sm">
  @csrf
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Board</label>
      <select name="board_id" class="form-select" required>
        @foreach($boards as $b)
          <option value="{{ $b->id }}" @selected(old('board_id')==$b->id)>
            {{ $b->board_name }} @if($b->project) — {{ $b->project->project_name }} @endif
          </option>
        @endforeach
      </select>
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
      <select name="status" class="form-select" required>
        @foreach($statuses as $s)
          @php($label = match($s){
            'backlog' => 'Backlog',
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'code_review' => 'Code Review',
            'testing' => 'Testing',
            'done' => 'Done',
            default => ucfirst(str_replace('_',' ',$s))
          })
          <option value="{{ $s }}" @selected(old('status')===$s)>{{ $label }}</option>
        @endforeach
      </select>
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
  </div>
  <div class="mt-3">
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('lead.cards.index') }}" class="btn btn-secondary">Batal</a>
  </div>
</form>
@endsection
