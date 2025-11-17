<style>
.alert-modern {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    animation: slideInDown 0.3s ease-out;
}
@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.alert-modern .alert-icon {
    font-size: 1.5rem;
    margin-right: 0.75rem;
}
@media (max-width: 576px) {
    .alert-modern {
        font-size: 0.9rem;
    }
}
</style>

@if(session('success'))
  <div class="alert alert-success alert-modern alert-dismissible fade show d-flex align-items-center" role="alert">
    <span class="alert-icon">✅</span>
    <div class="flex-grow-1">{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if(session('status'))
  <div class="alert alert-success alert-modern alert-dismissible fade show d-flex align-items-center" role="alert">
    <span class="alert-icon">✅</span>
    <div class="flex-grow-1">{{ session('status') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger alert-modern alert-dismissible fade show d-flex align-items-center" role="alert">
    <span class="alert-icon">❌</span>
    <div class="flex-grow-1">{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if(session('warning'))
  <div class="alert alert-warning alert-modern alert-dismissible fade show d-flex align-items-center" role="alert">
    <span class="alert-icon">⚠️</span>
    <div class="flex-grow-1">{{ session('warning') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if(session('info'))
  <div class="alert alert-info alert-modern alert-dismissible fade show d-flex align-items-center" role="alert">
    <span class="alert-icon">ℹ️</span>
    <div class="flex-grow-1">{{ session('info') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-start">
      <span class="alert-icon">❌</span>
      <div class="flex-grow-1">
        <strong class="d-block mb-2">Terdapat kesalahan:</strong>
        <ul class="mb-0 ps-3">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<script>
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-modern');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
