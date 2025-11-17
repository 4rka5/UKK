@extends('layouts.member')

@section('title', 'Kelola Subtask')

@section('memberContent')
<div class="card shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0">✓ Kelola Subtask</h5>
    <span class="badge bg-primary">{{ $totalSubtasks }} Total</span>
  </div>
  <div class="card-body">
    
    @if($totalSubtasks === 0)
      <div class="alert alert-light text-center py-5">
        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
        <p class="text-muted mt-3 mb-0">Belum ada subtask. Kunjungi card yang di-assign kepada Anda untuk menambahkan subtask.</p>
      </div>
    @else
      
      <!-- Statistics Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card border-0 bg-light">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <span class="badge bg-secondary rounded-circle p-3">📋</span>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-0 text-muted small">Todo</h6>
                  <h4 class="mb-0">{{ count($subtasksByStatus['todo']) }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 bg-light">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <span class="badge bg-warning rounded-circle p-3">🔄</span>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-0 text-muted small">In Progress</h6>
                  <h4 class="mb-0">{{ count($subtasksByStatus['in_progress']) }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 bg-light">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <span class="badge bg-success rounded-circle p-3">✅</span>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-0 text-muted small">Done</h6>
                  <h4 class="mb-0">{{ count($subtasksByStatus['done']) }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs for different status -->
      <ul class="nav nav-tabs mb-3" id="subtaskTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
            Semua ({{ $totalSubtasks }})
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="todo-tab" data-bs-toggle="tab" data-bs-target="#todo" type="button" role="tab">
            📋 Todo ({{ count($subtasksByStatus['todo']) }})
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="in-progress-tab" data-bs-toggle="tab" data-bs-target="#in-progress" type="button" role="tab">
            🔄 In Progress ({{ count($subtasksByStatus['in_progress']) }})
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="done-tab" data-bs-toggle="tab" data-bs-target="#done" type="button" role="tab">
            ✅ Done ({{ count($subtasksByStatus['done']) }})
          </button>
        </li>
      </ul>

      <!-- Tab Content -->
      <div class="tab-content" id="subtaskTabsContent">
        
        <!-- All Subtasks Tab -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
          @foreach($cards as $card)
            @if($card->subtasks->count() > 0)
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="mb-0">
                    <a href="{{ route('member.cards.show', $card->id) }}" class="text-decoration-none">
                      {{ $card->card_title }}
                    </a>
                  </h6>
                  <small class="text-muted">{{ $card->project->project_name }}</small>
                </div>
                <div class="list-group">
                  @foreach($card->subtasks as $subtask)
                    @include('member.subtasks.partials.subtask-item', ['subtask' => $subtask, 'card' => $card])
                  @endforeach
                </div>
              </div>
            @endif
          @endforeach
        </div>

        <!-- Todo Tab -->
        <div class="tab-pane fade" id="todo" role="tabpanel">
          @if(count($subtasksByStatus['todo']) === 0)
            <div class="alert alert-light text-center">
              <small class="text-muted">Tidak ada subtask dengan status Todo</small>
            </div>
          @else
            <div class="list-group">
              @foreach($subtasksByStatus['todo'] as $subtask)
                @include('member.subtasks.partials.subtask-item', ['subtask' => $subtask, 'card' => $subtask->card, 'showCard' => true])
              @endforeach
            </div>
          @endif
        </div>

        <!-- In Progress Tab -->
        <div class="tab-pane fade" id="in-progress" role="tabpanel">
          @if(count($subtasksByStatus['in_progress']) === 0)
            <div class="alert alert-light text-center">
              <small class="text-muted">Tidak ada subtask dengan status In Progress</small>
            </div>
          @else
            <div class="list-group">
              @foreach($subtasksByStatus['in_progress'] as $subtask)
                @include('member.subtasks.partials.subtask-item', ['subtask' => $subtask, 'card' => $subtask->card, 'showCard' => true])
              @endforeach
            </div>
          @endif
        </div>

        <!-- Done Tab -->
        <div class="tab-pane fade" id="done" role="tabpanel">
          @if(count($subtasksByStatus['done']) === 0)
            <div class="alert alert-light text-center">
              <small class="text-muted">Tidak ada subtask dengan status Done</small>
            </div>
          @else
            <div class="list-group">
              @foreach($subtasksByStatus['done'] as $subtask)
                @include('member.subtasks.partials.subtask-item', ['subtask' => $subtask, 'card' => $subtask->card, 'showCard' => true])
              @endforeach
            </div>
          @endif
        </div>

      </div>

    @endif

  </div>
</div>

<script>
function updateSubtaskStatus(subtaskId, newStatus) {
  const previousValue = event.target.dataset.previousValue || event.target.value;
  
  fetch('{{ url("/member/subtasks") }}/' + subtaskId, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ status: newStatus })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Update the dropdown's previous value
      event.target.dataset.previousValue = newStatus;
      
      // Reload page to update counters and tabs
      location.reload();
    } else {
      // Revert dropdown to previous value on error
      event.target.value = previousValue;
      alert('Gagal mengubah status: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(error => {
    // Revert dropdown to previous value on error
    event.target.value = previousValue;
    console.error('Error:', error);
    alert('Terjadi kesalahan saat mengubah status subtask');
  });
}

function deleteSubtask(subtaskId) {
  if (!confirm('Apakah Anda yakin ingin menghapus subtask ini?')) {
    return;
  }
  
  fetch('{{ url("/member/subtasks") }}/' + subtaskId, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Reload page to remove deleted subtask
      location.reload();
    } else {
      alert('Gagal menghapus subtask: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat menghapus subtask');
  });
}
</script>

@endsection
