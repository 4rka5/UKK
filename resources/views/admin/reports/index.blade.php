@extends('layouts.admin')
@section('title','Generate Laporan')
@section('adminContent')

<style>
.report-card { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
.report-card h5 { color: #1f2937; font-weight: 600; margin-bottom: 1rem; }
.report-type-card { border: 2px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; cursor: pointer; transition: all 0.2s; }
.report-type-card:hover { border-color: #667eea; background: #f9fafb; }
.report-type-card input[type="radio"]:checked + label .report-type-card { border-color: #667eea; background: #ede9fe; }
.report-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="mb-1">📊 Generate Laporan</h3>
    <p class="text-muted mb-0">Buat dan download laporan sistem dalam format PDF atau Excel</p>
  </div>
  <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>

<form method="POST" action="{{ route('admin.reports.generate') }}">
  @csrf
  
  <!-- Report Type Selection -->
  <div class="report-card">
    <h5>1️⃣ Pilih Jenis Laporan</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <input type="radio" name="report_type" value="users" id="report_users" class="d-none" required>
        <label for="report_users" class="w-100 mb-0">
          <div class="report-type-card text-center">
            <div class="report-icon">👥</div>
            <h6 class="fw-semibold mb-2">Laporan User</h6>
            <small class="text-muted">Data seluruh user dengan role dan status</small>
          </div>
        </label>
      </div>
      
      <div class="col-md-6">
        <input type="radio" name="report_type" value="projects" id="report_projects" class="d-none">
        <label for="report_projects" class="w-100 mb-0">
          <div class="report-type-card text-center">
            <div class="report-icon">📁</div>
            <h6 class="fw-semibold mb-2">Laporan Project</h6>
            <small class="text-muted">Data project dengan owner dan member</small>
          </div>
        </label>
      </div>
      
      <div class="col-md-6">
        <input type="radio" name="report_type" value="cards" id="report_cards" class="d-none">
        <label for="report_cards" class="w-100 mb-0">
          <div class="report-type-card text-center">
            <div class="report-icon">📋</div>
            <h6 class="fw-semibold mb-2">Laporan Card/Tugas</h6>
            <small class="text-muted">Data card dengan status dan assignment</small>
          </div>
        </label>
      </div>
      
      <div class="col-md-6">
        <input type="radio" name="report_type" value="summary" id="report_summary" class="d-none">
        <label for="report_summary" class="w-100 mb-0">
          <div class="report-type-card text-center">
            <div class="report-icon">📊</div>
            <h6 class="fw-semibold mb-2">Laporan Summary</h6>
            <small class="text-muted">Ringkasan statistik keseluruhan sistem</small>
          </div>
        </label>
      </div>
    </div>
  </div>
  
  <!-- Date Range (Optional) -->
  <div class="report-card">
    <h5>2️⃣ Periode Waktu (Opsional)</h5>
    <div class="alert alert-info">
      <small><i class="bi bi-info-circle"></i> Pilih periode cepat atau tentukan tanggal manual</small>
    </div>
    
    <!-- Quick Period Selection -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Pilihan Cepat Periode</label>
      <div class="row g-2">
        <div class="col-md-3">
          <button type="button" class="btn btn-outline-primary w-100" onclick="setPeriod('weekly')">
            📅 Minggu Ini
          </button>
        </div>
        <div class="col-md-3">
          <button type="button" class="btn btn-outline-primary w-100" onclick="setPeriod('monthly')">
            📆 Bulan Ini
          </button>
        </div>
        <div class="col-md-3">
          <button type="button" class="btn btn-outline-primary w-100" onclick="setPeriod('yearly')">
            📊 Tahun Ini
          </button>
        </div>
        <div class="col-md-3">
          <button type="button" class="btn btn-outline-secondary w-100" onclick="clearPeriod()">
            🔄 Reset
          </button>
        </div>
      </div>
    </div>
    
    <!-- Manual Date Selection -->
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Tanggal Mulai</label>
        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Tanggal Akhir</label>
        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}">
      </div>
    </div>
  </div>
  
  <!-- Format Selection -->
  <div class="report-card">
    <h5>3️⃣ Format Output</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="format" value="pdf" id="format_pdf" checked required>
          <label class="form-check-label" for="format_pdf">
            <strong>📄 PDF</strong>
            <div class="small text-muted">Format dokumen siap cetak</div>
          </label>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="format" value="excel" id="format_excel">
          <label class="form-check-label" for="format_excel">
            <strong>📊 Excel</strong>
            <div class="small text-muted">Format spreadsheet untuk analisis (Belum tersedia)</div>
          </label>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Submit Button -->
  <div class="text-end">
    <button type="submit" class="btn btn-primary btn-lg">
      <i class="bi bi-download"></i> Generate & Download Laporan
    </button>
  </div>
</form>

<script>
// Add active state to selected report type
document.querySelectorAll('input[name="report_type"]').forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.report-type-card').forEach(card => {
      card.style.borderColor = '#e5e7eb';
      card.style.background = 'white';
    });
    if (this.checked) {
      this.nextElementSibling.querySelector('.report-type-card').style.borderColor = '#667eea';
      this.nextElementSibling.querySelector('.report-type-card').style.background = '#ede9fe';
    }
  });
});

// Set period functions
function setPeriod(type) {
  const today = new Date();
  let startDate, endDate;
  
  switch(type) {
    case 'weekly':
      // Minggu ini (Senin - Minggu)
      const day = today.getDay();
      const diff = today.getDate() - day + (day === 0 ? -6 : 1); // Adjust when day is Sunday
      startDate = new Date(today.setDate(diff));
      endDate = new Date(startDate);
      endDate.setDate(startDate.getDate() + 6);
      break;
      
    case 'monthly':
      // Bulan ini
      startDate = new Date(today.getFullYear(), today.getMonth(), 1);
      endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
      break;
      
    case 'yearly':
      // Tahun ini
      startDate = new Date(today.getFullYear(), 0, 1);
      endDate = new Date(today.getFullYear(), 11, 31);
      break;
  }
  
  document.getElementById('start_date').value = formatDate(startDate);
  document.getElementById('end_date').value = formatDate(endDate);
}

function clearPeriod() {
  document.getElementById('start_date').value = '';
  document.getElementById('end_date').value = '';
}

function formatDate(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
</script>

@endsection
