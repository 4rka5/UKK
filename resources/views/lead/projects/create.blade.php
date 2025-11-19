@extends('layouts.lead')

@section('title', 'Buat Project Baru')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-send-plus"></i> Ajukan Project Baru</h1>
        <p class="text-muted mb-0">Isi form di bawah untuk mengajukan project ke admin</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Form Project</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lead.projects.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="project_name" class="form-label">Nama Project <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('project_name') is-invalid @enderror" 
                                   id="project_name" 
                                   name="project_name" 
                                   value="{{ old('project_name') }}" 
                                   placeholder="Masukkan nama project"
                                   required>
                            @error('project_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="5" 
                                      placeholder="Jelaskan tujuan dan detail project"
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="deadline" class="form-label">Deadline <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('deadline') is-invalid @enderror" 
                                   id="deadline" 
                                   name="deadline" 
                                   value="{{ old('deadline') }}" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   required>
                            @error('deadline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Deadline harus lebih dari hari ini</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Catatan:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Project akan <strong>langsung diajukan</strong> ke admin untuk direview</li>
                                <li>Anda akan menerima notifikasi saat project disetujui atau ditolak</li>
                                <li>Pastikan semua informasi sudah benar sebelum submit</li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Ajukan Project
                            </button>
                            <a href="{{ route('lead.projects.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-lightbulb"></i> Tips</h6>
                    <ul class="small mb-0">
                        <li>Gunakan nama project yang jelas dan deskriptif</li>
                        <li>Jelaskan tujuan dan scope project secara detail</li>
                        <li>Pastikan deadline realistis dan dapat dicapai</li>
                        <li>Setelah disetujui, Anda dapat membuat boards dan cards</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
