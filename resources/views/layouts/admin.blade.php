@extends('layouts.app')

@section('title', trim($__env->yieldContent('title', 'Admin')))

@section('content')
  <div class="row g-3">
    <div class="col-12 col-lg-3">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Admin</div>
        <div class="list-group list-group-flush">
          <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            Dashboard
          </a>
          <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/users*') ? 'active' : '' }}">
            Users
          </a>
          <a href="{{ route('admin.projects.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/projects*') ? 'active' : '' }}">
            Projects
          </a>
        </div>
      </div>
      <div class="small text-muted mt-2 ms-1">Logged in as: {{ auth()->user()->fullname ?? auth()->user()->username }}</div>
    </div>

    <div class="col-12 col-lg-9">
      @include('includes.alerts')
      @yield('adminContent')
    </div>
  </div>
@endsection
