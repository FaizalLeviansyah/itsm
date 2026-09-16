# ITSM Review & Rekomendasi — Fleet IT Report (Amarin Ship Management)

> Tanggal Review: 7 Juli 2026  
> Reviewer: Kiro AI  
> Basis: ITIL v4 Best Practice + Standar ISO/IEC 20000

---

## 1. Ringkasan Fitur yang Sudah Ada (Existing Features)

| Modul | Status | Catatan |
|---|---|---|
| Ticketing (Incident & Service Request) | ✅ Ada | GLPI-style, status 0–6, priority matrix |
| SLA Fields (`time_to_own`, `time_to_resolve`) | ⚠️ Ada di DB, belum dipakai | Kolom ada tapi tidak dihitung/ditampilkan |
| Problem Management | ✅ Ada | Cascade resolve ke tiket, tapi tersembunyi dari navigasi |
| Change Request | ✅ Ada | Approval workflow, risk level, rollback plan |
| Knowledge Base | ✅ Ada | Public/private, kategori, rich editor |
| Asset Management (ITAM) | ✅ Ada | Agent-based, depreciation, financial recommendation |
| Dashboard Widgets | ✅ Ada | Stats, incident trend chart, pending tickets |
| Notifikasi WhatsApp | ✅ Ada | Created + Resolved, tapi nomor SPV masih hardcoded |
| CCTV Monitoring | ✅ Ada | Per kapal, per channel, frame interval |
| Personal IT Report | ✅ Ada | Planned/actual tasks per minggu |
| Portal Pegawai (User Portal) | ✅ Ada | Submit tiket, lihat status, approval solusi |
| RBAC (Role-based Access) | ⚠️ Partial | Hardcoded nama/email, bukan role table |
| Audit Trail Tiket | ✅ Ada | `assigned_by_id`, `assigned_at` |

---

## 2. Analisis Mendalam Per Area

### 2.1 SLA Monitoring — PRIORITAS TINGGI 🔴

**Kondisi Sekarang:**
- Kolom `time_to_own` dan `time_to_resolve` sudah ada di tabel `tickets` sejak migrasi awal.
- Kolom-kolom ini di-`nullable` dan tidak pernah diisi oleh sistem secara otomatis.
- Tidak ada widget, kolom tabel, atau alert yang menampilkan status SLA.
- Tidak ada definisi SLA per kategori atau per prioritas tiket.

**Yang Perlu Ditambahkan:**
```
1. SLA Policy Table (tabel sla_policies):
   - priority (1–5) → response_time_hours, resolve_time_hours
   - Contoh: Priority 5 (Critical) → Response 1 jam, Resolve 4 jam
             Priority 3 (Medium)   → Response 4 jam, Resolve 24 jam

2. Auto-populate SLA saat tiket dibuat/di-publish:
   - time_to_own    = created_at + response_time berdasarkan priority
   - time_to_resolve = created_at + resolve_time berdasarkan priority

3. Kolom visual SLA di tabel Ticket:
   - SLA Response: Hijau/Kuning/Merah berdasarkan sisa waktu
   - SLA Resolve: Hijau/Kuning/Merah berdasarkan sisa waktu
   - "Breached" badge merah jika sudah lewat deadline

4. Dashboard Widget SLA:
   - % tiket selesai dalam SLA (SLA Achievement Rate)
   - Tiket yang sudah breach SLA (overdue)
   - MTTR (Mean Time to Resolve) per bulan

5. Notifikasi Eskalasi Otomatis:
   - T-30 menit sebelum SLA breach → notif ke teknisi
   - Saat SLA breach → eskalasi ke Head IT
```

---

### 2.2 Ticket Management — PERLU PERBAIKAN 🟡

**Masalah yang Ditemukan:**

**a) Inkonsistensi Kolom:**
- Migration awal: kolom `content` untuk deskripsi
- Resource Filament: menggunakan `description`  
- Tiket form juga punya `assigned_to` (integer) dan `assigned_to_id` — dua kolom yang tumpang tindih berdasarkan migrasi `add_agent_data` dan `add_audit_trails`.

**b) Priority Form vs Priority Matrix:**
- Form `TicketResource` menampilkan dropdown priority 1–3 (Low/Medium/High) yang bisa diubah manual.
- Model `Ticket` sudah punya `computePriority()` berdasarkan matrix `urgency × impact`.
- Tapi form tidak menampilkan field `urgency` dan `impact` sama sekali — matrix-nya tidak terpakai dalam UI.
- Priority matrix harus diaktifkan: tampilkan urgency + impact, sembunyikan priority (auto-computed).

**c) Kategori Tiket (ITIL Category):**
- Kolom `itilcategories_id` sudah ada di migration tapi tabelnya tidak pernah dibuat.
- Tidak ada filter kategori di list tiket.
- Harus dibuat tabel `itil_categories` dengan hierarki (Hardware → PC, Hardware → Printer, Network → WiFi, dll).

**d) Status "Draft" (0) vs GLPI Standard:**
- Status 0 (Draft) adalah penambahan kustom yang tidak ada di standar GLPI.
- Ini valid untuk use case portal pegawai (simpan dulu sebelum submit), tapi perlu dipastikan Draft tidak ikut dihitung dalam SLA.

**e) Attachment:**
- Migration `add_attachment_to_incident_tickets` sudah ada, tapi tidak ada field attachment di form `TicketResource` Filament.
- User tidak bisa upload screenshot/foto masalah.

**f) Tombol "Ambil Tiket" tidak set `assigned_by_id`:**
```php
// Kode sekarang — kurang lengkap:
$record->update([
    'assigned_to' => Auth::id(),
    'status' => 3,
]);

// Seharusnya:
$record->update([
    'assigned_to' => Auth::id(),
    'assigned_by_id' => Auth::id(),
    'assigned_at' => now(),
    'status' => 3,
    'time_to_own' => now(), // Mark TTO sebagai terpenuhi
]);
```

---

### 2.3 Problem Management — PERLU DIPERBAIKI 🟡

**Masalah yang Ditemukan:**

1. **Menu disembunyikan dari navigasi (`$shouldRegisterNavigation = false`)** — Problem Management adalah bagian inti ITIL, seharusnya bisa diakses langsung oleh tim IT, bukan disembunyikan.

2. **Teknisi hardcoded by name:**
   ```php
   User::whereIn('full_name', ['FAIZAL LEVIANSYAH', 'FARHAN ARIF INDIARTO', 'HENDRI SETIO PRAKOSO'])
   ```
   Ini sangat rapuh. Jika ada pergantian staf, seluruh sistem pecah. Harus diganti dengan `is_it_team = 1`.

3. **Tidak ada Root Cause Analysis (RCA) yang terstruktur:**
   - Saat ini hanya ada field `description` (rich text bebas).
   - Perlu field terstruktur: `root_cause`, `workaround`, `permanent_solution`, `recurrence_prevention`.

4. **Tidak ada link ke Change Request:**
   - Problem yang membutuhkan perubahan infrastruktur harus bisa langsung menghasilkan Change Request.
   - Perlu relasi `problem_id` di tabel `change_requests`.

5. **Status Problem terlalu sederhana (3 status):**
   - Standar ITIL: New → Investigation → Known Error → Resolved → Closed

---

### 2.4 Change Request — PERLU DIPERBAIKI 🟡

**Masalah yang Ditemukan:**

1. **Approval hardcoded by full_name:**
   ```php
   ->visible(fn ($record) => ... && auth()->user()->full_name === 'HENDRI SETIO PRAKOSO')
   ```
   Sama seperti di Problem, sangat rapuh. Harus menggunakan role atau flag `is_manager`.

2. **Tidak ada tanggal actual (hanya planned):**
   - Ada `planned_start_date` dan `planned_end_date`.
   - Tapi tidak ada `actual_start_date` dan `actual_end_date`.
   - Tidak bisa mengukur apakah implementasi tepat waktu.

3. **Status "Implemented" tidak otomatis:**
   - Status 5 (Implemented) harus bisa diupdate dengan catatan hasil implementasi.
   - Perlu field `implementation_notes` dan `implemented_at`.

4. **Tidak ada link ke tiket/problem asal:**
   - Change Request sering lahir dari Problem atau Incident.
   - Perlu `problem_id` atau `ticket_id` sebagai referensi asal.

5. **Tidak ada file attachment:**
   - Dokumen Change Advisory Board (CAB), diagram arsitektur, dll perlu bisa diupload.

---

### 2.5 RBAC (Role-based Access Control) — MASALAH KRITIS 🔴

**Kondisi Sekarang:**
Sistem menggunakan kombinasi tidak konsisten:
```php
// Cara 1: hardcoded email
$isSuperAdmin = $userEmail === 'head.it@amarinshipmgmt.com';

// Cara 2: hardcoded full_name
auth()->user()->full_name === 'HENDRI SETIO PRAKOSO'

// Cara 3: kolom is_it_team
auth()->user()->is_it_team == 1

// Cara 4: kolom role
strtolower(auth()->user()->role) !== 'owner'
```

**Risiko:**
- Pergantian email atau nama staf = sistem langsung broken.
- Tidak ada cara untuk menambah/mengubah permission tanpa edit kode.
- Tidak ada log siapa yang bisa mengakses apa.

**Solusi yang Direkomendasikan:**
```
Gunakan Filament Shield (spatie/laravel-permission) atau minimal:
- Buat enum/konstanta Role: IT_TEAM, IT_MANAGER, STAFF, OWNER
- Semua pengecekan pakai: auth()->user()->hasRole('it_manager')
- Atau minimal flag boolean: is_it_team, is_manager, is_owner
- Hapus semua hardcoded nama dan email dari logika bisnis
```

---

### 2.6 Dashboard & Reporting — PERLU PENAMBAHAN 🟡

**Yang Ada Sekarang:**
- Stats: Tiket Aktif, Tiket Unassigned, Tiket Selesai, Aset Online
- Chart: Tren tiket 7 hari (line chart)
- Chart: Tiket per status (doughnut/bar)
- Widget: Latest Pending Tickets
- Widget: Vessel Downtime Calendar, NVR Status

**Yang Belum Ada:**
```
1. SLA Achievement Rate widget (% tiket selesai dalam SLA)
2. MTTR (Mean Time to Resolve) — rata-rata waktu penyelesaian
3. MTTA (Mean Time to Acknowledge/Assign) — rata-rata waktu response
4. Tiket per Kategori (breakdown by type)
5. Tiket per Teknisi (beban kerja per orang)
6. Tiket SLA Breach (yang sudah lewat batas)
7. Filter dashboard by period (today/week/month/year)
8. Export laporan SLA ke Excel/PDF
```

---

### 2.7 Knowledge Base — SUDAH CUKUP BAIK ✅

Knowledge Base sudah cukup baik dengan:
- Kategori
- Public/private toggle
- Rich editor
- RBAC: hanya IT yang bisa buat/edit

**Kekurangan Minor:**
- Tidak ada view count / popularitas artikel
- Tidak ada fitur search full-text (hanya searchable di list)
- Tidak ada tag/label selain kategori
- Tidak ada link dari tiket ke artikel KB terkait (saat menutup tiket, sarankan artikel)

---

### 2.8 Asset Management (ITAM) — SUDAH BAIK ✅

Asset management sudah solid dengan:
- Agent-based sync (PowerShell)
- Financial depreciation & recommendation
- Software inventory
- Last seen tracking

**Kekurangan:**
- Tidak ada history perubahan aset (changelog)
- Tidak ada notifikasi aset mendekati end-of-life
- Tidak ada filter aset yang sudah melewati masa pakai
- Tidak ada relasi langsung ke tiket yang berkaitan dengan aset tersebut di list aset

---

### 2.9 Notifikasi WhatsApp — PERLU PERBAIKAN 🟡

**Masalah:**
```php
// Nomor hardcoded di TicketObserver.php baris 33:
WhatsAppService::sendMessage('081234567890', $msgSpv);
```

**Yang Perlu Diperbaiki:**
- Nomor SPV/Head IT harus diambil dari database (tabel users berdasarkan role).
- Tambahkan notif saat: SLA hampir breach, tiket di-assign, tiket di-reject user.
- Tambahkan toggle di settings untuk enable/disable notif WA.

---

### 2.10 Duplikat Route — BUG 🔴

Di `routes/web.php` ada duplikat route yang perlu dibersihkan:
```php
// Line 37 & 43 — DUPLIKAT:
Route::post('/ticket/{id}/reply', ...)  ->name('portal.reply-ticket');
Route::post('/ticket/{id}/reply', ...)  ->name('reply-ticket');

// Line 38 & 45 — DUPLIKAT:
Route::post('/ticket/{id}/approve', ...) ->name('portal.approve-ticket');
Route::post('/ticket/{id}/approve', ...) ->name('approve-ticket');
```

---

## 3. Rekomendasi Prioritas Implementasi

### 🔴 PRIORITAS 1 — Critical (Segera)

| # | Item | Alasan |
|---|---|---|
| 1 | **Implementasi SLA Engine** | Kolom sudah ada, tinggal diaktifkan. Ini inti dari ITSM. |
| 2 | **Perbaiki RBAC** | Hardcoded nama/email = security risk dan maintenance nightmare |
| 3 | **Bersihkan duplikat route** | Bug nyata yang bisa menyebabkan konflik routing |
| 4 | **Aktifkan urgency + impact di form tiket** | Priority matrix sudah dibuat tapi tidak dipakai |

### 🟡 PRIORITAS 2 — Important (1–2 Sprint)

| # | Item | Alasan |
|---|---|---|
| 5 | **SLA Dashboard Widget** | Tanpa ini, SLA monitoring tidak bisa dilakukan |
| 6 | **ITIL Category Table** | `itilcategories_id` sudah ada di DB, tinggal buat tabelnya |
| 7 | **Aktifkan Problem Management di navigasi** | Disembunyikan padahal fiturnya sudah ada |
| 8 | **Perbaiki notif WA (ganti nomor hardcoded)** | Risiko operasional |
| 9 | **Tambah attachment di form tiket** | User tidak bisa upload screenshot masalah |
| 10 | **Fix audit trail di tombol "Ambil Tiket"** | `assigned_by_id` dan `assigned_at` tidak terisi |

### 🟢 PRIORITAS 3 — Enhancement (Backlog)

| # | Item | Alasan |
|---|---|---|
| 11 | Link tiket → artikel Knowledge Base | Mengurangi tiket berulang |
| 12 | Notifikasi SLA breach (eskalasi otomatis) | Proactive monitoring |
| 13 | Beban kerja per teknisi (workload widget) | Distribusi pekerjaan merata |
| 14 | Actual date di Change Request | Mengukur ketepatan implementasi |
| 15 | Asset end-of-life alert | Proactive replacement planning |
| 16 | Problem → Change Request linkage | Traceability ITIL |
| 17 | Export SLA report (Excel/PDF) | Reporting ke manajemen |
| 18 | Full-text search Knowledge Base | User experience |

---

## 4. Blueprint Implementasi SLA Engine (Detail Teknis)

### Langkah 1: Buat Tabel SLA Policy

```php
// Migration: create_sla_policies_table
Schema::create('sla_policies', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Contoh: "Critical SLA", "Standard SLA"
    $table->tinyInteger('priority_level'); // 1–5 sesuai priority matrix
    $table->tinyInteger('type')->default(1); // 1=Incident, 2=ServiceRequest
    $table->integer('response_time_minutes'); // time_to_own
    $table->integer('resolve_time_minutes');  // time_to_resolve
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Langkah 2: Auto-Populate SLA di TicketObserver

```php
public function created(Ticket $ticket)
{
    // Set SLA saat tiket berstatus New (1) atau saat pertama dibuat
    if ($ticket->status >= 1) {
        $policy = SlaPolicy::where('priority_level', $ticket->priority)
            ->where('type', $ticket->type)
            ->where('is_active', true)
            ->first();

        if ($policy) {
            $ticket->updateQuietly([
                'time_to_own'     => now()->addMinutes($policy->response_time_minutes),
                'time_to_resolve' => now()->addMinutes($policy->resolve_time_minutes),
            ]);
        }
    }
}
```

### Langkah 3: Kolom SLA di Tabel Ticket (Filament)

```php
// Di TicketResource::table()
Tables\Columns\TextColumn::make('time_to_resolve')
    ->label('SLA Resolve')
    ->badge()
    ->getStateUsing(function (Ticket $record): string {
        if (!$record->time_to_resolve) return 'No SLA';
        if (in_array($record->status, [5, 6])) return 'Completed';
        $diff = now()->diffInMinutes($record->time_to_resolve, false);
        if ($diff < 0) return 'BREACHED';
        if ($diff < 60) return 'Critical (' . abs($diff) . 'm)';
        return 'OK (' . round($diff / 60, 1) . 'h)';
    })
    ->color(function (Ticket $record): string {
        if (!$record->time_to_resolve) return 'gray';
        if (in_array($record->status, [5, 6])) return 'success';
        $diff = now()->diffInMinutes($record->time_to_resolve, false);
        if ($diff < 0) return 'danger';
        if ($diff < 60) return 'warning';
        return 'success';
    }),
```

### Langkah 4: Widget SLA Dashboard

```php
// SlaOverviewWidget.php
Stat::make('SLA Achievement', $achievementRate . '%')
    ->description("Tiket selesai dalam SLA bulan ini")
    ->color($achievementRate >= 90 ? 'success' : ($achievementRate >= 70 ? 'warning' : 'danger')),

Stat::make('SLA Breached', $breachedCount)
    ->description('Tiket melewati batas SLA')
    ->color($breachedCount > 0 ? 'danger' : 'success'),

Stat::make('MTTR', round($avgResolveMins / 60, 1) . ' Jam')
    ->description('Rata-rata waktu penyelesaian'),
```

---

## 5. Catatan Khusus: Fitur yang Perlu Dikurangi / Disederhanakan

### Yang Perlu Disederhanakan:
1. **Duplikat `assigned_to` vs `assigned_to_id`** — pilih satu kolom yang konsisten.
2. **Dua cara akses portal tiket** — via Filament dan via `/portal/ticket/create`. Pilih satu sebagai primary, dokumentasikan yang lain sebagai legacy.
3. **Inline CSS masif di AdminPanelProvider** (~150 baris CSS) — sebaiknya dipindah ke file CSS terpisah di `public/css/` agar mudah dimaintain.

### Yang Tidak Perlu Ditambah Sekarang:
- Jangan tambah fitur sampai SLA engine benar-benar berjalan — fitur baru tanpa SLA monitoring tidak ada nilainya untuk manajemen.
- Jangan rebuild RBAC dulu dengan spatie/permission jika tim kecil — cukup tambahkan kolom `role` dengan enum dan pakai helper sederhana.

---

## 6. Kesimpulan

Sistem ITSM ini sudah punya fondasi yang sangat solid — ticketing, problem management, change management, knowledge base, dan asset management semuanya sudah ada. Arsitekturnya mengikuti framework ITIL v4 dengan baik.

**Gap terbesar adalah SLA Monitoring** — kolom sudah ada di database sejak awal, tapi mesin SLA-nya belum diaktifkan. Ini adalah satu-satunya hal yang membuat sistem ini belum bisa disebut ITSM yang lengkap dari perspektif monitoring.

**Gap kedua adalah RBAC** yang masih menggunakan hardcoded nama dan email, yang merupakan technical debt yang perlu diselesaikan sebelum sistem ini digunakan lebih luas.

Dengan implementasi Prioritas 1 dan 2 di atas, sistem ini akan memiliki SLA monitoring yang lengkap dan siap digunakan untuk laporan kinerja IT ke manajemen.
