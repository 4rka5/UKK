@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7 col-lg-6">
    <div class="card auth-card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Buat Akun</h4>
      </div>
      <div class="card-body">
        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ url('/register') }}">
          @csrf

          <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="fullname" class="form-control @error('fullname') is-invalid @enderror"
                   value="{{ old('fullname') }}" required>
            @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                   value="{{ old('username') }}" required>
            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Role</label>
            <div class="btn-group" role="group">
              <input type="radio" class="btn-check" name="role" id="roleDesigner" value="designer" {{ old('role','designer')==='designer'?'checked':'' }}>
              <label class="btn btn-outline-secondary" for="roleDesigner">Designer</label>

              <input type="radio" class="btn-check" name="role" id="roleDeveloper" value="developer" {{ old('role')==='developer'?'checked':'' }}>
              <label class="btn btn-outline-secondary" for="roleDeveloper">Developer</label>
            </div>
            @error('role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Daftar</button>
          </div>

          <div class="text-center mt-3">
            <small>Sudah punya akun? <a href="{{ url('/login') }}">Masuk</a></small>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
