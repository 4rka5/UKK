@extends('layouts.app')

@section('title', trim($__env->yieldContent('title', 'Team Lead')))

@section('content')
  <div class="row g-3">
    <div class="col-12 col-lg-3">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Team Lead</div>
        <div class="list-group list-group-flush">
          <a href="{{ url('/lead') }}" class="list-group-item list-group-item-action {{ request()->is('lead') && !request()->is('lead/*') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
          <a href="{{ route('lead.projects.index') }}" class="list-group-item list-group-item-action {{ request()->is('lead/projects*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-check"></i> Project Saya
          </a>
          <a href="{{ url('/lead/cards') }}" class="list-group-item list-group-item-action {{ request()->is('lead/cards*') ? 'active' : '' }}">
            <i class="bi bi-kanban"></i> Kelola Cards
          </a>
        </div>
      </div>
      <div class="small text-muted mt-2 ms-1">Lead: {{ auth()->user()->fullname ?? auth()->user()->username }}</div>
    </div>

    <div class="col-12 col-lg-9">
      @include('includes.alerts')
      @yield('leadContent')
    </div>
  </div>
@endsection
