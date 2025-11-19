# Dokumentasi Fitur Pengajuan Project

## Overview
Fitur ini menerapkan alur approval project dari Team Lead ke Admin, mirip dengan alur review card dari Member ke Team Lead.

## Alur Workflow

### 1. Team Lead - Membuat Project

**Langkah:**
1. Login sebagai Team Lead
2. Buka menu "Kelola Project" di sidebar (route: `/lead/projects`)
3. Klik tombol "Buat Project Baru"
4. Isi form:
   - Nama Project (required)
   - Deskripsi (required)
   - Deadline (required, harus > hari ini)
5. Klik "Simpan sebagai Draft"

**Hasil:**
- Project disimpan dengan status `draft`
- Team Lead bisa edit/hapus project
- Project belum terlihat oleh admin

---

### 2. Team Lead - Mengajukan Project untuk Approval

**Langkah:**
1. Buka halaman "Kelola Project"
2. Pilih project dengan status `draft` atau `rejected`
3. Klik tombol "Ajukan untuk Approval" (icon send)
4. Konfirmasi pengajuan

**Hasil:**
- Status project berubah menjadi `pending`
- Notifikasi dikirim ke semua admin
- Team Lead **tidak bisa** edit/hapus project yang pending
- Project muncul di list admin untuk direview

---

### 3. Admin - Review dan Approve/Reject

**Langkah:**
1. Login sebagai Admin
2. Buka menu "Kelola Project" (route: `/admin/projects`)
3. Filter by status: `pending` untuk melihat project yang menunggu review
4. Klik "Detail/Review" pada project yang pending
5. Lihat informasi lengkap project
6. Pilih aksi:
   
   **APPROVE:**
   - Klik tombol "Approve Project"
   - Konfirmasi approval
   
   **REJECT:**
   - Klik tombol "Reject Project"
   - Isi alasan penolakan (wajib)
   - Konfirmasi rejection

**Hasil Approve:**
- Status project berubah menjadi `approved`
- Field `reviewed_by` diisi dengan ID admin
- Field `reviewed_at` diisi timestamp saat ini
- Notifikasi "Project Disetujui" dikirim ke Team Lead pembuat
- Team Lead bisa mulai membuat boards dan cards

**Hasil Reject:**
- Status project berubah menjadi `rejected`
- Field `reviewed_by` diisi dengan ID admin
- Field `reviewed_at` diisi timestamp saat ini
- Field `rejection_reason` diisi dengan alasan penolakan
- Notifikasi "Project Ditolak" dikirim ke Team Lead dengan alasan
- Team Lead bisa melihat alasan penolakan
- Team Lead bisa edit project dan ajukan ulang

---

### 4. Team Lead - Memperbaiki Project yang Rejected

**Langkah:**
1. Terima notifikasi "Project Ditolak"
2. Buka detail project yang rejected
3. Lihat alasan penolakan
4. Klik tombol "Edit Project"
5. Perbaiki sesuai feedback admin
6. Simpan perubahan
7. Ajukan ulang untuk approval

**Hasil:**
- Project kembali ke status `pending`
- Field `reviewed_by`, `reviewed_at`, `rejection_reason` di-reset
- Notifikasi baru dikirim ke admin
- Admin bisa review ulang

---

## Database Schema

### Migration: `add_status_and_approval_to_projects`

Kolom baru ditambahkan ke tabel `projects`:

```php
$table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'active', 'completed'])->default('draft');
$table->unsignedBigInteger('reviewed_by')->nullable();
$table->timestamp('reviewed_at')->nullable();
$table->text('rejection_reason')->nullable();
```

**Status Enum:**
- `draft`: Project baru dibuat, belum diajukan
- `pending`: Menunggu review admin
- `approved`: Disetujui admin, siap digunakan
- `rejected`: Ditolak admin, perlu perbaikan
- `active`: (future) Project sedang berjalan
- `completed`: (future) Project selesai

---

## Routes

### Team Lead Routes (`/lead/projects`)
```php
Route::middleware(['auth','role:team_lead'])->prefix('lead')->name('lead.')->group(function () {
    Route::resource('projects', LeadProjectController::class)->except(['show']);
    Route::get('projects/{project}/detail', [LeadProjectController::class, 'show'])->name('projects.show');
    Route::post('projects/{project}/submit', [LeadProjectController::class, 'submitForApproval'])->name('projects.submit');
});
```

**Available Routes:**
- `GET /lead/projects` - Index (list projects)
- `GET /lead/projects/create` - Form buat project
- `POST /lead/projects` - Store project (status: draft)
- `GET /lead/projects/{id}/edit` - Form edit project (hanya draft/rejected)
- `PUT /lead/projects/{id}` - Update project (hanya draft/rejected)
- `DELETE /lead/projects/{id}` - Delete project (hanya draft/rejected)
- `GET /lead/projects/{id}/detail` - Show project detail
- `POST /lead/projects/{id}/submit` - Submit untuk approval

### Admin Routes (`/admin/projects`)
```php
Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('projects', ProjectController::class)->except(['show']);
    Route::get('projects/{project}/detail', [ProjectController::class, 'detail'])->name('projects.detail');
    Route::post('projects/{project}/approve', [ProjectController::class, 'approve'])->name('projects.approve');
    Route::post('projects/{project}/reject', [ProjectController::class, 'reject'])->name('projects.reject');
});
```

**Available Routes:**
- `GET /admin/projects` - Index (list all projects dengan filter status)
- `GET /admin/projects/{id}/detail` - Detail untuk review
- `POST /admin/projects/{id}/approve` - Approve project
- `POST /admin/projects/{id}/reject` - Reject project

---

## Controllers

### Lead\ProjectController

**Methods:**
- `index()`: List projects milik team lead dengan statistik
- `create()`: Form buat project baru
- `store()`: Simpan project baru (status: draft)
- `edit()`: Form edit project (validasi: hanya draft/rejected)
- `update()`: Update project (validasi: hanya draft/rejected)
- `destroy()`: Hapus project (validasi: hanya draft/rejected)
- `show()`: Detail project
- `submitForApproval()`: Ajukan project untuk approval (draft/rejected → pending)

### Admin\ProjectController

**New Methods:**
- `detail()`: Show project detail untuk review
- `approve()`: Approve project (pending → approved)
- `reject()`: Reject project dengan alasan (pending → rejected)

**Updated Methods:**
- `index()`: Tambah filter by status, tambah statistik pending/approved/rejected

---

## Views

### Team Lead Views (`resources/views/lead/projects/`)

1. **index.blade.php**: List projects dengan statistics cards
   - Filter by status: draft, pending, approved, rejected
   - Tabel dengan kolom: nama, deadline, status, reviewer, reviewed_at
   - Tombol aksi: Detail, Edit (draft/rejected), Submit (draft/rejected), Delete (draft/rejected)

2. **create.blade.php**: Form buat project baru
   - Input: project_name, description, deadline
   - Tips dan informasi workflow

3. **edit.blade.php**: Form edit project
   - Hanya bisa edit jika status draft atau rejected
   - Tampilkan alasan penolakan jika status rejected

4. **show.blade.php**: Detail project
   - Info lengkap project
   - Timeline status
   - Tombol aksi: Edit, Submit, Delete (tergantung status)

### Admin Views (`resources/views/admin/projects/`)

1. **detail.blade.php**: Review project
   - Info lengkap project
   - Info pembuat (team lead)
   - Timeline
   - Tombol Approve / Reject (hanya jika pending)
   - Modal reject dengan form alasan penolakan

2. **index.blade.php**: (perlu update)
   - Tambah filter by status
   - Tambah statistics cards (total, pending, approved, rejected)
   - Tambah kolom status di tabel
   - Tambah tombol "Review" untuk project pending

---

## Notifications

### Notifikasi yang Dikirim

**1. Project Submitted (Team Lead → Admin)**
```php
'type' => 'project_submitted',
'title' => 'Project Baru Menunggu Approval',
'message' => '{team_lead_name} mengajukan project "{project_name}" untuk approval.',
'related_type' => 'Project',
'related_id' => $project->id
```

**2. Project Approved (Admin → Team Lead)**
```php
'type' => 'project_approved',
'title' => 'Project Disetujui',
'message' => 'Project "{project_name}" telah disetujui oleh admin.',
'related_type' => 'Project',
'related_id' => $project->id
```

**3. Project Rejected (Admin → Team Lead)**
```php
'type' => 'project_rejected',
'title' => 'Project Ditolak',
'message' => 'Project "{project_name}" ditolak. Alasan: {rejection_reason}',
'related_type' => 'Project',
'related_id' => $project->id
```

---

## Business Rules

### Team Lead
1. ✅ Bisa membuat project (status: draft)
2. ✅ Bisa edit project hanya jika status `draft` atau `rejected`
3. ✅ Bisa hapus project hanya jika status `draft` atau `rejected`
4. ✅ Bisa ajukan project hanya jika status `draft` atau `rejected`
5. ❌ TIDAK bisa edit/hapus project jika status `pending` atau `approved`
6. ✅ Hanya melihat project yang dibuat sendiri

### Admin
1. ✅ Bisa melihat semua project (dari semua team lead)
2. ✅ Bisa approve project jika status `pending`
3. ✅ Bisa reject project jika status `pending`
4. ❌ HARUS isi alasan penolakan saat reject
5. ✅ Bisa filter project by status
6. ✅ Masih bisa CRUD project secara manual (fitur lama tetap ada)

---

## Model Methods

### Project Model

**New Methods:**
```php
public function reviewer() {
    return $this->belongsTo(User::class, 'reviewed_by');
}

public function canSubmitForApproval() {
    return in_array($this->status, ['draft', 'rejected']);
}

public function isPending() {
    return $this->status === 'pending';
}

public function isApproved() {
    return $this->status === 'approved';
}
```

**Updated Fillable:**
```php
protected $fillable = [
    'project_name',
    'description',
    'deadline',
    'created_by',
    'assigned_to',
    'status',
    'reviewed_by',
    'reviewed_at',
    'rejection_reason'
];
```

**Casts:**
```php
protected $casts = [
    'deadline' => 'date',
    'reviewed_at' => 'datetime',
];
```

---

## Cara Menjalankan

### 1. Jalankan Migration

```bash
# Start MySQL di Laragon
# Kemudian jalankan:
php artisan migrate
```

### 2. Test Workflow

**A. Sebagai Team Lead:**
```bash
# Login dengan user role team_lead
# Akses: /lead/projects
```

1. Buat project baru
2. Edit project (optional)
3. Ajukan untuk approval
4. Tunggu review admin
5. Cek notifikasi

**B. Sebagai Admin:**
```bash
# Login dengan user role admin
# Akses: /admin/projects
```

1. Filter status: pending
2. Klik "Detail/Review" pada project
3. Approve atau Reject dengan alasan
4. Cek notifikasi terkirim

**C. Sebagai Team Lead (after rejected):**
1. Cek notifikasi rejection
2. Lihat alasan penolakan di detail project
3. Edit project
4. Ajukan ulang

---

## UI/UX Highlights

### Badges Status
- **Draft**: `badge bg-secondary` - Abu-abu
- **Pending**: `badge bg-warning` - Kuning/Orange
- **Approved**: `badge bg-success` - Hijau
- **Rejected**: `badge bg-danger` - Merah

### Icons
- Draft: `bi-file-earmark`
- Pending: `bi-clock-history`
- Approved: `bi-check-circle`
- Rejected: `bi-x-circle`
- Submit: `bi-send`
- Review: `bi-check2-square`

### Statistics Cards
Menampilkan 5 kartu statistik:
1. Total Project
2. Draft
3. Pending
4. Approved
5. Rejected

### Timeline Component
Menampilkan kronologi status project dengan visual timeline.

---

## Future Enhancements

1. **Auto-update status `approved` → `active`** saat team lead mulai buat board pertama
2. **Auto-update status `active` → `completed`** saat semua cards done
3. **Deadline reminder** untuk project yang mendekati deadline
4. **Bulk approve/reject** untuk admin
5. **Project revision history** untuk tracking perubahan
6. **Comment/feedback** dari admin saat review (selain rejection reason)

---

## Troubleshooting

### Database Error saat Migration
- Pastikan MySQL Laragon sudah running
- Cek koneksi database di `.env`
- Drop table jika ada conflict: `php artisan migrate:fresh`

### Authorization Error
- Pastikan middleware role sudah benar
- Cek apakah user login dengan role yang sesuai
- Clear cache: `php artisan route:clear`

### View Not Found
- Pastikan folder `resources/views/lead/projects/` sudah dibuat
- Check case-sensitive di nama file
- Clear view cache: `php artisan view:clear`

---

## File Changes Summary

**New Files:**
- Migration: `database/migrations/*_add_status_and_approval_to_projects.php`
- Controller: `app/Http/Controllers/Lead/ProjectController.php`
- Views: `resources/views/lead/projects/*.blade.php`
- View: `resources/views/admin/projects/detail.blade.php`
- Docs: `PROJECT_APPROVAL_WORKFLOW.md`

**Modified Files:**
- Model: `app/Models/Project.php` (add fillable, casts, methods, relations)
- Controller: `app/Http/Controllers/Admin/ProjectController.php` (add approve, reject, detail methods, update index)
- Routes: `routes/web.php` (add lead.projects.*, admin.projects.detail, approve, reject)

---

**Dokumentasi dibuat oleh: GitHub Copilot**  
**Tanggal: 19 November 2025**
