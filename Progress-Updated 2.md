# ITSM System Update & Technical Changelog
**Project:** IT Service Management (ITSM) System  
**Date Range:** October 12 – 15, 2026  
**Document Status:** Final  

---

## 🇲🇨 versi Bahasa Indonesia

### 📌 Ringkasan Eksekutif
Dokumen ini berisi catatan pembaruan teknis lengkap, penambahan fitur, perbaikan bug, dan optimasi alur kerja sistem ITSM yang telah diselesaikan sejak 12 hingga 15 Oktober 2026. Pembaruan ini mencakup integrasi notifikasi tanpa otentikasi, penanganan penugasan multi-teknisi, sistem kontrol SLA oleh Admin, hingga penyempurnaan antarmuka pengguna (UI) dan logika backend.

---

### 🚀 Laporan Detail Pembaruan (12 - 15 Oktober 2026)

#### 1. Modul Public Rating & Alur On-Behalf Tanpa Otentikasi (`Public Key/Token`)
* **Latar Belakang:** Memudahkan *requester* (khususnya pada kasus tiket *On-Behalf Of* yang dibuatkan oleh Admin/Teknisi) memberikan ulasan tanpa harus *login* ke sistem.
* **Detail Implementasi:**
  * **Token Unik 40 Karakter:** Menambahkan pembuatan `public_token` (`Str::random(40)`) otomatis pada setiap pembuatan tiket di database.
  * **Halaman Public Review (`ticket.public.review`):** Mengembangkan halaman ulasan publik yang aman, di mana *requester* dapat memberikan rating bintang (1-5) dan ulasan per teknisi atau mengajukan *Reopen* tiket.
  * **Eksekusi Penutupan Otomatis:** Tiket secara otomatis berubah status dari `Resolved` menjadi `Closed` segera setelah ulasan dikirimkan via tautan publik.
  * **Notifikasi WhatsApp & Email Transparan:** Sistem mengirimkan pesan WhatsApp otomatis berisi tautan publik langsung ke nomor *requester* saat status tiket diubah menjadi `Resolved`.

#### 2. Penyesuaian Prioritas & Kontrol SLA oleh Admin (`Admin Priority Control`)
* **Latar Belakang:** Mencegah *user* menentukan skala prioritas *High/Critical* secara sembarangan yang dapat merusak alur target SLA.
* **Detail Implementasi:**
  * **Form Kontrol Admin di Halaman Detail:** Menambahkan panel khusus pada `show.blade.php` bagi Admin/Teknisi untuk menyesuaikan *Priority* dan *Impact Scale*.
  * **Rekalkulasi SLA Otomatis (`SlaCalculator`):** Ketika Admin mengubah tingkat prioritas, sistem secara otomatis menghitung ulang `due_date` (SLA Target) berdasarkan jam kerja baku.
  * **Audit Trail Perubahan:** Setiap penyesuaian prioritas dicatat secara rinci di tabel `TicketHistory` beserta alasan penyesuaian (*Reason*).

#### 3. Penugasan Multi-Teknisi & Ulasan Kinerja Independen (`Multiple Assigned Technicians`)
* **Latar Belakang:** Penanganan tiket kompleks yang membutuhkan kerja sama lebih dari satu teknisi.
* **Detail Implementasi:**
  * **Dukungan Pivot Table (`assignedTechnicians`):** Memungkinkan Admin menugaskan lebih dari satu teknisi secara bersamaan pada satu tiket.
  * **Sistem Parsing Ulasan Multi-Teknisi (`rateAndClose`):**
    * Sistem memproses input ulasan per individu teknisi dan mengompilasi datanya ke dalam format terstruktur.
    * Menambahkan parser string pada blade view untuk memisahkan rating dan ulasan teknisi ($1, $2, dst.) secara rapi pada kartu visual masing-masing.
  * **Kartu Ulasan Tambahan (`Additional Overall Feedback`):** Menyediakan kontainer khusus ulasan umum (*Requester Feedback & Details*) berwarna biru di bawah kartu performa teknisi dan tepat di atas *Resolution Time*.

#### 4. Otomatisasi & Proteksi Pengiriman WhatsApp Closing (`TicketController@rateAndClose`)
* **Latar Belakang:** Menangani kasus di mana ulasan ditutup tanpa teks komentar dari pengguna, yang sebelumnya berisiko menggagalkan pengiriman WA API.
* **Detail Implementasi:**
  * **Fallback String Komentar:** Menambahkan logika penanganan otomatis di mana jika *requester* atau atasan tidak memberikan komentar teks, sistem otomatis menyisipkan teks default (`"respon cepat dan langsung di perbaiki"`) agar struktur string tidak pecah.
  * **Jaminan Eksekusi WA API:** Memastikan blok `try-catch` eksekusi `waService->notifyTicketClosed()` dipanggil secara independen tanpa tergantung pada isi teks komentar.

#### 5. Isolasi Query & Pemisahan Halaman `My Tickets` vs `All Tickets`
* **Latar Belakang:** Memastikan privasi dan relevansi data antara semua tiket sistem dan tiket milik pengguna yang sedang *login*.
* **Detail Implementasi:**
  * **Isolasi Logika Query (`myTickets`):**
    * Role `User`: Hanya dapat melihat tiket yang dibuat oleh dirinya sendiri (`requester_id = Auth::id()`).
    * Role `Technician`: Hanya melihat tiket yang ditugaskan kepada dirinya (baik via `assigned_to` maupun pivot `assignedTechnicians`) atau tiket yang belum memiliki penanggung jawab (`unassigned`).

#### 6. Integrasi Profil Employee API & Informasi PT Requester
* **Latar Belakang:** Kelengkapan data organisasi pada kartu profil *requester*.
* **Detail Implementasi:**
  * **Informasi Perusahaan / PT:** Menampilkan nama PT/Company secara otomatis pada *Requester Info Card* di halaman detail tiket yang ditarik dari *Additional Info* / Master Employee.
  * **Pembaruan Data Manual via PHP Tinker:** Melakukan koreksi nama dan atribut *requester* (seperti pembaruan profil ke *Rinus Indra Komara*) langsung pada database produksi via Laravel Tinker.

#### 7. Perbaikan Timezone (WIB - Jakarta / Hanoi UTC+7)
* **Latar Belakang:** Jam pada riwayat, komentar, dan tanggal tiket masih menampilkan zona waktu UTC.
* **Detail Implementasi:**
  * Memperbarui konfigurasi zona waktu aplikasi di `config/app.php` menjadi `'timezone' => 'Asia/Jakarta'`.
  * Memastikan seluruh helper `now()`, tanggal pembuatan, riwayat aktivitas, dan tenggat waktu SLA dirender sesuai Waktu Indonesia Barat.

#### 8. Proteksi Double-Click & Indikator Loading pada Tombol Aksi
* **Latar Belakang:** Mencegah *duplicate submission* akibat pengguna yang menekan tombol aksi berulang kali saat proses jaringan lambat.
* **Detail Implementasi:**
  * Penambahan skrip JavaScript/Alpine.js untuk me-disable tombol dan menampilkan efek *spinner loading* / status *"Memproses..."* pada aksi *Create Ticket*, *Update Status*, *Assign*, *Add Comment*, dan *Submit Rating*.

---

## 🇬🇧 English Version

### 📌 Executive Summary
This document provides a comprehensive technical update, feature changelog, bug fixes, and workflow optimizations completed for the ITSM system between October 12 and October 15, 2026. Key enhancements include authentication-free public rating workflows, multi-technician assignment architectures, SLA admin overrides, and UI/backend optimizations.

---

### 🚀 Detailed Update Report (October 12 - 15, 2026)

#### 1. Public Rating Module & Auth-Free On-Behalf Workflow (`Public Key/Token`)
* **Background:** Enables requesters (specifically for *On-Behalf Of* tickets created by Admins/Technicians) to review and rate service performance without logging into the platform.
* **Implementation Details:**
  * **40-Character Unique Token:** Added automatic generation of `public_token` (`Str::random(40)`) upon ticket creation in the database.
  * **Public Review Page (`ticket.public.review`):** Developed a secure public rating interface where requesters can submit 1-5 star ratings, per-technician feedback, or request a ticket *Reopen*.
  * **Automated Ticket Closure:** Tickets automatically transition from `Resolved` to `Closed` immediately upon submitting the review via the public link.
  * **Seamless WhatsApp & Email Alerts:** Automated WhatsApp messages containing the direct public link are dispatched to the requester's mobile number once the ticket reaches `Resolved` status.

#### 2. Dynamic Priority & Admin SLA Overrides (`Admin Priority Control`)
* **Background:** Prevents regular end-users from arbitrarily marking tickets as *High/Critical*, which distorts SLA fulfillment metrics.
* **Implementation Details:**
  * **Admin Control Panel on Detail View:** Added a dedicated adjustment box in `show.blade.php` for Admins/Technicians to calibrate *Priority* and *Impact Scale*.
  * **Automatic SLA Recalculation (`SlaCalculator`):** Adjusting the priority level triggers a dynamic recalculation of the `due_date` (SLA Target) based on configured business hours.
  * **Audit Trail Logging:** Every priority modification is logged in `TicketHistory` along with the mandatory adjustment reason.

#### 3. Multi-Technician Assignments & Independent Performance Review
* **Background:** Supports complex IT issues requiring collaborative effort from multiple technicians.
* **Implementation Details:**
  * **Pivot Table Integration (`assignedTechnicians`):** Enables Admins to assign multiple technicians simultaneously to a single ticket.
  * **Multi-Technician Review Parsing (`rateAndClose`):**
    * Processes individual feedback per technician and formats it into structured data.
    * Implemented custom string parsing on blade components to cleanly render individual ratings and notes inside respective technician cards.
  * **Additional Overall Feedback Card:** Added a dedicated blue container (*Requester Feedback & Details*) situated right beneath technician performance cards and directly above *Resolution Time*.

#### 4. WhatsApp Closing Notification Robustness (`TicketController@rateAndClose`)
* **Background:** Handled edge cases where users submitted ratings without written text, which previously caused potential failures in the WhatsApp API dispatch pipeline.
* **Implementation Details:**
  * **Fallback Comment Logic:** Integrated a fallback mechanism that automatically injects a default string (`"respon cepat dan langsung di perbaiki"`) if the text comment field is left empty, preserving notification string integrity.
  * **Guaranteed WA Execution:** Isolated the `try-catch` execution block for `waService->notifyTicketClosed()` to ensure notification delivery regardless of text payload presence.

#### 5. Strict Query Isolation: `My Tickets` vs `All Tickets`
* **Background:** Ensures strict data privacy and context relevance between system-wide tickets and user-specific tickets.
* **Implementation Details:**
  * **Query Scope Isolation (`myTickets`):**
    * `User` Role: Restricted strictly to tickets created by the authenticated user (`requester_id = Auth::id()`).
    * `Technician` Role: Filtered strictly to tickets explicitly assigned to them (via `assigned_to` or pivot `assignedTechnicians`) or unassigned tickets available in their pool.

#### 6. Employee API Integration & Company Profiling
* **Background:** Enhanced organizational data representation on requester profile components.
* **Implementation Details:**
  * **Company Attribution:** Automatically renders company/entity names in the *Requester Info Card* pulled from *Additional Info* / Employee Master API.
  * **Manual Database Patching via PHP Tinker:** Executed targeted data corrections (such as updating specific requester profiles to *Rinus Indra Komara*) directly in the production environment via Laravel Tinker.

#### 7. Timezone Standardization (WIB - Jakarta / UTC+7)
* **Background:** System timestamps, audit logs, and comments were previously displaying in UTC format.
* **Implementation Details:**
  * Updated application timezone configuration in `config/app.php` to `'timezone' => 'Asia/Jakarta'`.
  * Standardized all `now()` calls, creation timestamps, activity logs, and SLA deadlines to Western Indonesia Time (WIB).

#### 8. Double-Click Protection & Action Loading Indicators
* **Background:** Prevents duplicate form submissions caused by repeated button clicks during network latency.
* **Implementation Details:**
  * Implemented JavaScript/Alpine.js handlers to disable submit buttons and render loading spinners with *"Processing..."* states across *Create Ticket*, *Update Status*, *Assign*, *Add Comment*, and *Submit Rating* actions.
```eof

Dokumen di atas sudah mencakup seluruh pembaruan teknis secara lengkap dan mendetail dalam **dua versi bahasa (Indonesia & Inggris)** dengan format Markdown (`.md`). Dokumen ini siap Anda salin atau ekspor untuk keperluan dokumentasi internal maupun laporan ke manajemen!