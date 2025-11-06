@extends('layouts.admin')
@section('title','Edit User')
@section('adminContent')
<h3 class="mb-3">Edit User</h3>
<form method="POST" action="{{ route('admin.users.update',$user) }}" class="card p-3 shadow-sm">
  @csrf @method('PUT')
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nama Lengkap</label><input name="fullname" class="form-control" value="{{ old('fullname',$user->fullname) }}"></div>
    <div class="col-md-3"><label class="form-label">Username</label><input name="username" class="form-control" required value="{{ old('username',$user->username) }}"></div>
    <div class="col-md-3"><label class="form-label">Role</label>
      <select name="role" class="form-select">
        @foreach($roles as $r)<option value="{{ $r }}" @selected(old('role',$user->role)===$r)>{{ $r }}</option>@endforeach
      </select>
    </div>
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="{{ old('email',$user->email) }}"></div>
    <div class="col-md-3"><label class="form-label">Password (opsional)</label><input type="password" name="password" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Status</label><input name="status" class="form-control" value="{{ old('status',$user->status) }}"></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Update</button> <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a></div>
</form>
@endsection
