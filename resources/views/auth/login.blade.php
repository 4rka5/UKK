@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card auth-card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Login</h4>
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

        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="mb-3">
            <label for="login" class="form-label">Email atau Username</label>
            <input type="text" class="form-control @error('login') is-invalid @enderror"
                   id="login" name="login" value="{{ old('login') }}" required autofocus>
            @error('login')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password" required>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Masuk</button>
          </div>

          <div class="text-center mt-3">
            <small>Belum punya akun? <a href="{{ url('/register') }}">Daftar di sini</a></small>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
