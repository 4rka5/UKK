@extends('layouts.lead')

@section('title', 'Edit Project')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-pencil"></i> Edit Project</h1>
        <p class="text-muted mb-0">Perbarui informasi project</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Form Edit Project</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lead.projects.update', $project) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="project_name" class="form-label">Nama Project <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('project_name') is-invalid @enderror" 
                                   id="project_name" 
                                   name="project_name" 
                                   value="{{ old('project_name', $project->project_name) }}" 
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
                                      required>{{ old('description', $project->description) }}</textarea>
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
                                   value="{{ old('deadline', $project->deadline->format('Y-m-d')) }}" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   required>
                            @error('deadline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Deadline harus lebih dari hari ini</small>
                        </div>

                        @if($project->status === 'rejected' && $project->rejection_reason)
                            <div class="alert alert-warning">
                                <strong><i class="bi bi-exclamation-triangle"></i> Alasan Penolakan:</strong>
                                <p class="mb-0 mt-2">{{ $project->rejection_reason }}</p>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Status:</strong> 
                            @if($project->status === 'draft')
                                <span class="badge bg-secondary">Draft</span> - Project belum diajukan
                            @elseif($project->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span> - Perbaiki dan ajukan kembali
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Project
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
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-info-circle"></i> Informasi</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($project->status === 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @elseif($project->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dibuat</td>
                            <td>{{ $project->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Diupdate</td>
                            <td>{{ $project->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
