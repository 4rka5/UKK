@extends('layouts.member')
@section('title', 'Detail Card')
@section('memberContent')

<style>
.detail-card { background: white; border-radius: 10px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
.detail-header { border-bottom: 2px solid #e9ecef; padding-bottom: 1rem; margin-bottom: 1.5rem; }
.info-row { display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; }
.info-row:last-child { border-bottom: none; }
.info-label { font-weight: 600; color: #6c757d; }
.status-select { max-width: 300px; }
</style>

<div class="mb-3">
  <a href="{{ route('member.dashboard') }}" class="btn btn-outline-secondary btn-sm">
    ← Kembali ke Dashboard
  </a>
</div>

<!-- Card Header -->
<div class="detail-card">
  <div class="detail-header">
    <h3 class="mb-2">{{ $card->card_title }}</h3>
    <div class="d-flex gap-2 flex-wrap">
      <span class="badge bg-{{ $card->priority === 'high' ? 'danger' : ($card->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
        Priority: {{ strtoupper($card->priority) }}
      </span>
      <span class="badge
        @if($card->status === 'done') bg-success
        @elseif($card->status === 'in_progress') bg-warning text-dark
        @elseif($card->status === 'code_review') bg-info
        @else bg-secondary
        @endif">
        {{ str_replace('_', ' ', strtoupper($card->status)) }}
      </span>
      @if($card->project)
        <span class="badge bg-light text-dark border">📋 {{ $card->project->project_name }}</span>
      @endif
      @if($card->project)
        <span class="badge bg-light text-dark border">📁 {{ $card->project->project_name }}</span>
      @endif
    </div>
  </div>

  <!-- Card Info -->
  <div class="info-row">
    <div class="info-label">Deskripsi</div>
    <div>{{ $card->description ?: '-' }}</div>
  </div>

  <div class="info-row">
    <div class="info-label">Due Date</div>
    <div>
      @if($card->due_date)
        {{ \Carbon\Carbon::parse($card->due_date)->format('d F Y') }}
        @if(\Carbon\Carbon::parse($card->due_date)->isPast() && $card->status !== 'done')
          <span class="badge bg-danger ms-2">OVERDUE</span>
        @endif
      @else
        -
      @endif
    </div>
  </div>

  <div class="info-row">
    <div class="info-label">Estimasi Jam</div>
    <div>{{ $card->estimated_hours ? number_format($card->estimated_hours, 2) . ' jam' : '-' }}</div>
  </div>

  <div class="info-row">
    <div class="info-label">Aktual Jam</div>
    <div>{{ $card->actual_hours ? number_format($card->actual_hours, 2) . ' jam' : '-' }}</div>
  </div>

  <div class="info-row">
    <div class="info-label">Dibuat Oleh</div>
    <div>{{ $card->creator->fullname ?? $card->creator->username ?? '-' }}</div>
  </div>

  <div class="info-row">
    <div class="info-label">Ditugaskan Ke</div>
    <div>
      @if($card->assignees && $card->assignees->count() > 0)
        @foreach($card->assignees as $assignee)
          <span class="badge bg-primary me-1">{{ $assignee->fullname ?? $assignee->username }}</span>
        @endforeach
      @else
        <span class="text-muted">Belum ada yang ditugaskan</span>
      @endif
    </div>
  </div>
</div>

<!-- Info: Status hanya bisa diubah oleh Team Lead -->
<div class="detail-card">
  <h5 class="mb-3">ℹ️ Informasi Status</h5>
  <div class="alert alert-info mb-0">
    <strong>Status Card:</strong> 
    <span class="badge bg-{{ $card->status === 'done' ? 'success' : ($card->status === 'in_progress' ? 'warning text-dark' : ($card->status === 'review' ? 'info' : 'secondary')) }} ms-2">
      {{ strtoupper(str_replace('_', ' ', $card->status)) }}
    </span>
    <br>
    <small class="text-muted">Status card hanya dapat diubah oleh Team Lead. Silakan gunakan fitur di bawah untuk melaporkan progress pekerjaan Anda.</small>
  </div>
</div>

<!-- Work Timer -->
@php
  $userAssignment = $card->assignees->where('id', auth()->id())->first();
  $isWorking = $userAssignment ? $userAssignment->pivot->is_working : false;
  $totalSeconds = $userAssignment ? $userAssignment->pivot->total_work_seconds : 0;
@endphp

@if($card->status !== 'done')
<div class="detail-card">
  <h5 class="mb-3">⏱️ Timer Pekerjaan</h5>
  
  <div class="row align-items-center">
    <div class="col-md-6">
      <div class="text-center p-4 bg-light rounded">
        <div class="display-4 fw-bold font-monospace" id="timer-display">
          @php
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;
          @endphp
          {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
        </div>
        
        @if($isWorking)
          <div class="mt-2">
            <span class="badge bg-success pulse">● Timer Berjalan</span>
          </div>
        @endif
      </div>
    </div>
    
    <div class="col-md-6">
      @php
        $isOverdue = $card->isOverdue();
        $canWork = $card->canUserWork(auth()->id());
        $userAssignmentPivot = $card->assignees()->where('users.id', auth()->id())->first()->pivot ?? null;
        $extensionRequested = $userAssignmentPivot && $userAssignmentPivot->extension_requested;
        $extensionApproved = $userAssignmentPivot && $userAssignmentPivot->extension_approved === true;
        $extensionRejected = $userAssignmentPivot && $userAssignmentPivot->extension_approved === false;
      @endphp
      
      @if($isOverdue && !$canWork)
        <div class="alert alert-danger">
          <h6>⚠️ Task Melewati Deadline!</h6>
          <p class="mb-2">Tugas ini sudah melewati deadline (<strong>{{ \Carbon\Carbon::parse($card->due_date)->format('d M Y') }}</strong>).</p>
          
          @if($extensionRequested && is_null($userAssignmentPivot->extension_approved))
            <div class="alert alert-warning mb-0">
              <small><strong>⏳ Menunggu Approval</strong><br>
              Permohonan perpanjangan Anda sedang diproses oleh Team Lead.</small>
            </div>
          @elseif($extensionRejected)
            <div class="alert alert-danger mb-0">
              <small><strong>❌ Perpanjangan Ditolak</strong><br>
              Hubungi Team Lead Anda untuk informasi lebih lanjut.</small>
            </div>
          @else
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#extensionModal">
              📝 Ajukan Perpanjangan Deadline
            </button>
          @endif
        </div>
      @elseif($extensionApproved)
        <div class="alert alert-success mb-3">
          <small>✅ Perpanjangan deadline Anda telah disetujui. Silakan lanjutkan pekerjaan.</small>
        </div>
      @endif
      
      <div class="d-grid gap-2">
        @if(!$isWorking)
          <form method="POST" action="{{ route('member.cards.timer.start', $card) }}">
            @csrf
            <button type="submit" class="btn btn-success btn-lg w-100" {{ !$canWork ? 'disabled' : '' }}>
              ▶️ Mulai Kerjakan
            </button>
          </form>
        @else
          <form method="POST" action="{{ route('member.cards.timer.pause', $card) }}">
            @csrf
            <button type="submit" class="btn btn-warning btn-lg w-100">
              ⏸️ Pause
            </button>
          </form>
        @endif
        
        <form method="POST" action="{{ route('member.cards.timer.stop', $card) }}" onsubmit="return confirm('Yakin ingin menyelesaikan pekerjaan? Timer akan direset dan waktu akan dicatat.')">
          @csrf
          <button type="submit" class="btn btn-danger btn-lg w-100" {{ !$totalSeconds && !$isWorking ? 'disabled' : '' }}>
            ⏹️ Selesai & Catat Waktu
          </button>
        </form>
      </div>
      
      <div class="mt-3">
        <small class="text-muted">
          <strong>Cara Kerja:</strong><br>
          1. Klik "Mulai Kerjakan" untuk mulai timer<br>
          2. Klik "Pause" jika perlu istirahat<br>
          3. Klik "Selesai" jika sudah selesai mengerjakan<br>
          <em>* Waktu akan otomatis tercatat di Actual Hours</em>
        </small>
      </div>
    </div>
  </div>
</div>
@else
<div class="detail-card">
  <div class="alert alert-success mb-0">
    <h5 class="mb-3">✅ Tugas Selesai</h5>
    <p class="mb-2">Tugas ini sudah selesai dan disetujui oleh Team Lead.</p>
    <div class="row g-3 mt-2">
      <div class="col-md-4">
        <strong>Total Waktu Kerja:</strong><br>
        <span class="fs-4 text-success">
          @php
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;
          @endphp
          {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
        </span>
        <small class="text-muted d-block">({{ number_format($totalSeconds / 3600, 2) }} jam)</small>
      </div>
      <div class="col-md-4">
        <strong>Actual Hours:</strong><br>
        <span class="fs-4 text-primary">{{ $card->actual_hours ? number_format($card->actual_hours, 2) : '0.00' }} jam</span>
      </div>
      <div class="col-md-4">
        <strong>Estimasi:</strong><br>
        <span class="fs-4">{{ $card->estimated_hours ? number_format($card->estimated_hours, 2) : '-' }} jam</span>
      </div>
    </div>
  </div>
</div>
@endif

<style>
.pulse {
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
.font-monospace {
  font-family: 'Courier New', monospace;
}
</style>

@if($card->status !== 'done')
<script>
// Timer variables
let timerInterval = null;
let isWorking = {{ $isWorking ? 'true' : 'false' }};
let cardId = {{ $card->id }};
let baseSeconds = {{ $totalSeconds }};
let currentSeconds = {{ $totalSeconds }};
let serverStartTimestamp = null;

console.log('Timer initialized:', { isWorking, cardId, baseSeconds });

function formatTime(seconds) {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;
  return String(hours).padStart(2, '0') + ':' + 
         String(minutes).padStart(2, '0') + ':' + 
         String(secs).padStart(2, '0');
}

function updateTimerDisplay() {
  if (isWorking && serverStartTimestamp) {
    // Calculate elapsed time since server start time
    const now = Math.floor(Date.now() / 1000); // Current time in seconds
    const elapsedSeconds = now - serverStartTimestamp;
    currentSeconds = baseSeconds + elapsedSeconds;
  } else {
    currentSeconds = baseSeconds;
  }
  
  const formatted = formatTime(currentSeconds);
  document.getElementById('timer-display').textContent = formatted;
  
  // Update hours_spent field (convert seconds to hours)
  const hoursSpentField = document.getElementById('hours_spent');
  if (hoursSpentField) {
    const hours = (currentSeconds / 3600).toFixed(2);
    hoursSpentField.value = hours;
  }
}

function startTimer() {
  if (!timerInterval) {
    timerInterval = setInterval(updateTimerDisplay, 1000);
    updateTimerDisplay(); // Update immediately
    console.log('Timer running with server timestamp:', serverStartTimestamp);
  }
}

// Initialize timer if working
if (isWorking) {
  @if($userAssignment && $userAssignment->pivot->work_started_at)
    // Get server start time as Unix timestamp (seconds)
    serverStartTimestamp = {{ strtotime($userAssignment->pivot->work_started_at) }};
    baseSeconds = {{ $userAssignment->pivot->total_work_seconds }};
    
    console.log('Timer sync from server:', {
      serverStartTime: new Date(serverStartTimestamp * 1000),
      currentTime: new Date(),
      baseSeconds: baseSeconds
    });
  @else
    // If no work_started_at, set to current time
    serverStartTimestamp = Math.floor(Date.now() / 1000);
    console.log('Starting fresh timer at:', new Date());
  @endif
  
  startTimer();
} else {
  // Update display even if not working
  updateTimerDisplay();
}

// Update hours_spent on page load
document.addEventListener('DOMContentLoaded', function() {
  updateTimerDisplay();
});

// Stop interval when page unloads
window.addEventListener('beforeunload', function() {
  if (timerInterval) {
    clearInterval(timerInterval);
  }
});
</script>
@endif

<!-- Role-Specific Actions -->
@if($card->status !== 'done' && auth()->user()->role === 'developer')
  <!-- Developer Actions - Unified Form -->
  <div class="detail-card">
    <h5 class="mb-3">💻 Developer Actions</h5>
    
    @php
      $isOverdue = $card->isOverdue();
      $canWork = $card->canUserWork(auth()->id());
      $userAssignmentPivot = $card->assignees()->where('users.id', auth()->id())->first()->pivot ?? null;
      $extensionRequested = $userAssignmentPivot && $userAssignmentPivot->extension_requested;
      $extensionApproved = $userAssignmentPivot && $userAssignmentPivot->extension_approved === true;
      $extensionRejected = $userAssignmentPivot && $userAssignmentPivot->extension_approved === false;
    @endphp
    
    @if($isOverdue && !$canWork)
      <div class="alert alert-danger mb-3">
        <h6>⚠️ Task Melewati Deadline!</h6>
        <p class="mb-2">Tugas ini sudah melewati deadline (<strong>{{ \Carbon\Carbon::parse($card->due_date)->format('d M Y') }}</strong>). Developer actions tidak dapat digunakan.</p>
        
        @if($extensionRequested && is_null($userAssignmentPivot->extension_approved))
          <div class="alert alert-warning mb-0">
            <small><strong>⏳ Menunggu Approval</strong><br>
            Permohonan perpanjangan Anda sedang diproses oleh Team Lead.</small>
          </div>
        @elseif($extensionRejected)
          <div class="alert alert-danger mb-0">
            <small><strong>❌ Perpanjangan Ditolak</strong><br>
            Hubungi Team Lead Anda untuk informasi lebih lanjut.</small>
          </div>
        @else
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#extensionModal">
            📝 Ajukan Perpanjangan Deadline
          </button>
        @endif
      </div>
    @elseif($extensionApproved)
      <div class="alert alert-success mb-3">
        <small>✅ Perpanjangan deadline Anda telah disetujui. Silakan lanjutkan pekerjaan.</small>
      </div>
    @endif
    
    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-3" id="developerTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="progress-tab" data-bs-toggle="tab" data-bs-target="#progress" type="button" role="tab" {{ !$canWork ? 'disabled' : '' }}>
          📊 Update Progress
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab" {{ !$canWork ? 'disabled' : '' }}>
          📎 Upload File
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="blocker-tab" data-bs-toggle="tab" data-bs-target="#blocker" type="button" role="tab" {{ !$canWork ? 'disabled' : '' }}>
          🚫 Report Blocker
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs" type="button" role="tab" {{ !$canWork ? 'disabled' : '' }}>
          📝 Dokumentasi
        </button>
      </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="developerTabsContent">
      
      <!-- Update Progress Tab -->
      <div class="tab-pane fade show active" id="progress" role="tabpanel">
        <div class="alert alert-info mb-3">
          <small><strong>ℹ️ Fungsi:</strong> Melaporkan progress pekerjaan Anda. Setelah submit, status card akan otomatis berubah menjadi <strong>"Review"</strong> dan Team Lead akan menerima notifikasi untuk review hasil kerja Anda.</small>
        </div>
        <form method="POST" action="{{ route('developer.tasks.progress', $card) }}">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Persentase (%)</label>
              <input type="number" name="progress_percentage" class="form-control" min="0" max="100" placeholder="75" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Jam Kerja <span class="text-danger">*</span></label>
              <input type="number" name="hours_spent" id="hours_spent" class="form-control" step="0.01" min="0" readonly style="background-color: #e9ecef;">
              <small class="text-muted">Otomatis dari timer ({{ sprintf('%02d:%02d:%02d', floor($totalSeconds / 3600), floor(($totalSeconds % 3600) / 60), $totalSeconds % 60) }})</small>
            </div>
            <div class="col-12">
              <label class="form-label">Catatan</label>
              <textarea name="progress_note" class="form-control" rows="3" placeholder="Tuliskan Progres Yang Anda Capai"></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary">Submit Progress</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Upload File Tab -->
      <div class="tab-pane fade" id="upload" role="tabpanel">
        <div class="alert alert-info mb-3">
          <small><strong>ℹ️ Fungsi:</strong> Upload file pendukung seperti screenshot hasil test, file kode, atau dokumen lainnya. File akan tersimpan sebagai attachment dan bisa diakses oleh Team Lead.</small>
        </div>
        <form method="POST" action="{{ route('developer.tasks.upload', $card) }}" enctype="multipart/form-data">
          @csrf
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">File Attachment</label>
              <input type="file" name="attachment" class="form-control" required>
              <small class="text-muted">Max 10MB</small>
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <input type="text" name="description" class="form-control" placeholder="Tuliskan Deskripsi FIle">
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-success">Upload</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Report Blocker Tab -->
      <div class="tab-pane fade" id="blocker" role="tabpanel">
        <div class="alert alert-warning mb-3">
          <small><strong>⚠️ Fungsi:</strong> Laporkan kendala atau blocker yang menghambat pekerjaan Anda (contoh: API belum siap, akses database tidak ada, dll). Priority card akan otomatis diubah menjadi <strong>HIGH</strong> dan Team Lead akan segera diberitahu.</small>
        </div>
        <form method="POST" action="{{ route('developer.tasks.blocker', $card) }}">
          @csrf
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Kendala/Blocker</label>
              <textarea name="blocker_description" class="form-control" rows="4" placeholder="Tuliskan Kendala Anda" required></textarea>
              <small class="text-muted">Otomatis set priority HIGH</small>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-danger">Report Blocker</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Work Documentation Tab -->
      <div class="tab-pane fade" id="docs" role="tabpanel">
        <div class="alert alert-info mb-3">
          <small><strong>ℹ️ Fungsi:</strong> Tulis dokumentasi teknis tentang implementasi yang Anda kerjakan. Dokumentasi ini akan membantu tim memahami cara kerja fitur dan mempermudah maintenance di masa depan.</small>
        </div>
        <form method="POST" action="{{ route('developer.tasks.documentation', $card) }}">
          @csrf
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Dokumentasi Kerja</label>
              <textarea name="documentation" class="form-control" rows="4" placeholder="Tulisknan Dokumentasi Singkat Pekerjaan Anda" required></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-info text-white">Save Documentation</button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>
@elseif($card->status !== 'done' && auth()->user()->role === 'designer')
  <!-- Designer Actions - Unified Form -->
  <div class="detail-card">
    <h5 class="mb-3">🎨 Designer Actions</h5>
    
    @php
      $isOverdue = $card->isOverdue();
      $canWork = $card->canUserWork(auth()->id());
      $userAssignmentPivot = $card->assignees()->where('users.id', auth()->id())->first()->pivot ?? null;
      $extensionRequested = $userAssignmentPivot && $userAssignmentPivot->extension_requested;
      $extensionApproved = $userAssignmentPivot && $userAssignmentPivot->extension_approved === true;
      $extensionRejected = $userAssignmentPivot && $userAssignmentPivot->extension_approved === false;
    @endphp
    
    @if($isOverdue && !$canWork)
      <div class="alert alert-danger mb-3">
        <h6>⚠️ Task Melewati Deadline!</h6>
        <p class="mb-2">Tugas ini sudah melewati deadline (<strong>{{ \Carbon\Carbon::parse($card->due_date)->format('d M Y') }}</strong>). Designer actions tidak dapat digunakan.</p>
        
        @if($extensionRequested && is_null($userAssignmentPivot->extension_approved))
          <div class="alert alert-warning mb-0">
            <small><strong>⏳ Menunggu Approval</strong><br>
            Permohonan perpanjangan Anda sedang diproses oleh Team Lead.</small>
          </div>
        @elseif($extensionRejected)
          <div class="alert alert-danger mb-0">
            <small><strong>❌ Perpanjangan Ditolak</strong><br>
            Hubungi Team Lead Anda untuk informasi lebih lanjut.</small>
          </div>
        @else
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#extensionModal">
            📝 Ajukan Perpanjangan Deadline
          </button>
        @endif
      </div>
    @elseif($extensionApproved)
      <div class="alert alert-success mb-3">
        <small>✅ Perpanjangan deadline Anda telah disetujui. Silakan lanjutkan pekerjaan.</small>
      </div>
    @endif
    
    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-3" id="designerTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="design-upload-tab" data-bs-toggle="tab" data-bs-target="#design-upload" type="button" role="tab" {{ !$canWork ? 'disabled' : '' }}>
          🖼️ Upload Design
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="review-tab" data-bs-toggle="tab" data-bs-target="#review" type="button" role="tab" {{ !$canWork ? 'disabled' : '' }}>
          ✅ Request Review
        </button>
      </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="designerTabsContent">
      
      <!-- Upload Design Tab -->
      <div class="tab-pane fade show active" id="design-upload" role="tabpanel">
        <form method="POST" action="{{ route('designer.tasks.uploadDesign', $card) }}" enctype="multipart/form-data">
          @csrf
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Design File</label>
              <input type="file" name="design_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.svg,.ai,.psd,.fig" required>
              <small class="text-muted">PDF, JPG, PNG, SVG, AI, PSD, FIG (Max 10MB)</small>
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Final UI mockup for login screen"></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-success">Upload Design</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Request Review Tab -->
      <div class="tab-pane fade" id="review" role="tabpanel">
        <form method="POST" action="{{ route('designer.tasks.requestReview', $card) }}">
          @csrf
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Catatan untuk Reviewer</label>
              <textarea name="review_note" class="form-control" rows="4" placeholder="Design sudah sesuai dengan brief client..."></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary">Request Review</button>
              <small class="text-muted d-block mt-2">Status otomatis berubah ke REVIEW</small>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>
@endif

<!-- Comments Section (jika ada) -->
<div class="detail-card">
  <h5 class="mb-3">💬 Comments ({{ $card->comments->count() }})</h5>
  
  @if($card->comments && $card->comments->count() > 0)
    <div class="comments-list mb-3" style="max-height: 400px; overflow-y: auto;">
      @foreach($card->comments->sortByDesc('created_at') as $comment)
        <div class="border-bottom pb-3 mb-3">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <strong>{{ $comment->user->fullname ?? $comment->user->username ?? 'User' }}</strong>
              <span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">{{ ucfirst($comment->user->role) }}</span>
            </div>
            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
          </div>
          <p class="mb-0">{{ $comment->comment_text }}</p>
          @if($comment->attachment)
            <div class="mt-2 p-2 bg-light border rounded">
              <a href="{{ asset('storage/' . $comment->attachment) }}" target="_blank" class="btn btn-sm btn-primary">
                <i class="bi bi-paperclip"></i> 📎 View Attachment
              </a>
              <small class="text-muted ms-2 d-block d-sm-inline mt-2 mt-sm-0">
                <i class="bi bi-file-earmark"></i> {{ basename($comment->attachment) }}
              </small>
            </div>
          @endif
        </div>
      @endforeach
    </div>
  @else
    <div class="alert alert-light mb-3">
      <small class="text-muted">Belum ada komentar. Jadilah yang pertama berkomentar!</small>
    </div>
  @endif
  
  <!-- Add Comment Form -->
  <div class="border rounded p-3 bg-light">
    <form method="POST" action="{{ route('member.cards.comment', $card) }}">
      @csrf
      <div class="mb-2">
        <label class="form-label small fw-semibold">Tambah Komentar</label>
        <textarea name="comment_text" class="form-control" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
      </div>
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-send"></i> Kirim Komentar
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Subtasks Section -->
<div class="detail-card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">✓ Subtasks ({{ $card->subtasks->count() }})</h5>
    <button type="button" class="btn btn-sm btn-primary" onclick="showAddSubtaskForm()">
      <i class="bi bi-plus-circle"></i> Tambah Subtask
    </button>
  </div>

  <!-- Add Subtask Form (hidden by default) -->
  <div id="addSubtaskForm" class="border rounded p-3 bg-light mb-3" style="display: none;">
    <form onsubmit="addSubtask(event)">
      <div class="mb-2">
        <label class="form-label small fw-semibold">Judul Subtask <span class="text-danger">*</span></label>
        <input type="text" name="subtask_title" class="form-control form-control-sm" placeholder="Contoh: Create login form" required>
      </div>
      <div class="mb-2">
        <label class="form-label small fw-semibold">Deskripsi</label>
        <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Deskripsi subtask (opsional)"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Estimasi Jam</label>
        <input type="number" name="estimated_hours" class="form-control form-control-sm" step="0.5" min="0" placeholder="Contoh: 2.5">
      </div>
      <div class="d-flex gap-2 justify-content-end">
        <button type="button" class="btn btn-sm btn-secondary" onclick="hideAddSubtaskForm()">Batal</button>
        <button type="submit" class="btn btn-sm btn-primary">
          <i class="bi bi-check-circle"></i> Tambah
        </button>
      </div>
    </form>
  </div>

  <!-- Subtasks List -->
  @if($card->subtasks && $card->subtasks->count() > 0)
    <div class="list-group">
      @foreach($card->subtasks as $subtask)
        <div class="list-group-item" id="subtask{{ $subtask->id }}">
          <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2 mb-1">
                <select class="form-select form-select-sm" style="width: auto;" onchange="updateSubtaskStatus({{ $subtask->id }}, this.value)">
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
              @if($subtask->estimated_hours)
                <small class="text-muted">⏱️ {{ $subtask->estimated_hours }} jam</small>
              @endif
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSubtask({{ $subtask->id }})" title="Hapus subtask">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="alert alert-light">
      <small class="text-muted">Belum ada subtask. Klik tombol "Tambah Subtask" untuk menambahkan.</small>
    </div>
  @endif
</div>

<!-- Extension Request Modal -->
<div class="modal fade" id="extensionModal" tabindex="-1" aria-labelledby="extensionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="extensionModalLabel">📝 Ajukan Perpanjangan Deadline</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('member.cards.request-extension', $card) }}">
        @csrf
        <div class="modal-body">
          <div class="alert alert-info">
            <small><strong>ℹ️ Informasi:</strong><br>
            Tugas ini sudah melewati deadline. Untuk melanjutkan pekerjaan, Anda perlu persetujuan dari Team Lead.</small>
          </div>
          
          <div class="mb-3">
            <label for="extension_reason" class="form-label">Alasan Perpanjangan <span class="text-danger">*</span></label>
            <textarea class="form-control" id="extension_reason" name="extension_reason" rows="4" required placeholder="Jelaskan mengapa Anda memerlukan perpanjangan deadline..."></textarea>
            <small class="text-muted">Jelaskan kendala atau alasan yang menyebabkan keterlambatan.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">📤 Kirim Permohonan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Subtask Management Functions

function showAddSubtaskForm() {
  document.getElementById('addSubtaskForm').style.display = 'block';
}

function hideAddSubtaskForm() {
  const form = document.getElementById('addSubtaskForm');
  form.style.display = 'none';
  form.querySelector('form').reset();
}

function addSubtask(event) {
  event.preventDefault();
  
  const form = event.target;
  const formData = new FormData(form);
  
  const data = {
    subtask_title: formData.get('subtask_title'),
    description: formData.get('description'),
    estimated_hours: formData.get('estimated_hours') || null
  };
  
  fetch('{{ route("member.cards.subtask.add", $card->id) }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Reload page to show new subtask
      location.reload();
    } else {
      alert('Gagal menambahkan subtask: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat menambahkan subtask');
  });
}

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
      
      // Update the visual state of the subtask title
      const subtaskItem = document.getElementById('subtask' + subtaskId);
      const titleElement = subtaskItem.querySelector('strong');
      
      if (newStatus === 'done') {
        titleElement.classList.add('text-decoration-line-through', 'text-muted');
      } else {
        titleElement.classList.remove('text-decoration-line-through', 'text-muted');
      }
      
      // Show success notification
      showNotification('Status subtask berhasil diubah', 'success');
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

function showNotification(message, type) {
  // Simple notification (could be enhanced with toast library)
  const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
  const notification = document.createElement('div');
  notification.className = `alert ${alertClass} position-fixed top-0 end-0 m-3`;
  notification.style.zIndex = '9999';
  notification.textContent = message;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.remove();
  }, 3000);
}
</script>

@endsection
