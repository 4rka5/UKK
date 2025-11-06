@extends('layouts.lead')
@section('title','Cards')
@section('leadContent')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Cards</h3>
  <div class="d-flex align-items-center gap-2">
    <form method="get" class="d-flex align-items-center gap-2">
      <select name="board_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Semua Board</option>
        @foreach($boards as $b)
          <option value="{{ $b->id }}" @selected((string)request('board_id')===(string)$b->id)>{{ $b->board_name }}</option>
        @endforeach
      </select>
      <noscript><button class="btn btn-sm btn-outline-secondary">Filter</button></noscript>
    </form>
    <a href="{{ route('lead.cards.create') }}" class="btn btn-primary btn-sm">+ Card</a>
  </div>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Judul</th>
          <th>Board</th>
          <th>Status</th>
          <th>Priority</th>
          <th>Due</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($cards as $c)
          <tr>
            <td>{{ data_get($c,'card_title') }}</td>
            <td>{{ data_get($c,'board.board_name','-') }}</td>
            <td><span class="badge text-bg-secondary">{{ data_get($c,'status') }}</span></td>
            <td>{{ data_get($c,'priority') }}</td>
            <td>{{ data_get($c,'due_date','-') }}</td>
            <td class="text-end">
              <form action="{{ route('lead.cards.move',$c) }}" method="POST" class="d-inline">
                @csrf @method('PATCH')
                <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                  @foreach($statuses as $k)
                    @php($label = match($k){
                      'backlog' => 'Backlog',
                      'todo' => 'To Do',
                      'in_progress' => 'In Progress',
                      'code_review' => 'Code Review',
                      'testing' => 'Testing',
                      'done' => 'Done',
                      default => ucfirst(str_replace('_',' ',$k))
                    })
                    <option value="{{ $k }}" @selected(data_get($c,'status')===$k)>{{ $label }}</option>
                  @endforeach
                </select>
              </form>
              <a href="{{ route('lead.cards.edit',$c) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
              <form action="{{ route('lead.cards.destroy',$c) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus card?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted p-3">Belum ada card.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">{{ $cards->links() }}</div>
 </div>
@endsection
