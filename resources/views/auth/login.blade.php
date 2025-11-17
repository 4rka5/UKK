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

        <!-- Quick Login (developer convenience) - only in non-production -->
        @if(app()->environment(['local','development']) || config('app.debug'))
        <div class="mb-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <small class="text-muted">Login Cepat (untuk pengujian)</small>
            <div class="d-flex align-items-center gap-2">
              <small class="text-muted"><em>Klik tombol untuk isi</em></small>
              <div class="form-check form-switch mb-0">
                 <label class="form-check-label small text-muted" for="ql_autosubmit">Auto-submit</label>
              </div>
            </div>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary quick-login" data-login="admin@gmail.com" data-password="admin123">Admin</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-login" data-login="lead@gmail.com" data-password="lead123">Team Lead</button>
            <button type="button" class="btn btn-sm btn-outline-info quick-login" data-login="dev@gmail.com" data-password="dev123">Developer</button>
            <button type="button" class="btn btn-sm btn-outline-warning quick-login" data-login="des@gmail.com" data-password="des123">Designer</button>
          </div>
          <small class="form-text text-muted">Ubah email/password di tombol jika perlu (default: <code>admin123 / lead123 / dev123 / des123</code>).</small>
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
      <script>
      document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.quick-login');
        const loginInput = document.getElementById('login');
        const passwordInput = document.getElementById('password');
        const form = document.querySelector('form[action="{{ route('login') }}"]');
        const autoCheckbox = document.getElementById('ql_autosubmit');

        buttons.forEach(btn => {
          btn.addEventListener('click', function() {
            const login = this.dataset.login || '';
            const pwd = this.dataset.password || '';
            if (loginInput) loginInput.value = login;
            if (passwordInput) passwordInput.value = pwd;

            const auto = autoCheckbox ? autoCheckbox.checked : true;

            if (auto) {
              // small delay to allow UI update
              setTimeout(() => { if (form) form.submit(); }, 200);
            } else {
              // show a confirmation so developer can review before submit
              if (confirm('Isi form sudah siap. Lanjutkan login sebagai "' + login + '" ?')) {
                if (form) form.submit();
              }
            }
          });
        });
      });
      </script>

      @endsection
