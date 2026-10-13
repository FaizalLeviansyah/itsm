# Updated Features & Bug Fixes ITSM
**Tanggal:** Selasa, 13 Oktober 2026

1. **Public Review, Rating, & Auto-Close Tanpa Auth (Full English)**
   - Fitur penilaian kinerja teknisi secara publik via tautan WhatsApp & Dashboard Web tanpa perlu login (*token-based*).
   - Tiket otomatis berubah status menjadi **`Closed`** saat *requester* memberikan rating bintang.
   - Opsi **`Reopen`** jika kendala belum selesai (otomatis mengirim notifikasi WA ke Teknisi & Admin beserta alasan/remark pengerjaan ulang).
   - Fitur *Daily Reminder* otomatis via cron job (jam 09:00 WIB) untuk pengingat rating tiket `Resolved`.

2. **Perbaikan Logika Notifikasi WhatsApp On Behalf Of (Role Admin / Requester)**
   - Memperbaiki bug notifikasi di mana user ber-role Admin (misal: Mas Hendri) yang ditunjuk sebagai *requester* pada tiket *On Behalf Of* tidak menerima pesan rating.
   - Sistem kini memprioritaskan nomor HP *requester* terlepas dari apa pun *role* akun tersebut di sistem.

3. **Integrasi Nama Asli (Full Name) & Asal Perusahaan (Company)**
   - Standarisasi seluruh tampilan nama pengguna (termasuk pada card *Requester Info*, timeline history, dan detail tiket) menggunakan **`full_name`** sesuai data profil terbaru.
   - Menampilkan informasi asal PT/Company secara presisi pada setiap tiket.

4. **Fitur Penyesuaian Prioritas & Impact oleh Admin/Teknisi serta Revisi SLA**
   - Penyesuaian tingkat prioritas (*Priority*) dan skala dampak (*Impact Scale*) secara langsung oleh Admin/Teknisi.
   - Rekalkulasi otomatis pada tenggat waktu perbaikan (*SLA Due Date*) beserta pencatatan alasan perubahan.

5. **Multiple Assign Technician secara Independen & Kolaboratif**
   - Penugasan lebih dari satu teknisi sekaligus dalam satu tiket untuk penanganan teknis secara kolaboratif.

6. **Perbaikan Zona Waktu Sistem ke WIB (Asia/Jakarta)**
   - Sinkronisasi seluruh timestamp pencatatan riwayat (*History*), SLA, dan riwayat interaksi agar akurat sesuai Waktu Indonesia Barat.

7. **Proteksi Double-Click Submit & Loading State pada Tombol Aksi**
   - Mencegah pengiriman data ganda (*double submission*) serta menambahkan animasi loading interaktif pada tombol form aksi.

8. **Pemisahan Logika Halaman My Tickets dan All Tickets Berdasarkan Role**
   - Segmentasi tampilan data tiket pada navigasi *My Tickets* dan *All Tickets* yang disesuaikan secara dinamis dengan role akun (*User*, *Technician*, *Admin*).