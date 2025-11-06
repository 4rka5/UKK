@extends('layouts.admin')
@section('title','Kelola Project')
@section('adminContent')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Project</h3>
  <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-sm">+ Project</a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Nama</th><th>Owner</th><th>Deadline</th><th></th></tr></thead>
      <tbody>
        @forelse($projects as $p)
          <tr>
            <td>{{ $p->project_name }}</td>
            <td>{{ $p->owner->fullname ?? '-' }}</td>
            <td>{{ $p->deadline ?? '-' }}</td>
            <td class="text-end">
              <a href="{{ route('admin.projects.edit',$p) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
              <form action="{{ route('admin.projects.destroy',$p) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus project?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted p-3">Belum ada project.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">{{ $projects->links() }}</div>
</div>
@endsection
