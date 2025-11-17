<div class="list-group-item" id="subtask{{ $subtask->id }}">
  <div class="d-flex justify-content-between align-items-start">
    <div class="flex-grow-1">
      @if(isset($showCard) && $showCard)
        <small class="text-muted d-block mb-1">
          <a href="{{ route('member.cards.show', $card->id) }}" class="text-decoration-none">
            {{ $card->card_title }}
          </a>
        </small>
      @endif
      
      <div class="d-flex align-items-center gap-2 mb-1">
        <select class="form-select form-select-sm" style="width: auto;" onchange="updateSubtaskStatus({{ $subtask->id }}, this.value)" data-previous-value="{{ $subtask->status }}">
          <option value="todo" {{ $subtask->status === 'todo' ? 'selected' : '' }}>📋 Todo</option>
          <option value="in_progress" {{ $subtask->status === 'in_progress' ? 'selected' : '' }}>🔄 In Progress</option>
          <option value="done" {{ $subtask->status === 'done' ? 'selected' : '' }}>✅ Done</option>
        </select>
        <strong class="{{ $subtask->status === 'done' ? 'text-decoration-line-through text-muted' : '' }}">
          {{ $subtask->subtask_title }}
        </strong>
      </div>
      
      @if($subtask->description)
        <small class="text-muted d-block">{{ $subtask->description }}</small>
      @endif
      
      <div class="d-flex gap-3 mt-1">
        @if($subtask->estimated_hours)
          <small class="text-muted">⏱️ {{ $subtask->estimated_hours }} jam</small>
        @endif
        <small class="text-muted">📅 {{ $subtask->created_at->format('d M Y H:i') }}</small>
      </div>
    </div>
    
    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSubtask({{ $subtask->id }})" title="Hapus subtask">
      <i class="bi bi-trash"></i>
    </button>
  </div>
</div>
