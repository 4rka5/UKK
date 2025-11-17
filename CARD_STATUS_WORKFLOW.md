# Card Status Workflow - 4 Kondisi

## Overview
Sistem card menggunakan 4 status yang merepresentasikan lifecycle tugas dari pembuatan hingga selesai.

## Status Conditions

### 1. **To Do** (Default)
- **Kode:** `todo`
- **Deskripsi:** Tugas baru dibuat, belum diserahkan ke user
- **Siapa yang set:** Team Lead (otomatis saat create card)
- **Kondisi:** 
  - Card baru dibuat
  - Belum ada user yang dikerjakan
  - Menunggu assignment atau perencanaan
- **Warna Badge:** Secondary/Gray

### 2. **In Progress**
- **Kode:** `in_progress`
- **Deskripsi:** Tugas sudah diserahkan ke user dan sedang dikerjakan
- **Siapa yang set:** Team Lead
- **Kondisi:**
  - Card sudah di-assign ke user (designer/developer)
  - User sudah mulai mengerjakan tugas
  - Team lead mengubah status dari 'To Do' ke 'In Progress'
- **Warna Badge:** Primary/Blue
- **Action:** User mulai bekerja pada tugas ini

### 3. **Review**
- **Kode:** `review`
- **Deskripsi:** User sudah menyelesaikan tugas, menunggu approval team lead
- **Siapa yang set:** User (Designer/Developer)
- **Kondisi:**
  - User sudah selesai mengerjakan tugas
  - Menunggu team lead untuk review dan approval
  - Card tidak bisa diedit oleh user lagi
- **Warna Badge:** Warning/Yellow
- **Action:** Team lead perlu review hasil pekerjaan

### 4. **Done**
- **Kode:** `done`
- **Deskripsi:** Tugas sudah di-approve dan selesai
- **Siapa yang set:** Team Lead
- **Kondisi:**
  - Team lead sudah review dan approve hasil kerja user
  - Tugas benar-benar selesai dan tidak ada revisi
  - Card tidak bisa diedit lagi (locked)
- **Warna Badge:** Success/Green
- **Action:** Tugas selesai, card archived

## Workflow Diagram

```
┌──────────────────────────────────────────────────────────────┐
│                     CARD LIFECYCLE                           │
└──────────────────────────────────────────────────────────────┘

[CREATE CARD]
     │
     ↓
┌─────────────┐
│   TO DO     │ ← Default Status
│  (todo)     │   Team Lead creates card
└─────────────┘
     │
     │ Team Lead assigns user & change status
     ↓
┌─────────────┐
│ IN PROGRESS │   User starts working
│(in_progress)│   
└─────────────┘
     │
     │ User finishes task
     ↓
┌─────────────┐
│   REVIEW    │   Team Lead reviews work
│  (review)   │   
└─────────────┘
     │
     │ Team Lead approves
     ↓
┌─────────────┐
│    DONE     │   Task completed ✓
│   (done)    │   Card locked
└─────────────┘
```

## Role Permissions

### Team Lead
- ✅ Dapat membuat card (default status: todo)
- ✅ Dapat assign user ke card
- ✅ Dapat mengubah status dari `todo` → `in_progress`
- ✅ Dapat mengubah status dari `review` → `done` (approve)
- ✅ Dapat mengubah status dari `review` → `in_progress` (revisi)
- ❌ Tidak bisa edit card yang sudah `done`

### User (Designer/Developer)
- ✅ Dapat melihat card yang di-assign ke mereka
- ✅ Dapat mengubah status dari `in_progress` → `review` (submit)
- ❌ Tidak bisa set status `done`
- ❌ Tidak bisa edit card yang sudah `review` atau `done`

### Admin
- ✅ Full access ke semua status
- ✅ Dapat override semua status

## Business Rules

### Rule 1: Default Status
```php
// Saat create card, status default = 'todo'
$data['status'] = $data['status'] ?? 'todo';
```

### Rule 2: Status Progression
- `todo` → `in_progress` (Team Lead assigns & starts)
- `in_progress` → `review` (User submits work)
- `review` → `done` (Team Lead approves)
- `review` → `in_progress` (Team Lead requests revision)

### Rule 3: Locked Status
```php
// Card dengan status 'done' tidak bisa diedit
if ($card->status === 'done') {
    return back()->with('error', 'Tugas yang sudah selesai tidak dapat diubah.');
}
```

### Rule 4: Assignment Validation
- Card dengan status `in_progress` harus punya assigned user
- Card dengan status `review` atau `done` harus punya actual_hours

## Implementation Examples

### Team Lead - Assign & Start Task
```php
// 1. Team lead creates card (status = todo)
$card = Card::create([
    'card_title' => 'Design Homepage',
    'status' => 'todo', // default
    // ...
]);

// 2. Team lead assigns user & change to in_progress
$card->assignees()->attach($userId, [
    'assignment_status' => 'in_progress',
    'assigned_at' => now()
]);
$card->update(['status' => 'in_progress']);
```

### User - Submit Completed Task
```php
// User finishes work and submits for review
if ($card->status === 'in_progress') {
    $card->update([
        'status' => 'review',
        'actual_hours' => $hoursWorked
    ]);
}
```

### Team Lead - Approve Task
```php
// Team lead reviews and approves
if ($card->status === 'review') {
    $card->update(['status' => 'done']);
    
    // Update user assignment status
    $card->assignees()->updateExistingPivot($userId, [
        'assignment_status' => 'completed'
    ]);
}
```

### Team Lead - Request Revision
```php
// Team lead sends back for revision
if ($card->status === 'review') {
    $card->update(['status' => 'in_progress']);
    // Add comment explaining what needs to be fixed
}
```

## Status Badge Colors (Frontend)

### Bootstrap Classes
```php
$badgeClass = match($status) {
    'todo' => 'bg-secondary',      // Gray
    'in_progress' => 'bg-primary',  // Blue
    'review' => 'bg-warning',       // Yellow
    'done' => 'bg-success',         // Green
    default => 'bg-secondary'
};
```

### Usage in Blade
```blade
<span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
```

## Migration Data Mapping

### From Old 6 Status to New 4 Status
```sql
-- backlog → todo
-- todo → todo
-- in_progress → in_progress
-- code_review → review
-- testing → review
-- done → done
```

## API Endpoints

### Get Cards by Status
```http
GET /api/cards?status=todo
GET /api/cards?status=in_progress
GET /api/cards?status=review
GET /api/cards?status=done
```

### Update Card Status
```http
PATCH /api/cards/{id}/status
Content-Type: application/json

{
  "status": "in_progress"
}
```

## Validation Rules

### Controller Validation
```php
$request->validate([
    'status' => ['required', 'in:todo,in_progress,review,done']
]);
```

### Database Enum
```sql
ALTER TABLE management_project_cards 
MODIFY status ENUM('todo','in_progress','review','done') DEFAULT 'todo';
```

## Testing Scenarios

### Test 1: New Card Default Status
```php
$card = Card::create(['card_title' => 'Test']);
$this->assertEquals('todo', $card->status);
```

### Test 2: Status Progression
```php
$card->update(['status' => 'in_progress']);
$this->assertEquals('in_progress', $card->fresh()->status);

$card->update(['status' => 'review']);
$this->assertEquals('review', $card->fresh()->status);

$card->update(['status' => 'done']);
$this->assertEquals('done', $card->fresh()->status);
```

### Test 3: Cannot Edit Done Card
```php
$card->update(['status' => 'done']);
$response = $this->put("/lead/cards/{$card->id}", $data);
$response->assertSessionHas('error');
```

## Summary

| Status | Who Sets | Can Edit | Next Status |
|--------|----------|----------|-------------|
| `todo` | Team Lead | Yes | `in_progress` |
| `in_progress` | Team Lead | Yes | `review` |
| `review` | User | No | `done` or `in_progress` |
| `done` | Team Lead | No | - (Final) |

## Benefits

1. ✅ **Simplicity** - 4 status lebih mudah dipahami dibanding 6
2. ✅ **Clear Workflow** - Setiap status punya meaning yang jelas
3. ✅ **Role Separation** - Jelas siapa yang bisa set status apa
4. ✅ **Quality Control** - Review step memastikan quality
5. ✅ **Accountability** - Track siapa yang approve (done by team lead)
