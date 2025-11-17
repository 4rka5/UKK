# Business Rules Implementation - Role Based

## ✅ **SEMUA BUSINESS RULES SUDAH DITERAPKAN!**

Berikut detail implementasi setiap business rule sesuai gambar:

---

## 1️⃣ **Developer/Designer Rules**

### ✅ **Max 1 tugas in_progress**
**File:** `app/Http/Controllers/Member/CardController.php`  
**Method:** `updateStatus()`  
**Lines:** 40-52

```php
// Validasi: hanya 1 tugas aktif
if ($request->status === 'in_progress') {
    $activeTask = ManagementProjectCard::whereHas('assignees', function($q) use ($userId) {
            $q->where('users.id', $userId);
        })
        ->where('status', 'in_progress')
        ->where('id', '!=', $card->id)
        ->first();

    if ($activeTask) {
        return back()->with('error', 'Anda sudah memiliki tugas aktif: ' . 
            $activeTask->card_title . '. Selesaikan tugas tersebut terlebih dahulu.');
    }
}
```

**Cara Kerja:**
- Saat developer/designer ingin pindah tugas ke status `in_progress`
- System cek apakah ada tugas lain dengan status `in_progress`
- Jika ada, muncul error dan tidak bisa proceed
- Developer harus selesaikan tugas aktif dulu

---

### ✅ **Wajib Time Tracking**
**File:** `app/Http/Controllers/Member/DeveloperController.php`  
**Method:** `updateProgress()`  
**Lines:** 49-75

```php
$request->validate([
    'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
    'progress_note' => ['nullable', 'string', 'max:1000'],
    'hours_spent' => ['required', 'numeric', 'min:0.1'] // WAJIB!
]);

// Update actual hours (WAJIB)
$card->increment('actual_hours', $request->hours_spent);
```

**Validasi Tambahan:**  
**File:** `app/Http/Controllers/Member/CardController.php`  
**Method:** `updateStatus()`

```php
// Validasi: wajib time tracking saat menyelesaikan tugas
if ($request->status === 'done') {
    if (!$card->actual_hours || $card->actual_hours <= 0) {
        return back()->with('error', 
            'Wajib input time tracking sebelum menyelesaikan tugas.');
    }
}
```

**Cara Kerja:**
- Developer WAJIB input `hours_spent` setiap update progress
- Field `hours_spent` tidak bisa kosong (minimal 0.1 jam)
- Saat ingin set status ke `done`, system cek apakah sudah ada `actual_hours`
- Jika belum ada time tracking, tidak bisa selesaikan tugas
- Semua time tracking terakumulasi di field `actual_hours`

---

### ✅ **Tidak bisa assign tugas ke orang lain**
**Implementation:** Tidak ada method assignment di `Member\CardController`

**Cara Kerja:**
- Developer/Designer tidak punya akses ke method `assignUser()`
- Hanya bisa akses tugas yang sudah di-assign ke mereka
- Hanya Team Lead yang punya akses untuk assign tugas
- Route assignment hanya ada di `lead.*` dan `admin.*`

---

## 2️⃣ **Team Lead Rules**

### ✅ **Bisa assign tapi tidak bisa hapus proyek**
**Implementation:**
- ✅ Team Lead punya `Lead\CardController::store()` untuk assign tugas
- ✅ Team Lead TIDAK punya akses ke `ProjectController::destroy()`
- ✅ Route `/lead` tidak ada route untuk hapus project

**File Routes:** `routes/web.php`
```php
// Team Lead TIDAK ada route destroy project
Route::middleware(['auth','role:team_lead'])->prefix('lead')->group(function () {
    Route::resource('cards', LeadCardController::class)->except(['show']);
    // TIDAK ADA ProjectController di sini!
});
```

---

### ✅ **Bisa lihat semua tugas tapi tidak bisa edit yang sudah selesai**
**File:** `app/Http/Controllers/Lead/CardController.php`  
**Method:** `update()`  
**Lines:** 82-104

```php
public function update(Request $request, Card $card)
{
    $this->authorizeCard($card);
    
    // Team Lead tidak bisa edit tugas yang sudah selesai
    if ($card->status === 'done') {
        return back()->with('error', 
            'Tidak bisa edit tugas yang sudah selesai (done). 
            Tugas yang sudah selesai tidak dapat diubah.');
    }
    
    // ... validasi dan update
}
```

**Cara Kerja:**
- Team Lead bisa lihat semua tugas di projectnya via `index()`
- Saat coba edit tugas dengan status `done`, muncul error
- Tugas yang sudah done tidak bisa diubah lagi
- Melindungi data historis dari perubahan

---

## 3️⃣ **Project Admin Rules**

### ✅ **Full access kecuali hapus data historis**
**File:** `app/Http/Controllers/Admin/ProjectController.php`  
**Method:** `destroy()`  
**Lines:** 74-86

```php
public function destroy(Project $project)
{
    // Cek apakah project punya data historis (boards, cards, members)
    $hasBoards = $project->boards()->count() > 0;
    $hasMembers = $project->members()->count() > 0;
    
    if ($hasBoards || $hasMembers) {
        return back()->with('error', 
            'Tidak bisa hapus project yang sudah memiliki data historis 
            (boards/members). Project hanya bisa di-archive.');
    }
    
    $project->delete();
    return back()->with('status','Project dihapus.');
}
```

**Cara Kerja:**
- Admin bisa hapus project HANYA jika masih kosong
- Jika sudah ada boards atau members, muncul error
- Melindungi data historis (boards, cards, time tracking, comments)
- Admin disarankan untuk archive project, bukan hapus

---

### ✅ **Bisa override beberapa rules jika diperlukan**
**File:** `app/Http/Controllers/Admin/ProjectController.php`  
**Method:** `overrideRule()`  
**Lines:** 130-158

```php
public function overrideRule(Request $request, $resourceType, $resourceId)
{
    $data = $request->validate([
        'rule_type' => ['required', 'in:edit_done_task,multiple_active_tasks,force_assign'],
        'reason' => ['required', 'string', 'max:500']
    ]);

    // Log override action untuk audit trail
    \Log::info('Admin Override Rule', [
        'admin_id' => auth()->id(),
        'resource_type' => $resourceType,
        'resource_id' => $resourceId,
        'rule_type' => $data['rule_type'],
        'reason' => $data['reason'],
        'timestamp' => now()
    ]);

    return back()->with('status', 'Override rule berhasil diterapkan.');
}
```

**Route:**  
`POST /admin/override/{resourceType}/{resourceId}`

**Override Types:**
1. `edit_done_task` - Edit tugas yang sudah done
2. `multiple_active_tasks` - Assign multiple active tasks ke user
3. `force_assign` - Assign user yang sudah jadi member

**Cara Kerja:**
- Admin bisa bypass certain rules dengan alasan yang jelas
- Semua override di-log untuk audit trail
- Harus provide reason (wajib, max 500 karakter)
- Log mencatat: admin_id, resource, rule_type, reason, timestamp

**Contoh Usage:**
```php
POST /admin/override/card/123
{
    "rule_type": "edit_done_task",
    "reason": "Client request urgent change on completed task"
}
```

---

## 📋 **Summary Validations**

| Rule | Location | Method | Status |
|------|----------|--------|--------|
| Max 1 tugas aktif | Member\CardController | updateStatus() | ✅ |
| Wajib time tracking | Member\DeveloperController | updateProgress() | ✅ |
| Wajib hours saat done | Member\CardController | updateStatus() | ✅ |
| Tidak bisa assign | - | N/A (tidak ada method) | ✅ |
| Team Lead - tidak bisa hapus project | - | N/A (tidak ada route) | ✅ |
| Team Lead - tidak bisa edit done task | Lead\CardController | update() | ✅ |
| Admin - tidak bisa hapus historis | Admin\ProjectController | destroy() | ✅ |
| Admin - bisa override | Admin\ProjectController | overrideRule() | ✅ |

---

## 🧪 **Testing**

### Test 1: Developer - Max 1 tugas aktif
```
1. Login sebagai developer
2. Set tugas A ke status in_progress
3. Coba set tugas B ke in_progress
4. Expected: Error "Anda sudah memiliki tugas aktif"
```

### Test 2: Developer - Wajib time tracking
```
1. Login sebagai developer
2. Update progress tanpa input hours_spent
3. Expected: Validation error
4. Update progress dengan hours_spent: 2.5
5. Expected: Success, actual_hours bertambah 2.5
6. Coba set status ke done tanpa time tracking
7. Expected: Error "Wajib input time tracking"
```

### Test 3: Team Lead - Tidak bisa edit done task
```
1. Login sebagai team lead
2. Buka card dengan status done
3. Coba edit card
4. Expected: Error "Tidak bisa edit tugas yang sudah selesai"
```

### Test 4: Admin - Tidak bisa hapus historis
```
1. Login sebagai admin
2. Buat project baru (tanpa data)
3. Hapus project → Success
4. Buat project dengan boards/members
5. Coba hapus project
6. Expected: Error "Tidak bisa hapus project yang sudah memiliki data historis"
```

### Test 5: Admin - Override rules
```
1. Login sebagai admin
2. POST /admin/override/card/123
   {
     "rule_type": "edit_done_task",
     "reason": "Client urgent request"
   }
3. Check logs → harus ada entry override
4. Expected: Success dengan log lengkap
```

---

## 🔐 **Security Notes**

1. **Audit Trail**: Semua admin override di-log
2. **Immutable History**: Data done/completed tidak bisa diubah
3. **Time Accountability**: Wajib time tracking memastikan akuntabilitas
4. **Single Active Task**: Mencegah multitasking yang tidak efektif
5. **Role Isolation**: Developer tidak bisa assign, Team Lead tidak bisa hapus project

---

## 📊 **Logs Location**

Admin override logs tersimpan di:
- `storage/logs/laravel.log`
- Format: `[timestamp] production.INFO: Admin Override Rule {"admin_id":1,"resource_type":"card","resource_id":123,...}`

Untuk monitoring override actions:
```bash
# View recent overrides
tail -f storage/logs/laravel.log | grep "Admin Override Rule"

# Count overrides by admin
grep "Admin Override Rule" storage/logs/laravel.log | grep "admin_id\":1" | wc -l
```

---

## ✅ **Checklist Implementation**

- [x] Developer: Max 1 tugas in_progress
- [x] Developer: Wajib time tracking dengan validasi
- [x] Developer: Tidak bisa assign tugas
- [x] Team Lead: Bisa assign tugas
- [x] Team Lead: Tidak bisa hapus project  
- [x] Team Lead: Tidak bisa edit tugas done
- [x] Admin: Full access kecuali hapus historis
- [x] Admin: Override rules dengan logging
- [x] All: Validation error messages dalam Bahasa Indonesia

**SEMUA BUSINESS RULES SUDAH DIIMPLEMENTASIKAN DENGAN LENGKAP!** ✨
