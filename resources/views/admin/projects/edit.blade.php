@extends('layouts.admin')
@section('title','Edit Project')
@section('adminContent')
<div class="mb-3">
  <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>
<h3 class="mb-3">Edit Project</h3>
<form method="POST" action="{{ route('admin.projects.update',$project) }}" class="card p-3 shadow-sm">
  @csrf @method('PUT')
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nama Project</label><input name="project_name" class="form-control" required value="{{ old('project_name',$project->project_name) }}"></div>
    <div class="col-md-3"><label class="form-label">Deadline</label><input type="date" name="deadline" class="form-control" value="{{ old('deadline',$project->deadline) }}"></div>
    <div class="col-md-3"><label class="form-label">Owner</label>
      <select name="created_by" class="form-select">
        @foreach($owners as $o)<option value="{{ $o->id }}" @selected(old('created_by',$project->created_by)==$o->id)>{{ $o->fullname ?: $o->username }}</option>@endforeach
      </select>
    </div>
    <div class="col-12"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="4">{{ old('description',$project->description) }}</textarea></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Update</button> <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">Batal</a></div>
</form>
@endsection
