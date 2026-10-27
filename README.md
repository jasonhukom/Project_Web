# SIMAS X PPLG B 🚀
### Sistem Informasi Manajemen Akademik & Siswa Terpadu

Sistem Informasi berbasis web untuk lingkungan sekolah kejuruan, khususnya **Kelas X PPLG B** (Pengembangan Perangkat Lunak dan GIM). Dirancang secara modular menggunakan **PHP 8 native** dan siap dijalankan baik di server lokal maupun **Vercel Serverless**.

---

## 🎨 Color Palette & Desain

Website ini menggunakan palet warna khas yang hangat, profesional, dan modern:
- **Navy Deep Blue (`#003049`, `#213B4E`)**: Header/navbar, judul utama, kartu, dan footer.
- **Warm Orange / Amber (`#F77F00`, hover `#FF9729`)**: Tombol aksi utama, jadwal aktif, badge status, dan aksen penting.
- **Warm Cream (`#EAE2B7`, cards `#FAF7EB`)**: Latar belakang yang nyaman di mata dan ramah kontras.
- **Warm Gold (`#FCBF49`)**: Aksen sub-header, border avatar, dan penanda highlight.
- **Typography**: Kombinasi **Outfit** (heading) dan **Roboto** (body).

---

## 🌟 8 Modul Sistem Informasi

### 1. 🏠 Executive Dashboard (`index.php`)
- **Sapaan Dinamis**: Menyesuaikan waktu (Pagi, Siang, Sore, Malam).
- **Statistik Cepat Terpadu**: Persentase kehadiran kelas hari ini, tugas aktif, saldo kas, peminjaman lab aktif, dan event terdekat.
- **Interactive Timetable Switcher**: Beralih instan antara **Hari Biasa** dan **Hari Upacara**.
- **Launchpad**: Akses langsung ke seluruh 8 modul sistem informasi.

### 2. 📅 Jadwal Pelajaran & Regu Piket (`page/jadwal.php`)
- **Jadwal KBM Harian**: Detail mata pelajaran per jam (Senin s/d Jumat), jam istirahat, nama guru pengampu, dan ruang kelas/lab.
- **Mode Upacara**: Jadwal upacara bendera khusus Senin.
- **Regu Piket Kebersihan**: Pembagian jadwal piket kebersihan harian (Senin - Jumat) dengan badge penanda "Tugas Hari Ini".
- **Lightbox Preview**: Modal untuk memperbesar dokumen foto jadwal asli.

### 3. 📋 Presensi & Absensi Siswa (`page/absensi.php`)
- **Sistem Kehadiran Harian**: Rekap Hadir, Sakit, Izin, dan Alpa per siswa dari 25 siswa X PPLG B.
- **Tingkat Kehadiran (%)**: Persentase kehadiran kelas hari ini secara otomatis.
- **Aksi Cepat**: Tombol *Set Semua Hadir* dan update status per siswa.
- **Permohonan Izin Online**: Formulir pengajuan surat izin/sakit siswa online.

### 4. 🛠️ Inventaris & Peminjaman Lab RPL (`page/inventaris.php`)
- **Manajemen Alat Praktik**: Katalog alat lab (Crimping Tool, Tester LAN, Arduino Starter Kit, Raspberry Pi, Kabel HDMI, Proyektor).
- **Log Peminjaman**: Pelacakan nama peminjam, tanggal pinjam, batas kembali, dan keperluan.
- **Pengembalian Alat**: Tombol konfirmasi pengembalian dengan auto-update stok inventaris.

### 5. 📚 Bank Modul & Materi Koding (`page/materi.php`)
- **Pusat Materi Belajar**: Kumpulan modul Pemrograman Web, DPK, Git & GitHub, dan Kecerdasan Buatan (AI).
- **Code Snippet Box**: Potongan kode referensi praktikum dilengkapi tombol **Salin Kode** (Copy to Clipboard).
- **Berbagi Materi**: Formulir untuk guru dan siswa membagikan materi pembelajaran baru.

### 6. 🗳️ Kotak Aspirasi & Polling Kelas (`page/aspirasi.php`)
- **Musyawarah Siswa**: Wadah penyampaian saran, kritik, dan usulan fasilitas kelas.
- **Sistem Polling (Voting)**: Pemungutan suara demokratis dengan live progress bar persentase suara.
- **Transparansi Respon**: Status aspirasi (Diterima, Sedang Dibahas, Selesai) beserta tanggapan dari wali kelas/pengurus.

### 7. 📝 Manajemen Tugas & PR (`page/tugas.php`)
- **Pencatatan Tugas Lengkap**: Mapel, judul, deskripsi, deadline, prioritas, dan link pengumpulan.
- **Filter Fleksibel**: Filter berdasarkan status (Belum Selesai, Dikerjakan, Selesai) dan mata pelajaran.
- **Pemberitahuan Tenggat**: Indikator otomatis untuk tugas mendesak (*urgent*), hari ini, atau lewat tenggat.

### 8. 💵 Transparansi Kas Kelas (`page/kas.php`)
- **Ringkasan Finansial**: Total kas masuk, total pengeluaran, saldo aktif saat ini, dan iuran mingguan (Rp 5.000/minggu).
- **Buku Kas & Riwayat Transaksi**: Catatan pemasukan dan pengeluaran terperinci.
- **Matriks Iuran Siswa**: Tabel 25 siswa X PPLG B dengan status pembayaran per minggu (M1 s/d M4) dengan one-click toggle.

---

## ☁️ Menjalankan di Vercel (PHP Serverless)

Proyek ini telah dilengkapi konfigurasi [`vercel.json`](vercel.json) dan router serverless [`api/index.php`](api/index.php) yang kompatibel dengan runtime `vercel-php@0.7.3`:

1. Buat akun di **[Vercel](https://vercel.com)** dan hubungkan akun GitHub Anda.
2. Klik **Add New Project** lalu pilih repository **`Project_Web`**.
3. Pada bagian **Build and Output Settings**, biarkan default (Vercel otomatis mendeteksi `vercel.json` dan fungsi di `api/index.php`).
4. Klik **Deploy**!
5. Website PHP Anda akan aktif di URL Vercel (misal: `https://project-web-xpplgb.vercel.app`).

> File `includes/functions.php` secara otomatis menggunakan cache storage `/tmp` di environment Vercel, sehingga sistem aman dari error *read-only filesystem*.

---

## 💻 Menjalankan Secara Lokal

```bash
cd Project_Web
php -S localhost:8000
```
Buka browser di: **`http://localhost:8000`**

---

## 📁 Struktur Direktori

```
Project_Web/
├── api/
│   └── index.php          # Entrypoint router Serverless PHP untuk Vercel
├── data/
│   ├── absensi.json       # Data presensi & kehadiran harian siswa
│   ├── aspirasi.json      # Data kotak saran & polling voting kelas
│   ├── events.json        # Data kalender kegiatan & agenda kelas
│   ├── inventaris.json    # Data katalog alat lab & log peminjaman
│   ├── kas.json           # Data transaksi kas & matriks iuran siswa
│   ├── materi.json        # Bank materi modul koding & snippet
│   ├── schedule.json      # Jadwal pelajaran KBM & regu piket
│   └── tugas.json         # Data penugasan PR & deadline
├── image/
│   ├── jadwal_hari_biasa.jpg
│   ├── logo.png
│   └── logo_profile.png
├── includes/
│   ├── footer.php         # Template footer modular
│   ├── functions.php      # Helper Vercel /tmp, Rupiah, tanggal ID, JSON
│   ├── header.php         # Template header & Google Fonts
│   └── navbar.php         # Navigasi responsif 8 modul
├── page/
│   ├── absensi.php        # Modul Presensi & Kehadiran Siswa
│   ├── aspirasi.php       # Modul Kotak Aspirasi & Polling Kelas
│   ├── event.php          # Modul Kalender Kegiatan & Events
│   ├── inventaris.php     # Modul Peminjaman & Inventaris Lab RPL
│   ├── jadwal.php         # Modul Jadwal Pelajaran & Piket
│   ├── kas.php            # Modul Transparansi Kas Kelas
│   ├── materi.php         # Modul Bank Modul & Materi Koding
│   └── tugas.php          # Modul Manajemen Tugas & PR
├── style/
│   └── style.css          # Design system lengkap
├── index.php              # Executive Dashboard Utama SIMAS
├── vercel.json            # Konfigurasi Serverless PHP untuk Vercel
└── README.md              # Dokumentasi lengkap sistem
```

---
Dibuat dengan ❤️ oleh **Kelas X PPLG B**
