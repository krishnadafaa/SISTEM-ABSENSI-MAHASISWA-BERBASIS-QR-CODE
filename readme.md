# Sistem Absensi Berbasis QR Code

Aplikasi sistem absensi akademik berbasis web yang dibangun menggunakan PHP Native. Sistem ini dirancang untuk mengotomatisasi pencatatan kehadiran mahasiswa secara *real-time* melalui pemindaian QR Code dinamis yang divalidasi dengan pencocokan kelas, batas waktu sesi, serta perekaman koordinat posisi (lintang dan bujur) perangkat pengguna.

## Hak Akses Pengguna (Role)

Sistem ini membagi hak akses ke dalam 3 tingkatan dengan fungsi yang spesifik:

### 1. Administrator (Admin)
Admin memiliki otoritas penuh untuk melakukan manajemen data master sistem (CRUD):
- Mengelola data master mahasiswa dan dosen.
- Mengelola data mata kuliah dan ruangan kelas.
- Mengelola seluruh jadwal perkuliahan di dalam sistem tanpa batasan.

### 2. Dosen
Dosen memiliki kendali terhadap proses belajar mengajar dan absensi kelas mereka sendiri:
- Mengelola jadwal perkuliahan secara terbatas (hanya jadwal yang ditugaskan kepada dosen yang bersangkutan).
- Membuat dan membuka sesi absensi sesuai dengan jadwal yang telah ditetapkan.
- Melakukan pembuatan (generate) QR Code untuk ditampilkan pada layar proyektor kelas.
- Mengontrol status sesi absensi secara penuh (membuka atau menutup sesi secara manual).
- Melihat dan mengunduh rekapitulasi data absensi mahasiswa per pertemuan.

### 3. Mahasiswa
Mahasiswa bertindak sebagai pengguna akhir yang melakukan konfirmasi kehadiran:
- Memantau statistik persentase kehadiran dan akumulasi status absensi (Hadir, Izin, Alfa) per mata kuliah secara *real-time*.
- Melakukan pemindaian (scan) QR Code melalui kamera perangkat (smartphone).
- Mengirimkan data spasial koordinat GPS (Latitude & Longitude) ke server saat proses absensi dilakukan.
- Mengelola keamanan akun mandiri dengan utilitas pembaruan data kredensial (Username dan Password).
- Melihat riwayat kronologis aktivitas absensi pribadi.

---

## Spesifikasi Teknologi

### Backend & Keamanan
- **Core Engine:** PHP Native (Arsitektur Prosedural).
- **Database Driver:** MySQLi Extension (`mysqli_query`, `mysqli_fetch_assoc`, `mysqli_real_escape_string`).
- **Kriptografi & Enkripsi:** MD5 Hashing Algorithm (untuk standardisasi keamanan kata sandi pada tabel `users`).
- **Format Pertukaran Data:** JSON (JavaScript Object Notation) sebagai jembatan komunikasi data asinkron antara server-side dan client-side.

### Frontend & Antarmuka Pengguna
- **Framework UI:** Bootstrap v5.3.0 (Responsive Layout & Tab Components).
- **Ikonografi:** Bootstrap Icons v1.11.1.
- **Tipografi:** Google Fonts - Outfit.

### Library & Web API Terintegrasi
- **Asynchronous JavaScript:** Fetch API (Pengganti AJAX konvensional untuk penanganan proses mutasi profil dan pengiriman token absensi tanpa *reload* halaman).
- **Library Pemindaian:** Html5-Qrcode Library (diintegrasikan via unpkg CDN untuk pengelolaan *stream* kamera secara langsung pada peramban).
- **Integrasi Perangkat:** HTML5 Geolocation API (utilitas penangkap koordinat `latitude` dan `longitude` dengan parameter `enableHighAccuracy: true` guna akurasi posisi objek).

---

## Struktur Direktori

Berikut adalah representasi struktur folder utama pada repositori ini:

```text
ABSENSI/
├── admin/                 # Modul dan logika khusus Administrator
│   └── dashboard.php      
├── config/                # Konfigurasi sistem utama
│   └── database.php       # Koneksi ke database (absensi_db)
├── dosen/                 # Modul dan logika khusus Dosen
│   └── dashboard.php      
├── mahasiswa/             # Modul dan logika khusus Mahasiswa
│   ├── dashboard.php      # Panel utama mahasiswa (Statistik, Kamera, Riwayat, & Profil)
│   ├── notif_absen.php    # Halaman informasi pasca-pemindaian (Success/Warning/Error)
│   └── proses_absen.php   # API Backend penangan validasi token, kelas, waktu, dan double-scan
├── auth.php               # Autentikasi dan manajemen sesi multi-role
├── index.php              # Halaman utama (Login interface)
└── setting.php            # Setting Profile