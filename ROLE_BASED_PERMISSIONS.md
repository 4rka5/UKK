# Role-Based Access Control Implementation

## Overview
Implementasi sistem role-based permissions sesuai dengan tabel yang telah ditentukan untuk 4 role: **Project Admin**, **Team Lead**, **Developer**, dan **Designer**.

---

## Role Permissions Matrix

### 1. **Project Admin**
**Hak Akses:**
- ✅ Buat/hapus proyek
- ✅ Kelola anggota
- ✅ Akses semua data
- ✅ Edit semua tugas
- ✅ Monitoring keseluruhan
- ✅ Generate laporan

**Tugas Utama:**
- Setup awal proyek
- Manajemen tim
- Monitoring keseluruhan
- Generate laporan

**Batasan:**
- ❌ Tidak bisa hapus data historis

---

### 2. **Team Lead**
**Hak Akses:**
- ✅ Assign tugas
- ✅ Set priority
- ✅ Update status
- ✅ Lihat semua progress
- ✅ Distribusi tugas
- ✅ Koordinasi tim
- ✅ Review hasil kerja
- ✅ Solve blocker

**Batasan:**
- ❌ Tidak bisa hapus proyek
- ❌ Tidak bisa remove anggota

---

### 3. **Developer**
**Hak Akses:**
- ✅ Update tugas sendiri
- ✅ Time tracking
- ✅ Komentar
- ✅ Upload file (opsional)
- ✅ Kerjakan tugas
- ✅ Update progress
- ✅ Dokumentasi kerja
- ✅ Kolaborasi

**Batasan:**
- ❌ Hanya 1 tugas aktif
- ❌ Tidak bisa assign tugas
- ❌ Tidak bisa edit tugas orang lain

---

### 4. **Designer**
**Hak Akses:**
- ✅ Sama seperti Developer
- ✅ Khusus tugas desain
- ✅ Desain UI/UX
- ✅ Design preparation
- ✅ Design review

**Batasan:**
- ❌ Hanya akses tugas terkait desain

---

## Implementation Details

### Middleware Created

#### 1. CheckRole Middleware
**File:** `app/Http/Middleware/CheckRole.php`
```php
// Middleware untuk validasi role user
// Usage: middleware('role:admin,team_lead')
```

#### 2. CheckProjectMember Middleware
**File:** `app/Http/Middleware/CheckProjectMember.php`
```php
// Middleware untuk validasi keanggotaan project
// Memastikan user adalah member dari project yang diakses
```

**Registration:** Kedua middleware sudah didaftarkan di `bootstrap/app.php`

---

### Controllers Updated

#### 1. Admin\ProjectController
**Methods Added:**
- `members(Project $project)` - Kelola anggota project
- `addMember(Request $request, Project $project)` - Tambah anggota
- `removeMember(Project $project, $memberId)` - Hapus anggota
- `generateReport(Project $project)` - Generate laporan project

**Features:**
- CRUD project lengkap
- Manajemen anggota dengan validasi
- Generate report dengan statistik
- Auto update status owner ke 'idle' saat project dibuat

---

#### 2. Lead\CardController
**Updated Methods:**
- `index()` - Filter berdasarkan project membership (team_lead)
- `create()` - Hanya boards dari project yang user adalah team lead
- `edit()` - Authorization via project membership
- `authorizeCard()` - Validasi team lead membership

**Features:**
- Assign tugas ke member
- Set priority (low, medium, high)
- Update status tugas
- Move cards antar status
- Review hasil kerja

---

#### 3. Member\CardController
**Updated Methods:**
- `show()` - Hanya tugas yang di-assign ke user
- `updateStatus()` - Validasi 1 tugas aktif

**Validation:**
- User hanya bisa akses tugas yang di-assign ke mereka
- Validasi maksimal 1 tugas dengan status 'in_progress'
- Tidak bisa edit tugas orang lain

---

#### 4. Member\DeveloperController (NEW)
**File:** `app/Http/Controllers/Member/DeveloperController.php`

**Methods:**
- `index()` - Dashboard developer dengan daftar tugas
- `show(Card $card)` - Detail tugas
- `updateProgress()` - Update progress dengan persentase
- `uploadFile()` - Upload file attachment
- `reportBlocker()` - Laporkan blocker dengan priority auto-high
- `workDocumentation()` - Dokumentasi kerja

**Features:**
- Time tracking via progress updates
- File upload (max 10MB)
- Blocker reporting dengan tag
- Work documentation
- Kolaborasi via comments

---

#### 5. Member\DesignerController (NEW)
**File:** `app/Http/Controllers/Member/DesignerController.php`

**Methods:**
- `index()` - Dashboard designer (filter tugas desain)
- `show(Card $card)` - Detail tugas desain
- `uploadDesign()` - Upload design file (PDF, JPG, PNG, SVG, AI, PSD, FIG)
- `requestReview()` - Request design review

**Features:**
- Auto-filter tugas desain (kata kunci: desain, design, UI, UX)
- Upload design file dengan validasi format
- Design review request
- Khusus untuk tugas desain

---

## Routes Structure

### Admin Routes
```
GET     /admin                              - Dashboard
GET     /admin/projects                     - List projects
GET     /admin/projects/create              - Form create project
POST    /admin/projects                     - Store project
GET     /admin/projects/{id}/edit           - Form edit project
PUT     /admin/projects/{id}                - Update project
DELETE  /admin/projects/{id}                - Delete project
GET     /admin/projects/{id}/members        - Kelola anggota
POST    /admin/projects/{id}/members        - Tambah anggota
DELETE  /admin/projects/{id}/members/{id}   - Hapus anggota
GET     /admin/projects/{id}/report         - Generate report
```

### Team Lead Routes
```
GET     /lead                               - Dashboard
GET     /lead/boards                        - List boards
POST    /lead/boards                        - Create board
GET     /lead/cards                         - List cards
POST    /lead/cards                         - Create card (assign tugas)
PATCH   /lead/cards/{id}                    - Update card
DELETE  /lead/cards/{id}                    - Delete card
PATCH   /lead/cards/{id}/move               - Move card (update status)
```

### Developer Routes
```
GET     /developer                          - Dashboard
GET     /developer/tasks/{id}               - Detail tugas
POST    /developer/tasks/{id}/progress      - Update progress
POST    /developer/tasks/{id}/upload        - Upload file
POST    /developer/tasks/{id}/blocker       - Report blocker
POST    /developer/tasks/{id}/documentation - Work documentation
```

### Designer Routes
```
GET     /designer                           - Dashboard
GET     /designer/tasks/{id}                - Detail tugas desain
POST    /designer/tasks/{id}/upload-design  - Upload design file
POST    /designer/tasks/{id}/request-review - Request review
```

---

## Database Changes

### No New Tables
Menggunakan tabel yang sudah ada:
- `users` - dengan kolom `role` dan `status`
- `projects` 
- `project_members` - untuk membership dan role di project
- `boards`
- `cards` (management_project_cards)
- `card_assignments` - untuk assign tugas
- `comments` - untuk komentar, attachment, dokumentasi

---

## Validation Rules

### 1. Project Member Validation
```php
// User tidak bisa di-assign tugas jika sudah menjadi project member
if (ProjectMember::where('project_id', $projectId)
    ->where('user_id', $userId)->exists()) {
    return error('User sudah bergabung di project');
}
```

### 2. Active Task Validation (Developer/Designer)
```php
// Maksimal 1 tugas aktif (status: in_progress)
if (moving to 'in_progress') {
    $activeTask = Card::whereHas('assignees', ...)
        ->where('status', 'in_progress')
        ->exists();
    if ($activeTask) return error('Sudah ada tugas aktif');
}
```

### 3. Assignment Access Validation
```php
// Hanya bisa akses tugas yang di-assign
$isAssigned = $card->assignees()
    ->where('users.id', $userId)->exists();
if (!$isAssigned) abort(403);
```

---

## Usage Examples

### Admin: Tambah Anggota ke Project
```php
POST /admin/projects/1/members
{
    "user_id": 5,
    "role": "developer"  // team_lead, developer, designer
}
```

### Team Lead: Assign Tugas
```php
POST /lead/cards
{
    "board_id": 1,
    "card_title": "Implement Login Feature",
    "description": "...",
    "status": "todo",
    "priority": "high",
    "assigned_to": 5  // user_id developer
}
```

### Developer: Update Progress
```php
POST /developer/tasks/10/progress
{
    "progress_percentage": 75,
    "progress_note": "Login form completed, testing in progress",
    "hours_spent": 2.5
}
```

### Developer: Report Blocker
```php
POST /developer/tasks/10/blocker
{
    "blocker_description": "API endpoint not ready, waiting for backend team"
}
```

### Designer: Upload Design
```php
POST /designer/tasks/5/upload-design
{
    "design_file": file (PDF/JPG/PNG/SVG/AI/PSD/FIG),
    "description": "Final UI mockup for login screen"
}
```

---

## Testing Checklist

### Admin
- [ ] Create project dengan auto status 'idle' untuk owner
- [ ] Add member ke project dengan role berbeda
- [ ] Remove member dari project
- [ ] Generate report project
- [ ] Edit semua tugas di system

### Team Lead
- [ ] Create board di project yang menjadi team lead
- [ ] Assign tugas ke developer/designer
- [ ] Set priority tugas
- [ ] Update status tugas
- [ ] Review hasil kerja member
- [ ] Tidak bisa hapus project
- [ ] Tidak bisa remove anggota

### Developer
- [ ] Lihat hanya tugas yang di-assign
- [ ] Update status tugas sendiri
- [ ] Validasi maksimal 1 tugas aktif
- [ ] Update progress dengan persentase
- [ ] Upload file attachment
- [ ] Report blocker
- [ ] Work documentation
- [ ] Tidak bisa edit tugas orang lain

### Designer
- [ ] Lihat hanya tugas desain yang di-assign
- [ ] Upload design file (berbagai format)
- [ ] Request design review
- [ ] Validasi maksimal 1 tugas aktif
- [ ] Tidak bisa akses tugas non-desain

---

## Security Features

1. **Role-based Middleware**: Akses route dibatasi sesuai role
2. **Project Membership Validation**: User harus member dari project
3. **Assignment Validation**: User hanya bisa akses tugas yang di-assign
4. **Active Task Limit**: Developer/Designer maksimal 1 tugas aktif
5. **File Upload Validation**: Format dan ukuran file dibatasi
6. **Authorization Methods**: Setiap action divalidasi kepemilikan

---

## Next Steps

1. Buat view untuk setiap controller:
   - `resources/views/admin/projects/members.blade.php`
   - `resources/views/admin/projects/report.blade.php`
   - `resources/views/developer/` (dashboard, show)
   - `resources/views/designer/` (dashboard, show)

2. Test semua endpoint dengan role berbeda

3. Clear cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

4. Verify routes:
```bash
php artisan route:list
```

---

## Summary

✅ **Middleware**: CheckRole, CheckProjectMember  
✅ **Controllers**: Admin, TeamLead, Developer, Designer  
✅ **Routes**: Semua routes dengan middleware yang sesuai  
✅ **Validations**: Project member, active task, assignment access  
✅ **Features**: Sesuai tabel permissions  
✅ **Security**: Role-based access control lengkap  

Sistem role-based permissions sudah diimplementasikan lengkap sesuai dengan tabel yang diberikan!
