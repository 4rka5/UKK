<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Manajemen Proyek')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
  <style>
    body { background: #f6f8fb; }
    .auth-card { margin-top: 64px; }
    .navbar-brand { font-weight: 700; }
    .card-kpi h3 { margin: 0; font-weight: 700; }
    .card-kpi small { color: #667085; }
    .kanban { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .kanban-col { background: #fff; border-radius: 10px; border: 1px solid #eee; }
    .kanban-col h6 { padding: 12px 14px; border-bottom: 1px solid #f0f0f0; margin: 0; }
    .kanban-col .list { padding: 12px; }
    .kanban-card { background: #fafafa; border: 1px solid #eee; border-radius: 8px; padding: 10px; margin-bottom: 10px; }

    /* Hover/Active/Disabled: Login & Register */
    .btn { transition: all .15s ease-in-out; }
    .btn-primary:hover { background-color: #0b5ed7; border-color: #0a58ca; box-shadow: 0 0 0 .2rem rgba(13,110,253,.25); }
    .btn-primary:active, .btn-primary.active { background-color: #0a58ca !important; border-color: #0a53be !important; }
    .btn-primary:focus-visible { box-shadow: 0 0 0 .25rem rgba(13,110,253,.4); }
    .btn-primary.disabled, .btn-primary:disabled { background-color: #9ec5fe !important; border-color: #9ec5fe !important; color: #fff !important; pointer-events: none; }

    .btn-outline-primary:hover { color:#fff; background-color:#0d6efd; border-color:#0d6efd; box-shadow: 0 0 0 .2rem rgba(13,110,253,.2); }
    .btn-outline-primary:active, .btn-outline-primary.active { color:#fff !important; background-color:#0a58ca !important; border-color:#0a53be !important; }
    .btn-outline-primary:focus-visible { box-shadow: 0 0 0 .25rem rgba(13,110,253,.35); }
    .btn-outline-primary.disabled, .btn-outline-primary:disabled { color:#6c757d !important; border-color:#ced4da !important; background-color:#e9ecef !important; pointer-events:none; }
  </style>
  @stack('head')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/">P2</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav"
            aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div id="topNav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        @auth
          @php $role = auth()->user()->role ?? 'developer'; @endphp

          @if($role === 'admin')
            <li class="nav-item">
              <a class="nav-link {{ request()->is('admin*') ? 'active' : '' }}" href="{{ url('/admin') }}">Admin</a>
            </li>
          @endif

          @if($role === 'team_lead')
            <li class="nav-item">
              <a class="nav-link {{ request()->is('lead*') ? 'active' : '' }}" href="{{ url('/lead') }}">Team Lead</a>
            </li>
          @endif

          @if(!in_array($role, ['admin', 'team_lead']))
            <li class="nav-item">
              <a class="nav-link {{ request()->is('member*') ? 'active' : '' }}" href="{{ url('/member') }}">Member</a>
            </li>
          @endif
        @endauth
      </ul>

      <ul class="navbar-nav ms-auto">
        @auth
          <!-- Notifications Bell -->
          <li class="nav-item me-3">
            @include('partials.notifications')
          </li>
          
          @php $displayName = auth()->user()->fullname ?: auth()->user()->username; @endphp
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="me-2">{{ $displayName }}</span>
              <span class="badge text-bg-secondary">{{ strtoupper($role ?? auth()->user()->role) }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="{{ url('/profile') }}">Profil</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="{{ url('/logout') }}" class="px-3 py-1">
                  @csrf
                  <button class="btn btn-outline-danger w-100">Logout</button>
                </form>
              </li>
            </ul>
          </li>
        @else
          @php
            $isLogin = request()->routeIs('login') || request()->is('login');
            $isRegister = request()->is('register');
          @endphp
          <li class="nav-item">
            <a
              class="btn btn-outline-primary btn-sm me-2 {{ $isLogin ? 'active disabled' : '' }}"
              href="{{ $isLogin ? '#' : url('/login') }}"
              @if($isLogin) tabindex="-1" aria-disabled="true" @endif
            >Login</a>
          </li>
          <li class="nav-item">
            <a
              class="btn btn-outline-primary btn-sm {{ $isRegister ? 'active disabled' : '' }}"
              href="{{ $isRegister ? '#' : url('/register') }}"
              @if($isRegister) tabindex="-1" aria-disabled="true" @endif
            >Register</a>
          </li>
        @endauth
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>

