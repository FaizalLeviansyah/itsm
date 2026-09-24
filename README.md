# 🚀 Amarin Fleet ITSM (IT Service Management) System

Sistem Manajemen Layanan TI (ITSM) terpadu berbasis web yang dirancang khusus untuk mengelola infrastruktur TI di Kantor Pusat (*Shore*) dan Armada Kapal (*Fleet/Vessels*). Sistem ini dibangun di atas kerangka kerja **ITIL v4 Best Practice** serta memenuhi standar **ISO/IEC 20000** untuk menjamin keandalan, transparansi, serta efisiensi operasional TI maritim.

---

## 📌 Daftar Isi
- [1. Ringkasan Arsitektur & Standardisasi](#1-ringkasan-arsitektur--standardisasi)
- [2. Modul Sistem Lengkap (Existing Features)](#2-modul-sistem-lengkap-existing-features)
- [3. Pembaruan Pasca-Handover (Post-Handover Updates)](#3-pembaruan-pasca-handover-post-handover-updates)
- [4. Audit Trail & Asset History Engine](#4-audit-trail--asset-history-engine)
- [5. Analisis GAP & Blueprint SLA Engine (ITIL v4)](#5-analisis-gap--blueprint-sla-engine-itil-v4)
- [6. Matriks Prioritas & Roadmap Pengembangan](#6-matriks-prioritas--roadmap-pengembangan)
- [7. Panduan Instalasi & Konfigurasi Lingkungan](#7-panduan-instalasi--konfigurasi-lingkungan)

---

## 1. Ringkasan Arsitektur & Standardisasi

Sistem ini ditinjau secara berkala untuk memastikan kesesuaian operasional:
* **Reviewer:** Kiro AI (7 Juli 2026)
* **Basis Standardisasi:** ITIL v4 Framework & ISO/IEC 20000
* **Penanggung Jawab Operasional:** Faizal Leviansyah (IT Support Staff) setelah *handover* dari Hendry Setio Prakoso (IT Manager).

---

## 2. Modul Sistem Lengkap (Existing Features)

| Modul | Status | Deskripsi & Cakupan Fitur |
|---|---|---|
| **Ticketing (Incident & Service Request)** | ✅ Aktif | Berbasis standar GLPI (Status 0–6), dilengkapi dengan *Priority Matrix* (`Urgency` × `Impact`). |
| **SLA Management** | ⚠️ Partial | Kolom database (`time_to_own`, `time_to_resolve`) telah tersedia tetapi memerlukan aktivasi *engine* kalkulasi otomatis. |
| **Problem Management** | ✅ Aktif | Mendukung *cascade resolve* (penyelesaian otomatis ke tiket terhubung). |
| **Change Request (Change Management)** | ✅ Aktif | Dilengkapi alur persetujuan (*approval workflow*), analisis tingkat risiko (*risk level*), dan rencana pemulihan (*rollback plan*). |
| **Knowledge Base (KB)** | ✅ Aktif | Artikel privat/publik, kategori hierarkis, serta *rich text editor*. |
| **Asset Management (ITAM)** | ✅ Aktif | Berbasis *agent/telemetry sync*, analisis depresiasi keuangan, dan rekomendasi penggantian aset. |
| **Monitoring CCTV & NVR** | ✅ Aktif | Pemantauan status perekaman CCTV per kapal, status *channel*, dan pengaturan *frame interval*. |
| **Portal Pegawai (User Portal)** | ✅ Aktif | Antarmuka mandiri untuk pelaporan tiket, peninjauan status, dan persetujuan solusi. |
| **Personal IT Report** | ✅ Aktif | Pencatatan tugas mingguan (*planned vs actual tasks*) untuk teknisi IT. |
| **Notifikasi WhatsApp** | ✅ Aktif | Pengiriman pesan otomatis saat tiket dibuat (*created*) dan diselesaikan (*resolved*)[cite: 1]. |
| **Audit Trail & Logging** | ✅ Aktif | Pencatatan penugasan tiket (`assigned_by_id`, `assigned_at`) dan riwayat aset (`AssetHistory`)[cite: 1, 2]. |

---

## 3. Pembaruan Pasca-Handover (Post-Handover Updates)

Berikut adalah detail teknis pembaruan arsitektur dan otomatisasi yang dikembangkan oleh **Faizal Leviansyah**[cite: 2]:

### 3.1 Integrasi Data Perusahaan Terpusat (Company ID)
* **Single Source of Truth:** Pengelolaan data perusahaan secara manual (CRUD) ditiadakan untuk menjaga konsistensi data[cite: 2].
* **Session API Mapping:** Saat pengguna *login*, sistem mendeteksi identitas perusahaan secara otomatis melalui API pusat[cite: 2]:
  * `ID 1` = **PT Amarin Ship Management**[cite: 2]
  * `ID 2` = **PT Caraka Tirta Pratama**[cite: 2]
* **Auto Injection:** Informasi perusahaan disisipkan secara otomatis di latar belakang ke dalam kolom `additional_info` setiap kali tiket baru dibuat tanpa memerlukan masukan manual dari pengguna[cite: 2].

### 3.2 Integrasi Service API SOC (`SocIntegrationService.php`)
* **Keamanan API:** Menggunakan REST API terenkripsi dengan otentikasi *header* `x-api-key`[cite: 2].
* **Smart Parsing (`parsePcBrand`):** Memisahkan string kompleks dari SOC menjadi data terstruktur (Merek PC: Acer/HP/Asus, Tipe Model, dan *Serial Number*)[cite: 2].
* **Auto Asset Tagging:** Pembuatan kode tag aset otomatis berbasis nama *hostname* (`SOC-[HOSTNAME]`) dan pengelompokan otomatis ke kategori *Computer / Laptop*[cite: 2].

### 3.3 Resolusi Error Sync SOC & Auto-Register User
* **Masalah Awal:** Terjadi kegagalan sinkronisasi (*SQL Error*) pada 24 *endpoint* karena kolom `assigned_to` pada database ITSM membutuhkan *Foreign Key* (ID User), sedangkan SOC hanya mengirim data teks nama (*alias*)[cite: 2].
* **Mekanisme Solusi (Auto-Register Dummy Account):**
  1. Sistem melakukan pencarian nama berdasarkan *alias* dari SOC[cite: 2].
  2. Jika pengguna belum terdaftar, sistem membuat akun profil baru secara otomatis di latar belakang dengan email terenkapsulasi (`[namaclean]@soc-sync.local`)[cite: 2].
  3. ID pengguna baru langsung dihubungkan ke kolom `assigned_to`[cite: 2].
  4. **Hasil:** Tingkat keberhasilan sinkronisasi meningkat dari **24 Failed** menjadi **100% Success** (0 Error)[cite: 2].
* **Null State Handling:** Jika *alias* pada SOC kosong (`-`), kolom `ASSIGNED TO` di ITSM juga akan secara konsisten ditampilkan sebagai *Unassigned* (`-`)[cite: 2].

---

## 4. Audit Trail & Asset History Engine

Setiap kali tombol **Sync SOC** dijalankan, modul *Asset History Engine* akan membandingkan data lama dan baru secara otomatis[cite: 2]. Perubahan pada variabel berikut akan dicatat ke tabel `asset_histories`[cite: 2]:
* Nama *Hostname*[cite: 2]
* Status Antivirus (`antivirus_status`)[cite: 2]
* Status Keamanan USB (`usb_status`)[cite: 2]
* Status Pembaruan Windows (`windows_update_status`)[cite: 2]
* Status Koneksi SOC (`soc_status`)[cite: 2]
* Lokasi Kapal / Unit (`vessel_name`)[cite: 2]

---

## 5. Analisis GAP & Blueprint SLA Engine (ITIL v4)

Berdasarkan tinjauan ITIL v4, area paling krusial yang perlu diaktifkan adalah *Service Level Agreement (SLA) Engine*[cite: 1].

### 5.1 Skema Basis Data SLA (`sla_policies`)
Tabel baru yang dirancang untuk mengelola ambang batas SLA[cite: 1]:

```php
Schema::create('sla_policies', function (Blueprint $table) {
    $table->id();$table->string('name'); // Contoh: "Critical Incident SLA"[cite: 1]
    $table->tinyInteger('priority_level'); // Prioritas 1–5[cite: 1]$table->tinyInteger('type')->default(1); // 1 = Incident, 2 = Service Request[cite: 1]
    $table->integer('response_time_minutes'); // Target Response (Time To Own)[cite: 1]$table->integer('resolve_time_minutes');  // Target Resolve (Time To Resolve)[cite: 1]
    $table->boolean('is_active')->default(true);$table->timestamps();
});

5.2 Logika Pemicu SLA (TicketObserver.php)
Pengisian kolom SLA secara otomatis saat tiket diterbitkan[cite: 1]:
public function created(Ticket $ticket)
{
    if ($ticket->status >= 1) {
        $policy = SlaPolicy::where('priority_level',$ticket->priority)
            ->where('type', $ticket->type)
            ->where('is_active', true)
            ->first();

        if ($policy) {
            $ticket->updateQuietly([                 'time_to_own'     => now()->addMinutes($policy->response_time_minutes),
                'time_to_resolve' => now()->addMinutes($policy->resolve_time_minutes),
            ]);
        }
    }
}

5.3 Indikator Tampilan SLA pada Tabel (Filament UI)
Pemberian warna dan lencana peringatan otomatis berdasarkan batas waktu[cite: 1]:
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
    });

6. Matriks Prioritas & Roadmap Pengembangan
🔴 Prioritas 1: Kritis (Immediate Action)
Aktivasi Engine Kalkulasi SLA: Mengisi kolom time_to_own dan time_to_resolve secara dinamis[cite: 1].

Refactoring RBAC: Menghapus logika hak akses berbasis nama/email yang hardcoded dan menggantinya dengan tabel Role/Permission terstruktur[cite: 1].

Pembersihan Route Duplikat: Menghapus duplikasi pendaftaran route pada routes/web.php[cite: 1].

Pengaktifan Form Matriks Prioritas: Menampilkan kembali field Urgency dan Impact pada formulir tiket[cite: 1].

🟡 Prioritas 2: Penting (1–2 Sprint)
Widget SLA Dashboard: Menampilkan persentase pencapaian SLA, SLA Breach Count, dan MTTR (Mean Time to Resolve)[cite: 1].

Pembuatan Tabel Kategori ITIL: Membuat tabel itil_categories berstruktur hierarki (Contoh: Hardware → PC, Network → WiFi)[cite: 1].

Pembaruan Notifikasi WhatsApp: Mengambil nomor tujuan penerima (Head IT/SPV) secara dinamis dari basis data[cite: 1].

Dukungan Lampiran (Attachment): Menambahkan field upload bukti foto/tangkapan layar masalah pada formulir tiket[cite: 1].

🟢 Prioritas 3: Peningkatan (Backlog)
Integrasi otomatis antara tiket insiden dan artikel Knowledge Base[cite: 1].

Eskalasi otomatis via WhatsApp saat tiket mendekati status SLA Breach[cite: 1].

Fitur export laporan SLA ke format Excel/PDF untuk kebutuhan manajemen[cite: 1].

7. Panduan Instalasi & Konfigurasi Lingkungan
Persyaratan Sistem
- PHP >= 8.1
- Composer
- MySQL / MariaDB
- Node.js & NPM

Langkah Instalasi
1. Klon repositori ini:
Bash :
  - git clone [https://github.com/FaizalLeviansyah/itsm.git](https://github.com/FaizalLeviansyah/itsm.git)
  - cd itsm
2. Salin file lingkungan .env:
Bash :
  - cp .env.example .env

3. Sesuaikan variabel integrasi API SOC pada .env:
Code snippet :
  - SOC_API_URL=[https://soc-api.example.com](https://soc-api.example.com)
  - SOC_API_KEY=your-secret-api-key

4. Install dependensi PHP dan JavaScript:
Bash :
  - composer install
  - npm install && npm run build

5. Jalankan migrasi basis data beserta seeder:
Bash : 
  - php artisan migrate --seed

6. Jalankan server lokal:
Bash : 
  - php artisan serve
  - Dokumentasi ini dipelihara secara berkala oleh Faizal Leviansyah (IT Support Staff) - PT Amarin Ship Management.

8. Checklist Kesiapan & Pemeliharaan Operasional
Sebelum melakukan rilis penuh (production launch), pastikan daftar pemeliharaan berikut telah terverifikasi:

8.1 Konfigurasi Cron Job / Scheduler Server
Agar fitur sinkronisasi SOC pada SocIntegrationService.php berjalan otomatis di latar belakang tanpa menekan tombol manual[cite: 2], tambahkan entri berikut ke crontab server produksi:
Bash : 
  - * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

8.2 Verifikasi Lingkungan Production (.env)
Pastikan variabel lingkungan pada server produksi dikonfigurasi secara aman:

Code snippet : 
  - APP_ENV=production
  - APP_DEBUG=false
  - SOC_API_URL=[https://soc-api.example.com](https://soc-api.example.com)
  - SOC_API_KEY=your-actual-production-key

8.3 Pengujian Layanan Notifikasi
Lakukan tes pembuatan 1 tiket dummy untuk memastikan alur notifikasi email dan integrasi gateway WhatsApp berhasil terikirim[cite: 1].

Verifikasi bahwa nomor penerima supervisor disesuaikan dari data dinamis pengguna[cite: 1].

Dokumentasi ini dipelihara secara berkala oleh Faizal Leviansyah (IT Support Staff) - PT Amarin Ship Management.
