@extends('layouts.lead')
@section('title','Boards')
@section('leadContent')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Boards</h3>
  <a href="{{ route('lead.boards.create') }}" class="btn btn-primary btn-sm">+ Board</a>
  </div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Board</th>
          <th>Project</th>
          <th>Deskripsi</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($boards as $b)
          <tr>
            <td>{{ data_get($b,'board_name') }}</td>
            <td>{{ data_get($b,'project.project_name','-') }}</td>
            <td class="text-truncate" style="max-width: 380px">{{ data_get($b,'description') }}</td>
            <td class="text-end">
              <a href="{{ route('lead.boards.edit',$b) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
              <form action="{{ route('lead.boards.destroy',$b) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus board?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted p-3">Belum ada board.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">{{ $boards->links() }}</div>
 </div>
@endsection
