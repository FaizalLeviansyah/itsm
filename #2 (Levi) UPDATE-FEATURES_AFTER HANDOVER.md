# 📚 Dokumentasi Pengembangan & Pembaruan ITSM (Post-Handover)

Dokumentasi ini mencatat seluruh pembaruan arsitektur, modifikasi logika, dan penambahan fitur pada sistem ITSM setelah proses *handover* dari IT Manager (**Hendry Setio Prakoso**) kepada **Faizal Leviansyah**. Seluruh pembaruan ini difokuskan pada otomatisasi operasional IT dan sinkronisasi data yang solid.

---

## 🏢 1. Sistem Ticketing & Integrasi Data Perusahaan (Company ID)

Untuk menjaga integritas data dan menjadikan sistem ini sebagai *Single Source of Truth* tanpa perlu melakukan *data entry* ganda, pengelolaan data perusahaan ditiadakan dari sistem CRUD manual. Sebagai gantinya, pembuatan tiket (*Create Ticket*) kini menggunakan mekanisme injeksi data otomatis:

* **Session-Based Company Mapping:** Saat karyawan melakukan *login*, sistem akan secara otomatis menarik data dari API pusat dan mendeteksi perusahaan tempat karyawan tersebut bernaung berdasarkan *Company ID*.
  * `ID 1` = **PT Amarin Ship Management**
  * `ID 2` = **PT Caraka Tirta Pratama**
* **Otomatisasi Additional Info pada Tiket:** Informasi *Company Name* (berdasarkan *mapping* ID di atas) akan disisipkan (*injected*) secara otomatis di *background* ke dalam kolom `additional_info` setiap kali pengguna berhasil men-submit tiket baru. 
* **Tanpa Manual Input:** Pengguna tidak perlu memilih nama entitas perusahaan di *form* pembuatan tiket. Sistem memastikan data perusahaan selalu valid sesuai status kepegawaian terakhir.

---

## 🛡️ 2. Integrasi Service API SOC (Asset Management)

Telah dilakukan pembaruan struktur kode pada `SocIntegrationService.php` untuk menarik data status perangkat secara dinamis via REST API SOC dan mengintegrasikannya dengan modul *Asset Management* ITSM.

### Fitur Penarikan & Parsing Data:
* **Endpoint API Consumer:** Mengimplementasikan koneksi aman ke server SOC menggunakan autentikasi *header* `x-api-key`.
* **Smart Parsing (`parsePcBrand`):** Fungsi *parser* otomatis untuk mengekstrak data kompleks (misal: memisahkan *manufacturer* seperti Acer, HP, Asus dengan tipe *model* serta *Serial Number*) langsung dari string SOC.
* **Auto Asset Tagging:** Mengimplementasikan algoritma penomoran aset otomatis berbasis nama *hostname* (`SOC-[HOSTNAME]`) dan *auto-categorization* ke *Computer / Laptop*.

### 🔄 Otomatisasi Sinkronisasi & Auto-Register User (Resolusi Bug SQL Error)
* **Problem Awal:** Sinkronisasi sebelumnya mengalami *failed status* pada banyak aset baru. Hal ini dikarenakan *database* ITSM membutuhkan *Foreign Key* (ID User) pada kolom `assigned_to`, sementara SOC hanya mengirimkan nama *string* (`alias`).
* **Solusi (Auto-Register User Dummy):**
  * Ketika melakukan *Sync*, sistem kini mencari ketersediaan pengguna di ITSM berdasarkan *alias* dari SOC.
  * **Otomasi Pembuatan Akun:** Jika nama pengguna belum terdaftar, ITSM akan membuatkan profil akun *background* baru menggunakan format email terenkapsulasi (`[namaclean]@soc-sync.local`) dengan kombinasi *password default*.
  * **Seamless Assignment:** ID dari akun yang baru dibuat otomatis diikat ke perangkat. Hal ini menjadikan proses *Sync SOC* **100% berhasil** tanpa error *database*.
* **Penanganan Null State:** Aset yang pada aplikasi SOC belum memiliki *user assignment* (alias kosong), maka sistem ITSM juga secara identik akan melewatinya dan memberikan status *Unassigned* (`-`).

---

## 📝 3. Audit Trail & Riwayat Perubahan Aset

Sistem sekarang memiliki kemampuan pencatatan aktivitas yang jauh lebih rinci pada modul Aset.

* **Activity Tracking (`AssetHistory`):** Mengintegrasikan pembuatan rekaman otomatis ke tabel `asset_histories` setiap kali pembaruan ditarik menggunakan tombol **Sync SOC**.
* **Change Detection Engine:** Sistem akan membandingkan atribut krusial secara spesifik antara data *existing* dengan data dari SOC API (termasuk *hostname*, `antivirus_status`, `usb_status`, `windows_update_status`, status online SOC, dan lokasi `vessel_name`). Setiap adanya perubahan akan dicatat sebagai *log* transparan untuk audit infrastruktur.

---
*Dokumentasi ini dikelola dan diperbarui oleh **Faizal Leviansyah**.*