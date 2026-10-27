# Website Resmi Kelas X PPLG B 🚀

Website portal terpadu untuk siswa **Kelas X PPLG B** (Pengembangan Perangkat Lunak dan GIM). Dirancang untuk mempermudah pengaturan jadwal pelajaran, pemantauan tugas sekolah (PR), transparansi pengelolaan kas kelas, dan kalender kegiatan kelas.

---

## 🎨 Color Palette & Desain

Website ini menggunakan palet warna khas yang hangat, profesional, dan modern:
- **Navy Deep Blue (`#003049`, `#213B4E`)**: Digunakan untuk navigasi utama, teks judul, dan aksen profesional.
- **Warm Orange / Amber (`#F77F00`, hover `#FF9729`)**: Warna aksen utama untuk tombol interaktif, highlight status, dan badge.
- **Warm Cream (`#EAE2B7`, cards `#FAF7EB`)**: Latar belakang yang ramah di mata dan nyaman untuk sesi belajar panjang.
- **Warm Gold (`#FCBF49`)**: Aksen sub-header dan penanda penting.
- **Typography**: Kombinasi **Outfit** untuk headline modern dan **Roboto** untuk body text yang mudah dibaca.

---

## 🌟 Fitur Utama

### 1. 🏠 Beranda & Dashboard Pintar (`index.php`)
- **Sapaan Dinamis**: Menyesuaikan waktu (Pagi, Siang, Sore, Malam).
- **Statistik Cepat**: Jumlah sesi hari ini, tugas yang perlu diselesaikan, saldo kas terkini, dan hitung mundur agenda terdekat.
- **Interactive Timetable Switcher**: Tombol instan untuk beralih antara **Hari Biasa** dan **Hari Upacara**, serta filter per hari.
- **Dokumen Asli**: Fitur toggle untuk melihat gambar jadwal asli (`jadwal_hari_biasa.jpg`).
- **Struktur Organisasi**: Wali Kelas, Ketua Kelas, Wakil, Sekretaris, dan Bendahara.

### 2. 📅 Jadwal Pelajaran & Regu Piket (`page/jadwal.php`)
- **Jadwal Harian**: Detail mata pelajaran per jam (Senin s/d Jumat), jam istirahat, nama guru pengampu, dan ruang kelas/lab.
- **Mode Hari Upacara**: Jadwal upacara bendera khusus Senin.
- **Regu Piket Kebersihan**: Pembagian jadwal piket kebersihan harian (Senin - Jumat) dengan badge penanda "Tugas Hari Ini".
- **Lightbox Preview**: Modal untuk memperbesar dokumen foto jadwal asli.

### 3. 📝 Manajemen Tugas & PR (`page/tugas.php`)
- **Pencatatan Tugas Lengkap**: Mata pelajaran, judul tugas, deskripsi instruksi, deadline, tingkat prioritas, dan tautan pengumpulan (Google Classroom / GitHub).
- **Filter Fleksibel**: Filter berdasarkan status (Semua, Belum Selesai, Sedang Dikerjakan, Selesai) dan mata pelajaran.
- **Pemberitahuan Tenggat**: Indikator otomatis untuk tugas mendesak (*urgent*), hari ini, atau lewat tenggat.
- **Tambah & Kelola Tugas**: Formulir modal interaktif untuk menambahkan tugas baru dan mengubah status pengerjaan secara langsung.

### 4. 💵 Transparansi Kas Kelas (`page/kas.php`)
- **Ringkasan Finansial**: Total kas masuk, total pengeluaran, saldo aktif saat ini, dan besaran iuran mingguan (Rp 5.000/minggu).
- **Buku Kas & Riwayat Transaksi**: Catatan pemasukan dan pengeluaran secara terperinci dengan tanggal, kategori, keterangan, nominal, dan petugas pencatat.
- **Matriks Iuran Siswa**: Tabel 25 siswa X PPLG B dengan status pembayaran per minggu (Minggu 1 s/d Minggu 4) yang dapat diubah statusnya dengan satu klik.
- **Pencatatan Transaksi Baru**: Form modal untuk bendahara mencatat pengeluaran perlengkapan atau pemasukan iuran.

### 5. 🏆 Events & Agenda Kelas (`page/event.php`)
- **Kalender Kegiatan**: Ujian Semester, Class Meeting, Kunjungan Industri ke software house, pameran karya siswa, dan hari besar.
- **Live Countdown**: Menampilkan sisa hari menuju kegiatan secara otomatis (*Hari Ini*, *Besok*, *X Hari Lagi*, atau *Terlaksana*).
- **Kategori & Filter**: Filter agenda berdasarkan status (Akan Datang / Selesai) dan kategori (Akademik, Lomba, Study Tour, Pameran, dll.).
- **Tambah Agenda**: Form modal untuk mendaftarkan kegiatan kelas baru.

---

## 🛠️ Arsitektur & Teknologi

- **Backend**: Native PHP 8.x (arsitektur modular: `includes/header.php`, `includes/navbar.php`, `includes/footer.php`, `includes/functions.php`).
- **Data Persistence**: JSON Flat-File storage di folder `data/` (`schedule.json`, `tugas.json`, `kas.json`, `events.json`). Tidak memerlukan setup database MySQL rumit, langsung siap pakai!
- **Frontend / Styling**: Vanilla CSS Modern dengan CSS Variables, CSS Grid, Flexbox, Glassmorphism, dan dukungan responsif penuh untuk ponsel dan desktop.

---

## 🚀 Cara Menjalankan Website

1. Pastikan **PHP** (versi 8.0+) telah terpasang di komputer Anda. Cek dengan perintah:
   ```bash
   php -v
   ```

2. Buka terminal di dalam folder proyek (`Project_Web`):
   ```bash
   cd Project_Web
   ```

3. Jalankan server bawaan PHP:
   ```bash
   php -S localhost:8000
   ```

4. Buka peramban (browser) dan akses:
   ```
   http://localhost:8000
   ```

---

## 📁 Struktur Direktori

```
Project_Web/
├── data/
│   ├── events.json        # Data agenda dan kegiatan kelas
│   ├── kas.json           # Data transaksi kas dan matriks iuran siswa
│   ├── schedule.json      # Jadwal pelajaran dan pembagian regu piket
│   └── tugas.json         # Data penugasan dan deadline tugas
├── image/
│   ├── jadwal_hari_biasa.jpg  # Foto dokumen jadwal resmi
│   ├── logo.png               # Logo Kelas X PPLG B
│   └── logo_profile.png       # Avatar profil kelas
├── includes/
│   ├── footer.php         # Template footer modular
│   ├── functions.php      # Fungsi pembantu (format Rupiah, tanggal ID, JSON helper)
│   ├── header.php         # Template header dan meta tag
│   └── navbar.php         # Navigasi responsif dengan menu mobile
├── page/
│   ├── event.php          # Halaman kalender kegiatan & countdown
│   ├── jadwal.php         # Halaman jadwal pelajaran & piket kelas
│   ├── kas.php            # Halaman transparansi kas kelas & matriks siswa
│   └── tugas.php          # Halaman manajemen tugas & PR
├── style/
│   └── style.css          # Design system lengkap dengan palet khas
├── index.php              # Beranda & dashboard utama kelas
└── README.md              # Dokumentasi proyek
```

---
Dibuat dengan ❤️ oleh **Kelas X PPLG B**
