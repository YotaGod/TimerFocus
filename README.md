# 🎯 Focus Timer

Aplikasi web untuk membantu pengguna menghitung dan melacak waktu fokus saat melakukan aktivitas seperti membaca, belajar, atau olahraga.

## ✨ Fitur Utama

- **Timer Fokus**: Stopwatch dengan kontrol start, pause, stop, dan reset
- **Input Aktivitas**: Catat jenis aktivitas yang sedang dilakukan
- **Riwayat Aktivitas**: Lihat semua sesi fokus yang telah dilakukan
- **Dashboard Statistik**: Visualisasi data fokus dengan grafik dan statistik
- **Filter & Pencarian**: Filter riwayat berdasarkan aktivitas dan tanggal
- **Responsive Design**: Tampilan yang responsif untuk desktop dan mobile

## 🛠️ Teknologi yang Digunakan

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+ (Procedural)
- **Database**: MySQL 5.7+
- **Visualisasi**: Chart.js
- **Styling**: Custom CSS dengan gradient dan glassmorphism

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- Browser modern dengan dukungan ES6+

## 🚀 Cara Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd focus-timer
```

### 2. Setup Database

1. Buat database MySQL baru dengan nama `focus_timer`
2. Import file `database/setup.sql` ke database Anda
3. Atau jalankan query SQL berikut:

```sql
CREATE DATABASE focus_timer;
USE focus_timer;

CREATE TABLE focus_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_name VARCHAR(255) NOT NULL,
    duration_seconds INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_created_at ON focus_history(created_at);
CREATE INDEX idx_activity_name ON focus_history(activity_name);
```

### 3. Konfigurasi Database

Edit file `config/database.php` dan sesuaikan dengan konfigurasi database Anda:

```php
$host = 'localhost';
$dbname = 'focus_timer';
$username = 'root';        // Sesuaikan dengan username MySQL Anda
$password = '';            // Sesuaikan dengan password MySQL Anda
```

### 4. Setup Web Server

1. Pastikan web server (Apache/Nginx) sudah berjalan
2. Letakkan file aplikasi di direktori web server
3. Akses aplikasi melalui browser: `http://localhost/focus-timer`

## 📁 Struktur File

```
focus-timer/
├── config/
│   └── database.php          # Konfigurasi database
├── database/
│   └── setup.sql             # Script setup database
├── assets/
│   └── css/
│       └── style.css         # Styling aplikasi
├── includes/
│   ├── header.php            # Header template
│   └── footer.php            # Footer template
├── home.php                  # Halaman utama dengan timer
├── history.php               # Halaman riwayat aktivitas
├── dashboard.php             # Halaman dashboard statistik
├── save_history.php          # API untuk menyimpan data
├── get_history.php           # API untuk mengambil riwayat
├── get_dashboard_data.php    # API untuk data dashboard
├── index.php                 # Redirect ke home.php
└── README.md                 # Dokumentasi ini
```

## 🎮 Cara Penggunaan

### 1. Menggunakan Timer

1. Buka halaman utama (`home.php`)
2. Masukkan jenis aktivitas yang akan dilakukan
3. Klik tombol "Mulai" untuk memulai timer
4. Gunakan tombol "Jeda" untuk menghentikan sementara
5. Klik "Selesai" untuk mengakhiri sesi dan menyimpan data

### 2. Melihat Riwayat

1. Klik menu "Riwayat" di navigasi
2. Gunakan filter untuk mencari aktivitas tertentu
3. Filter berdasarkan tanggal untuk melihat data periode tertentu
4. Navigasi menggunakan tombol pagination

### 3. Dashboard Statistik

1. Klik menu "Dashboard" di navigasi
2. Pilih periode yang ingin dilihat (Minggu/Bulan/Tahun)
3. Atau gunakan tanggal kustom untuk periode tertentu
4. Lihat grafik dan statistik produktivitas Anda

## ⌨️ Keyboard Shortcuts

- **Spacebar**: Start/Pause timer (ketika tidak sedang mengetik)
- **Enter**: Filter riwayat (di halaman history)

## 📊 Fitur Dashboard

### Statistik Umum

- Total jam fokus
- Jumlah sesi
- Rata-rata durasi per sesi
- Periode yang dipilih

### Grafik Visualisasi

1. **Tren Fokus Harian**: Line chart menunjukkan jam fokus per hari
2. **Distribusi Aktivitas**: Doughnut chart menunjukkan persentase waktu per aktivitas
3. **Jam Produktif**: Bar chart menunjukkan jam dengan fokus terbanyak

### Tabel Aktivitas Teratas

- Peringkat aktivitas berdasarkan total waktu
- Jumlah sesi per aktivitas
- Rata-rata durasi per aktivitas

## 🔧 Customization

### Mengubah Tema

Edit file `assets/css/style.css` untuk mengubah:

- Warna gradient background
- Warna tombol dan elemen UI
- Font dan ukuran teks
- Responsivitas untuk mobile

### Menambah Fitur

- Tambahkan validasi tambahan di `save_history.php`
- Implementasi sistem user/login
- Export data ke Excel/PDF
- Notifikasi browser untuk sesi fokus

## 🐛 Troubleshooting

### Masalah Koneksi Database

- Pastikan MySQL server berjalan
- Periksa konfigurasi di `config/database.php`
- Pastikan database `focus_timer` sudah dibuat

### Timer Tidak Berfungsi

- Pastikan JavaScript diaktifkan di browser
- Periksa console browser untuk error
- Pastikan semua file JavaScript ter-load dengan benar

### Grafik Tidak Muncul

- Pastikan Chart.js ter-load dengan benar
- Periksa koneksi internet untuk CDN Chart.js
- Periksa console browser untuk error JavaScript

## 📝 Lisensi

Proyek ini dibuat untuk tujuan pembelajaran dan penggunaan pribadi.

## 🤝 Kontribusi

Silakan buat issue atau pull request untuk saran perbaikan atau fitur baru.

---

**Dibuat dengan ❤️ untuk produktivitas yang lebih baik!**
