# Fitur Auto Update Status Owner Project

## Deskripsi
Ketika admin membuat project baru atau mengubah owner project, sistem akan otomatis mengubah status user yang menjadi owner menjadi **"idle"**.

## Alasan Fitur Ini
- Saat user ditunjuk sebagai owner project baru, status mereka diubah menjadi "idle" untuk menandakan bahwa mereka siap memulai project
- Ini memastikan tracking status user tetap akurat sesuai dengan assignment project mereka

## Implementasi

### File yang Diubah
**`app/Http/Controllers/Admin/ProjectController.php`**

### Method `store()` - Membuat Project Baru
```php
public function store(Request $request)
{
    $data = $request->validate([
        'project_name' => ['required','string','max:150'],
        'description'  => ['nullable','string'],
        'deadline'     => ['nullable','date'],
        'created_by'   => ['nullable','exists:users,id'],
    ]);
    if (empty($data['created_by'])) $data['created_by'] = auth()->id();
    
    // Buat project baru
    $project = Project::create($data);
    
    // Ubah status owner project menjadi 'idle' otomatis
    $owner = User::find($data['created_by']);
    if ($owner) {
        $owner->update(['status' => 'idle']);
    }
    
    return redirect()->route('admin.projects.index')
        ->with('status','Project dibuat dan status owner diubah menjadi idle.');
}
```

### Method `update()` - Update Project
```php
public function update(Request $request, Project $project)
{
    $data = $request->validate([
        'project_name' => ['required','string','max:150'],
        'description'  => ['nullable','string'],
        'deadline'     => ['nullable','date'],
        'created_by'   => ['nullable','exists:users,id'],
    ]);
    
    // Jika owner berubah, ubah status owner baru menjadi 'idle'
    if (isset($data['created_by']) && $data['created_by'] != $project->created_by) {
        $newOwner = User::find($data['created_by']);
        if ($newOwner) {
            $newOwner->update(['status' => 'idle']);
        }
    }
    
    $project->update($data);
    return redirect()->route('admin.projects.index')
        ->with('status','Project diperbarui.');
}
```

## Cara Kerja

### Skenario 1: Admin Membuat Project Baru
1. Admin mengisi form create project
2. Admin memilih user sebagai owner project (atau kosongkan untuk owner = admin sendiri)
3. Sistem membuat project baru
4. **Sistem otomatis mengubah status owner menjadi "idle"**
5. Admin melihat pesan: "Project dibuat dan status owner diubah menjadi idle."

### Skenario 2: Admin Mengubah Owner Project
1. Admin mengedit project yang sudah ada
2. Admin mengganti owner project ke user lain
3. Sistem mendeteksi perubahan owner
4. **Sistem otomatis mengubah status owner BARU menjadi "idle"**
5. Owner lama tidak terpengaruh

## Status User yang Tersedia

Berdasarkan database schema, status user memiliki nilai:
- `idle` - User belum bekerja / siap menerima tugas
- `working` - User sedang bekerja pada tugas
- `active` - User aktif dalam project

## Testing

### Manual Test
1. Login sebagai admin
2. Buat project baru dengan owner tertentu
3. Cek database: status owner harus "idle"

```sql
-- Cek status user tertentu
SELECT id, username, fullname, status FROM users WHERE id = [owner_id];
```

### Test di Browser
1. Login: `http://127.0.0.1/UKK/public/login`
   - Username: `admin`
   - Password: (sesuai database)
2. Navigate to: Admin > Projects > Create New
3. Pilih owner project
4. Submit form
5. Verify: Flash message muncul dan owner status berubah

## Notes

- Fitur ini hanya berlaku untuk **Admin** (role: admin)
- Route yang terpengaruh:
  - `POST /admin/projects` (create)
  - `PUT/PATCH /admin/projects/{project}` (update)
- Status owner lama **tidak terpengaruh** saat owner diganti
- Jika `created_by` kosong, owner akan diset ke admin yang sedang login

---
**Created**: November 13, 2025  
**Last Updated**: November 13, 2025
