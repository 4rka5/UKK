@extends('layouts.lead')
@section('title','Edit Board')
@section('leadContent')
<h3 class="mb-3">Edit Board</h3>
<form method="POST" action="{{ route('lead.boards.update',$board) }}" class="card p-3 shadow-sm">
  @csrf @method('PUT')
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Project</label>
      <select name="project_id" class="form-select" required>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" @selected(old('project_id',$board->project_id)==$p->id)>{{ $p->project_name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Nama Board</label>
      <input name="board_name" class="form-control" value="{{ old('board_name',$board->board_name) }}" required>
    </div>
    <div class="col-12">
      <label class="form-label">Deskripsi</label>
      <textarea name="description" class="form-control" rows="4">{{ old('description',$board->description) }}</textarea>
    </div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Update</button> <a href="{{ route('lead.boards.index') }}" class="btn btn-secondary">Batal</a></div>
 </form>
@endsection
