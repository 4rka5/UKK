@extends('layouts.app')

@section('title', trim($__env->yieldContent('title', 'Member')))

@section('content')
  <div class="row g-3">
    <div class="col-12 col-lg-3">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
          <span>🎨</span> Member Area
        </div>
        <div class="list-group list-group-flush">
          <a href="{{ route('member.dashboard') }}" class="list-group-item list-group-item-action {{ request()->is('member') && !request()->is('member/*') ? 'active' : '' }}">
            📋 Dashboard
          </a>
        </div>
      </div>
      <div class="card shadow-sm mt-3">
        <div class="card-header bg-white fw-semibold">User Info</div>
        <div class="card-body">
          <div class="mb-2">
            <small class="text-muted d-block">Name</small>
            <strong>{{ auth()->user()->fullname ?? auth()->user()->username }}</strong>
          </div>
          <div class="mb-2">
            <small class="text-muted d-block">Role</small>
            <span class="badge bg-info">{{ ucfirst(auth()->user()->role) }}</span>
          </div>
          <div>
            <small class="text-muted d-block">Email</small>
            <small>{{ auth()->user()->email }}</small>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-9">
      @include('includes.alerts')
      @yield('memberContent')
    </div>
  </div>
@endsection
