# ITSM System Update & Technical Changelog
**Project:** IT Service Management (ITSM) System  
**Date Range:** October 15 – 21, 2026  
**Document Status:** Final  

---

## 🇲🇨 Versi Bahasa Indonesia

### 📌 Ringkasan Eksekutif
Dokumen ini berisi catatan pembaruan teknis lengkap, penambahan fitur, perbaikan bug, dan optimasi alur kerja sistem ITSM yang telah diselesaikan sejak **15 hingga 21 Oktober 2026**. Pembaruan ini mencakup perbaikan sinkronisasi data pegawai murni dari API Master, fitur Advanced Smart Search, penandaan otomatis akun kapal (Vessel Badge), manajemen vendor pihak ketiga, modul PDF Knowledge Base, hingga optimasi alur kerja tiket.

---

### 🚀 Laporan Detail Pembaruan (15 - 21 Oktober 2026)

#### 1. Advanced Smart Search & Multi-Column Filtering
* **Latar Belakang:** Memudahkan Admin dan Teknisi dalam mencari tiket tidak hanya berdasarkan Nomor Tiket atau Judul, melainkan dari seluruh atribut terkait secara otomatis.
* **Detail Implementasi:**
  * **Query Multi-Relasi Cerdas (`TicketController@index` & `myTickets`):** Mengembangkan pencarian teks yang secara fleksibel memfilter data di berbagai tabel relasi:
    * **Atribut Tiket:** `ticket_number`, `title`, `description`, dan `vessel_name`.
    * **Requester (Pembuat Tiket):** `full_name`, `name`, `email`, `department`, `position`/`job_title`, dan `employee_number` (NIK).
    * **Teknisi Penanggung Jawab:** Mencakup penugasan individu (`assignee`) maupun tim (`assignedTechnicians`).
    * **Entitas Organisasi & Pihak Ketiga:** `company.name`, `vendor.name`, `category.name`, dan `priority.name`.
  * **Antarmuka Input Pencarian Baru:** Memperbarui tampilan placeholder pada UI filter menjadi `"Smart Search (ID, Title, Email, Dept, Name, Vessel, Vendor)..."`.

#### 2. Sinkronisasi Murni API Master & Konsistensi `full_name` Pegawai
* **Latar Belakang:** Mengatasi nama pengguna yang sebelumnya terpotong, berupa alamat email, atau format NIK numerik akibat belum terdaftarnya kolom `full_name` pada fillable model.
* **Detail Implementasi:**
  * **Dukungan Model `User.php`:** Menambahkan kolom `'full_name'` ke dalam array `$fillable` dan menyesuaikan accessor `getNameAttribute()`.
  * **Perbaikan Komando Sync (`SyncEmployees.php`):** Mengubah logika `updateOrCreate` agar secara otomatis meng-overwrite dan memperbarui nama pegawai lokal menggunakan string murni dari atribut `full_name` API Master (`http://api.amarin.biz.id/...`).
  * **Pembersihan Tampilan Blade:** Menghapus seluruh logika manipulasi string lama (`explode('@')` dan regex numerik NIK) pada `users.blade.php`, `tickets/index.blade.php`, dan `create.blade.php`.

#### 3. Penandaan Otomatis Identitas Kapal (`🚢 Vessel Badge Label`)
* **Latar Belakang:** Memberikan identifikasi visual yang jelas bagi tim IT Officer saat menerima tiket yang berasal dari kru/pegawai kapal.
* **Detail Implementasi:**
  * **Logika Deteksi Akun Kapal (`index.blade.php`):** Menambahkan pengecekan otomatis berdasarkan `source === 'vessel_api'`, kata kunci `vessel` pada departemen/jabatan, serta isi atribut `vessel_name`.
  * **Elemen Visual Badge Emas:** Menampilkan badge khusus berwarna keemasan dengan ikon kapal (`🚢 MT Queen Century`, `🚢 MT Soviana`, dll.) di bawah nama requester pada tabel utama tiket.

#### 4. Modul Manajemen Vendor & Pengelolaan Pihak Ketiga (`Vendor Management System`)
* **Latar Belakang:** Mendukung penanganan tiket yang didelegasikan atau dikerjakan oleh penyedia jasa/pihak ketiga (Vendor).
* **Detail Implementasi:**
  * **CRUD & Antarmuka Vendor:** Menambahkan halaman manajemen vendor lengkap dengan informasi kontak, PIC, dan nomor telepon.
  * **Integrasi Pembuatan Tiket:** Menambahkan opsi pemilihan vendor pada form pembuat tiket dan halaman edit.
  * **Visual Badge & Filter Vendor:** Menampilkan badge pihak ketiga berwarna ungu pada tabel tiket serta menyediakan filter dropdown khusus Vendor pada panel pencarian.

#### 5. Modul Knowledge Base dengan Dukungan Pengunggahan PDF
* **Latar Belakang:** Memungkinkan tim IT mempublikasikan panduan teknis dan dokumen Prosedur Operasional Standar (SOP) secara resmi.
* **Detail Implementasi:**
  * **Fitur Unggah PDF (`KnowledgeController`):** Menambahkan bidang unggah file lampiran PDF pada pembuatan dan pembaruan artikel panduan.
  * **Embedded Document Viewer:** Mengembangkan antarmuka pratinjau dokumen PDF secara langsung pada halaman detail artikel tanpa mengharuskan pengguna mengunduh berkas.

#### 6. Public Rating Module & Alur On-Behalf Tanpa Otentikasi (`Public Key/Token`)
* **Latar Belakang:** Memudahkan *requester* memberikan ulasan hasil pengerjaan tiket tanpa perlu melakukan login.
* **Detail Implementasi:**
  * **Token Unik 40 Karakter:** Pembuatan otomatis `public_token` (`Str::random(40)`) pada setiap tiket baru.
  * **Halaman Public Review (`ticket.public.review`):** Antarmuka khusus ulasan bintang (1-5) per teknisi dan pengajuan *Reopen* tiket.
  * **Penutupan Otomatis Tiket:** Mengubah status tiket dari `Resolved` menjadi `Closed` secara otomatis setelah ulasan dikirimkan.
  * **Pengiriman Notifikasi WhatsApp:** Mengirimkan tautan ulasan publik langsung ke WhatsApp pengguna begitu tiket dinyatakan selesai.

#### 7. Penyesuaian Prioritas & Rekalkulasi SLA oleh Admin
* **Latar Belakang:** Memberikan wewenang penuh kepada Admin/Teknisi dalam menentukan tingkat urgensi dan batas waktu pengerjaan.
* **Detail Implementasi:**
  * **Panel Kontrol Prioritas:** Form penyesuaian *Priority* dan *Impact* pada halaman detail tiket khusus Admin/Teknisi.
  * **Kalkulasi Ulang SLA (`SlaCalculator`):** Menghitung ulang target `due_date` secara otomatis saat prioritas diubah.
  * **Catatan Audit Trail:** Menyimpan riwayat perubahan prioritas beserta alasan penyesuaian di `TicketHistory`.

#### 8. Penugasan Multi-Teknisi & Ulasan Kinerja Independen
* **Latar Belakang:** Mendukung kolaborasi tim IT pada tiket yang membutuhkan lebih dari satu teknisi.
* **Detail Implementasi:**
  * **Tabel Pivot (`assignedTechnicians`):** Memungkinkan alokasi beberapa teknisi sekaligus dalam satu tiket.
  * **Ulasan Per Individu Teknisi (`rateAndClose`):** Memproses dan menampilkan nilai rating serta komentar per individu teknisi pada kartu ulasan terpisah.

---

## 🇬🇧 English Version

### 📌 Executive Summary
This document provides a comprehensive technical update, feature changelog, bug fixes, and workflow optimizations completed for the ITSM system between **October 15 and October 21, 2026**. Key enhancements include pure Master API employee synchronization, Advanced Smart Search, automatic Vessel Badge labeling, third-party Vendor Management, PDF Knowledge Base modules, and ticket workflow optimizations.

---

### 🚀 Detailed Update Report (October 15 - October 21, 2026)

#### 1. Advanced Smart Search & Multi-Column Filtering
* **Background:** Enables Admins and Technicians to search for tickets across all related attributes, beyond just Ticket IDs or Titles.
* **Implementation Details:**
  * **Smart Multi-Relation Query (`TicketController@index` & `myTickets`):** Implemented flexible text search querying across multiple database tables:
    * **Ticket Attributes:** `ticket_number`, `title`, `description`, and `vessel_name`.
    * **Requester Profile:** `full_name`, `name`, `email`, `department`, `position`/`job_title`, and `employee_number`.
    * **Assigned Technicians:** Searches both individual assignees (`assignee`) and team assignments (`assignedTechnicians`).
    * **Entities & Categories:** `company.name`, `vendor.name`, `category.name`, and `priority.name`.
  * **Updated UI Placeholder:** Enhanced search input placeholder to `"Smart Search (ID, Title, Email, Dept, Name, Vessel, Vendor)..."`.

#### 2. Master API Employee Synchronization & `full_name` Consistency
* **Background:** Fixed issues where employee names were truncated, displayed as email addresses, or formatted as numeric IDs due to missing fillable attributes.
* **Implementation Details:**
  * **Model Update (`User.php`):** Added `'full_name'` to the `$fillable` array and adjusted the `getNameAttribute()` accessor.
  * **Sync Command Refactoring (`SyncEmployees.php`):** Updated `updateOrCreate` logic to force-update local user records using raw `full_name` strings fetched directly from the Master API (`http://api.amarin.biz.id/...`).
  * **Blade Template Cleanup:** Removed all legacy string manipulation logic (`explode('@')` and NIK regex filters) across `users.blade.php`, `tickets/index.blade.php`, and `create.blade.php`.

#### 3. Automatic Vessel Identification (`🚢 Vessel Badge Label`)
* **Background:** Provides clear visual identification for IT Officers when handling tickets submitted by vessel crew members.
* **Implementation Details:**
  * **Vessel Account Detection (`index.blade.php`):** Implemented logic to automatically identify vessel tickets via `source === 'vessel_api'`, `vessel` keywords in department/position, or populated `vessel_name` attributes.
  * **Gold Visual Badge:** Displays a prominent golden badge with a ship icon (`🚢 MT Queen Century`, `🚢 MT Soviana`, etc.) beneath the requester's name in the main ticket list.

#### 4. Third-Party Vendor Management System
* **Background:** Facilitates management and tracking of tickets delegated to external service providers or third-party vendors.
* **Implementation Details:**
  * **Vendor CRUD Interface:** Integrated full vendor management views including contact details, PIC information, and phone numbers.
  * **Ticket Creation Integration:** Added vendor selection inputs during ticket creation and edit workflows.
  * **Vendor Visual Badges & Filters:** Added purple third-party badges on ticket cards and a dedicated vendor filter dropdown on the main dashboard.

#### 5. Knowledge Base Module with PDF Upload Support
* **Background:** Allows the IT department to officially publish technical guides and Standard Operating Procedures (SOPs).
* **Implementation Details:**
  * **PDF File Attachments (`KnowledgeController`):** Added PDF file upload capabilities for article creation and updates.
  * **Embedded Document Viewer:** Implemented an inline PDF viewer directly within the article view page, eliminating mandatory file downloads.

#### 6. Public Rating Module & Auth-Free Workflow (`Public Key/Token`)
* **Background:** Allows requesters to review and rate resolved tickets without logging into the portal.
* **Implementation Details:**
  * **40-Character Unique Token:** Automatically generates a `public_token` (`Str::random(40)`) upon ticket creation.
  * **Public Review Interface (`ticket.public.review`):** Dedicated page for 1-5 star ratings, per-technician feedback, and ticket reopening requests.
  * **Automated Ticket Closure:** Automatically updates ticket status from `Resolved` to `Closed` upon review submission.
  * **WhatsApp Notification Dispatch:** Sends public review links directly to user WhatsApp numbers upon ticket resolution.

#### 7. Priority Control & Admin SLA Recalculation
* **Background:** Empowers Admins and Technicians to calibrate issue urgency and SLA targets accurately.
* **Implementation Details:**
  * **Priority Adjustment Panel:** Added a dedicated priority and impact configuration box on the ticket detail view.
  * **SLA Recalculation (`SlaCalculator`):** Dynamically updates target `due_date` values whenever priorities are adjusted.
  * **Audit Trail Logging:** Records all priority modifications and justification notes in `TicketHistory`.

#### 8. Multi-Technician Assignments & Individual Feedback
* **Background:** Supports multi-technician collaboration on complex IT infrastructure issues.
* **Implementation Details:**
  * **Pivot Table Integration (`assignedTechnicians`):** Enables simultaneous assignment of multiple technicians to a single ticket.
  * **Per-Technician Performance Review (`rateAndClose`):** Captures and displays separate rating scores and feedback comments for each assigned technician.