@extends('layouts.admin')
@section('title','Kelola User')
@section('adminContent')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">User</h3>
  <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">+ User</a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
      <tbody>
      @foreach($users as $u)
        <tr>
          <td>{{ $u->fullname }}</td>
          <td>{{ $u->username }}</td>
          <td>{{ $u->email }}</td>
          <td><span class="badge text-bg-secondary">{{ $u->role }}</span></td>
          <td>{{ $u->status }}</td>
          <td class="text-end">
            <a href="{{ route('admin.users.edit',$u) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
            <form action="{{ route('admin.users.destroy',$u) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus user?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Hapus</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection
