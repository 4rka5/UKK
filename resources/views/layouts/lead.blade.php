@extends('layouts.app')

@section('title', trim($__env->yieldContent('title', 'Team Lead')))

@section('content')
  <div class="row g-3">
    <div class="col-12 col-lg-3">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Team Lead</div>
        <div class="list-group list-group-flush">
          <a href="{{ url('/lead') }}" class="list-group-item list-group-item-action {{ request()->is('lead') ? 'active' : '' }}">Dashboard</a>
          <a href="{{ url('/lead/boards/create') }}" class="list-group-item list-group-item-action">+ Board</a>
          <a href="{{ url('/lead/cards/create') }}" class="list-group-item list-group-item-action">+ Card</a>
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
