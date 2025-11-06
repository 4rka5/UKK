@extends('layouts.admin')
@section('title','Tambah User')
@section('adminContent')
<h3 class="mb-3">Tambah User</h3>
<form method="POST" action="{{ route('admin.users.store') }}" class="card p-3 shadow-sm">
  @csrf
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nama Lengkap</label><input name="fullname" class="form-control" value="{{ old('fullname') }}"></div>
    <div class="col-md-3"><label class="form-label">Username</label><input name="username" class="form-control" required value="{{ old('username') }}"></div>
    <div class="col-md-3"><label class="form-label">Role</label>
      <select name="role" class="form-select">
        @foreach($roles as $r)<option value="{{ $r }}" @selected(old('role')===$r)>{{ $r }}</option>@endforeach
      </select>
    </div>
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="{{ old('email') }}"></div>
    <div class="col-md-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Status</label><input name="status" class="form-control" value="{{ old('status','active') }}"></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Simpan</button> <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a></div>
</form>
@endsection
