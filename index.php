<?php
/**
 * Beranda Dashboard - SIMAS X PPLG B
 * Sistem Informasi Manajemen Akademik & Siswa
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Sistem Informasi Terpadu";
$current_page = "home";
$base_url = "";
$ext = defined('IS_STATIC_BUILD') ? '.html' : '.php';

// Ambil seluruh data sistem informasi
$schedules = read_json('schedule.json');
$tugas_list = read_json('tugas.json', []);
$kas_data = read_json('kas.json', []);
$events_list = read_json('events.json', []);
$absensi_data = read_json('absensi.json', ['records' => []]);
$inventaris_data = read_json('inventaris.json', ['peminjaman' => []]);
$materi_list = read_json('materi.json', []);
$aspirasi_data = read_json('aspirasi.json', ['polling' => [], 'aspirasi' => []]);

// 1. Tugas pending
$tugas_pending = 0;
foreach ($tugas_list as $t) {
    if ($t['status'] !== 'Selesai') {
        $tugas_pending++;
    }
}

// 2. Saldo Kas
$saldo_kas = 0;
if (isset($kas_data['transaksi'])) {
    foreach ($kas_data['transaksi'] as $trx) {
        if ($trx['tipe'] === 'masuk') {
            $saldo_kas += $trx['nominal'];
        } elseif ($trx['tipe'] === 'keluar') {
            $saldo_kas -= $trx['nominal'];
        }
    }
}

// 3. Kehadiran siswa hari ini
$total_siswa = count($absensi_data['records']);
$hadir_count = 0;
foreach ($absensi_data['records'] as $r) {
    if ($r['status'] === 'Hadir') $hadir_count++;
}
$persen_hadir = ($total_siswa > 0) ? round(($hadir_count / $total_siswa) * 100) : 0;

// 4. Alat Lab yang sedang dipinjam
$alat_dipinjam = 0;
foreach ($inventaris_data['peminjaman'] ?? [] as $p) {
    if ($p['status'] === 'Dipinjam') $alat_dipinjam++;
}

// 5. Hari & Pelajaran
$daftar_hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$hari_inggris = date('l');
$hari_ini = $daftar_hari[$hari_inggris] ?? 'Senin';
$is_weekend = ($hari_ini === 'Sabtu' || $hari_ini === 'Minggu');
$jadwal_hari_ini = $schedules['biasa'][$hari_ini] ?? [];

// 6. Event terdekat
$event_terdekat = null;
$hari_tersisa_event = 999;
$today_str = date('Y-m-d');
foreach ($events_list as $ev) {
    if ($ev['tanggal'] >= $today_str) {
        $sisa = sisa_hari($ev['tanggal']);
        if ($sisa >= 0 && $sisa < $hari_tersisa_event) {
            $hari_tersisa_event = $sisa;
            $event_terdekat = $ev;
        }
    }
}

// Sapaan waktu
$jam_sekarang = (int)date('H');
if ($jam_sekarang >= 5 && $jam_sekarang < 11) {
    $sapaan = "Selamat Pagi";
} elseif ($jam_sekarang >= 11 && $jam_sekarang < 15) {
    $sapaan = "Selamat Siang";
} elseif ($jam_sekarang >= 15 && $jam_sekarang < 18) {
    $sapaan = "Selamat Sore";
} else {
    $sapaan = "Selamat Malam";
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="main-wrapper">
    <!-- Hero Banner -->
    <section class="hero-section">
        <span class="hero-badge"><span style="color:#fcbf49;">●</span> SIMAS X PPLG B</span>
        <h1 class="hero-title">Yo, What's up!</h1>
        <p class="hero-subtitle">
            <?= $sapaan ?> kawan-kawan! Selamat datang di <b>Sistem Informasi Manajemen Siswa & Akademik (SIMAS) Kelas X PPLG B</b>. Solusi digital terintegrasi untuk absensi harian, peminjaman lab RPL, bank materi koding, transparansi kas kelas, pemantauan tugas, dan musyawarah aspirasi siswa.
        </p>
        <div class="hero-actions">
            <a href="page/jadwal<?= $ext ?>" class="btn btn-primary btn-lg">Lihat Jadwal Pelajaran</a>
            <a href="page/absensi<?= $ext ?>" class="btn btn-secondary btn-lg">Cek Presensi Kelas (<?= $persen_hadir ?>%)</a>
            <a href="page/inventaris<?= $ext ?>" class="btn btn-outline btn-lg" style="color: #FFFFFF; border-color: rgba(255,255,255,0.4);">Peminjaman Alat Lab</a>
        </div>
    </section>

    <!-- Quick Stats Grid (4 Metrik Utama) -->
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--success-light); color: var(--success);">👥</div>
            <div class="stat-info">
                <div class="stat-label">Kehadiran Hari Ini</div>
                <div class="stat-value" style="color: var(--success);"><?= $hadir_count ?>/<?= $total_siswa ?> Siswa</div>
                <div class="stat-detail"><?= $persen_hadir ?>% Tingkat Kehadiran</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--danger-light); color: var(--danger);">📝</div>
            <div class="stat-info">
                <div class="stat-label">Tugas Pending</div>
                <div class="stat-value" style="color: var(--danger);"><?= $tugas_pending ?> Tugas</div>
                <div class="stat-detail">Perlu diselesaikan</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <div class="stat-label">Saldo KAS Kelas</div>
                <div class="stat-value"><?= format_rupiah($saldo_kas) ?></div>
                <div class="stat-detail">Transparan & Terbuka</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--warning-light); color: var(--accent-orange);">🛠️</div>
            <div class="stat-info">
                <div class="stat-label">Peminjaman Lab</div>
                <div class="stat-value" style="color: var(--accent-orange);"><?= $alat_dipinjam ?> Unit</div>
                <div class="stat-detail">Sedang digunakan siswa</div>
            </div>
        </div>
    </section>

    <!-- Interactive Schedule Section -->
    <section class="card">
        <div class="card-header">
            <div class="card-title">
                <span>🗓️</span> Jadwal KBM: Hari Ini (<?= $hari_ini ?>)
            </div>
            <div class="buttonjadwal" style="margin: 0;">
                <button type="button" class="buttonjadwalbiasa active" id="btnJadwalBiasa" onclick="switchSchedule('biasa')">
                    <b>Hari Biasa</b>
                </button>
                <button type="button" class="buttonjadwalupacara" id="btnJadwalUpacara" onclick="switchSchedule('upacara')">
                    <b>Hari Upacara</b>
                </button>
                <button type="button" class="btn btn-outline btn-sm" id="btnToggleFoto" onclick="toggleFotoJadwal()">
                    🖼️ Foto Asli
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Schedule Timetable Display -->
            <div id="scheduleTableContainer">
                <div class="day-pills">
                    <?php 
                    $days_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                    $active_day = in_array($hari_ini, $days_list) ? $hari_ini : 'Senin';
                    foreach ($days_list as $day_name): 
                    ?>
                        <button type="button" class="day-pill <?= $day_name === $active_day ? 'active' : '' ?>" onclick="selectDay('<?= $day_name ?>')">
                            <?= $day_name ?> <?= ($day_name === $hari_ini) ? '<span style="font-size:0.75rem; color:var(--accent-orange);">(Hari Ini)</span>' : '' ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Timetable Cards Container -->
                <div id="timetableCards" class="timetable-grid">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <!-- Original Image Display -->
            <div class="imgjadwal-wrapper" id="fotoJadwalContainer" style="display: none;">
                <p style="margin-bottom: 12px; font-weight: 600; color: var(--primary-navy);">
                    Dokumen Jadwal Resmi X PPLG B
                </p>
                <img src="image/jadwal_hari_biasa.jpg" alt="Jadwal Kelas di hari Biasa" class="imgjadwal">
            </div>
        </div>
    </section>

    <!-- 8 Modul Sistem Informasi Sekolah (Launchpad Grid) -->
    <section style="margin-top: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 style="font-size: 1.6rem; color: var(--primary-navy);">Modul Sistem Informasi X PPLG B</h2>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Akses cepat ke seluruh layanan dan sistem kesiswaan terpadu.</p>
            </div>
            <span class="badge badge-success">8 Modul Aktif</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            <!-- 1. Jadwal & Piket -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 10px;">⏰</div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 6px;">Jadwal & Piket</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 16px;">
                        Jadwal KBM mingguan, jam istirahat, serta jadwal regu piket kebersihan kelas.
                    </p>
                    <a href="page/jadwal<?= $ext ?>" class="btn btn-outline btn-sm">Buka Jadwal &rarr;</a>
                </div>
            </div>

            <!-- 2. Presensi Siswa -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 10px;">📋</div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 6px;">Presensi Siswa</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 16px;">
                        Pencatatan absensi harian (Hadir, Sakit, Izin, Alpa) dan pengajuan surat izin online.
                    </p>
                    <a href="page/absensi<?= $ext ?>" class="btn btn-outline btn-sm">Buka Presensi &rarr;</a>
                </div>
            </div>

            <!-- 3. Inventaris Lab -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 10px;">🛠️</div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 6px;">Peminjaman Lab</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 16px;">
                        Peminjaman peralatan lab RPL (crimping tool, tester LAN, Arduino, kabel HDMI, proyektor).
                    </p>
                    <a href="page/inventaris<?= $ext ?>" class="btn btn-outline btn-sm">Buka Inventaris &rarr;</a>
                </div>
            </div>

            <!-- 4. Bank Materi Koding -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 10px;">📚</div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 6px;">Bank Materi Koding</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 16px;">
                        Modul belajar Web, DPK, Git & GitHub, serta potongan kode siap copy untuk praktikum.
                    </p>
                    <a href="page/materi<?= $ext ?>" class="btn btn-outline btn-sm">Buka Materi &rarr;</a>
                </div>
            </div>

            <!-- 5. Aspirasi & Polling -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 10px;">🗳️</div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 6px;">Aspirasi & Polling</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 16px;">
                        Kotak saran kelas dan sistem pemungutan suara (voting) musyawarah mufakat.
                    </p>
                    <a href="page/aspirasi<?= $ext ?>" class="btn btn-outline btn-sm">Buka Aspirasi &rarr;</a>
                </div>
            </div>

            <!-- 6. Tugas & PR -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 10px;">📌</div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 6px;">Daftar Tugas</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 16px;">
                        Monitoring PR, tenggat waktu deadline, status pengerjaan, dan link pengumpulan.
                    </p>
                    <a href="page/tugas<?= $ext ?>" class="btn btn-outline btn-sm">Buka Tugas &rarr;</a>
                </div>
            </div>

            <!-- 7. Kas Kelas -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 10px;">💵</div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 6px;">Transparansi Kas</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 16px;">
                        Laporan arus kas masuk/keluar serta matriks status iuran mingguan 25 siswa.
                    </p>
                    <a href="page/kas<?= $ext ?>" class="btn btn-outline btn-sm">Buka Kas &rarr;</a>
                </div>
            </div>

            <!-- 8. Events & Agenda -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 2.2rem; margin-bottom: 10px;">🏆</div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 6px;">Events & Agenda</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 16px;">
                        Kalender kegiatan: Ujian Semester, Class Meeting, Study Tour, dan Expo karya siswa.
                    </p>
                    <a href="page/event<?= $ext ?>" class="btn btn-outline btn-sm">Buka Events &rarr;</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Struktur Organisasi Kelas -->
    <section class="card" style="margin-top: 36px;">
        <div class="card-header">
            <div class="card-title">
                <span>👥</span> Struktur Pengurus Kelas X PPLG B
            </div>
            <span class="badge badge-primary">Tahun Ajaran 2026/2027</span>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div style="background: var(--bg-white); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center;">
                    <span style="font-size: 0.78rem; color: var(--accent-orange); font-weight: 700; text-transform: uppercase;">Wali Kelas</span>
                    <h4 style="font-size: 1.05rem; margin-top: 4px;">Bpk. Budi Santoso, S.Kom</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Guru Kejuruan RPL</p>
                </div>
                <div style="background: var(--bg-white); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center;">
                    <span style="font-size: 0.78rem; color: var(--accent-orange); font-weight: 700; text-transform: uppercase;">Ketua Kelas</span>
                    <h4 style="font-size: 1.05rem; margin-top: 4px;">Jason Christopher</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Koordinator Utama</p>
                </div>
                <div style="background: var(--bg-white); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center;">
                    <span style="font-size: 0.78rem; color: var(--accent-orange); font-weight: 700; text-transform: uppercase;">Wakil Ketua</span>
                    <h4 style="font-size: 1.05rem; margin-top: 4px;">Aditya Pratama</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Wakil Koordinator</p>
                </div>
                <div style="background: var(--bg-white); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center;">
                    <span style="font-size: 0.78rem; color: var(--accent-orange); font-weight: 700; text-transform: uppercase;">Sekretaris</span>
                    <h4 style="font-size: 1.05rem; margin-top: 4px;">Annisa Rahmawati</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Administrasi & Presensi</p>
                </div>
                <div style="background: var(--bg-white); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center;">
                    <span style="font-size: 0.78rem; color: var(--accent-orange); font-weight: 700; text-transform: uppercase;">Bendahara</span>
                    <h4 style="font-size: 1.05rem; margin-top: 4px;">Dina Safitri</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Pengelola Kas Kelas</p>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Client Script for Schedule Switcher -->
<script>
const scheduleData = <?= json_encode($schedules, JSON_UNESCAPED_UNICODE) ?>;
let currentMode = 'biasa';
let currentSelectedDay = '<?= $active_day ?>';

function renderTimetable() {
    const container = document.getElementById('timetableCards');
    if (!container) return;

    let dayData = [];
    if (currentMode === 'upacara' && scheduleData.upacara && scheduleData.upacara[currentSelectedDay]) {
        dayData = scheduleData.upacara[currentSelectedDay];
    } else if (scheduleData.biasa && scheduleData.biasa[currentSelectedDay]) {
        dayData = scheduleData.biasa[currentSelectedDay];
    }

    if (dayData.length === 0) {
        container.innerHTML = `
            <div style="grid-column: 1 / -1; padding: 30px; text-align: center; color: var(--text-muted);">
                Tidak ada jadwal pelajaran untuk hari ini (${currentSelectedDay}).
            </div>
        `;
        return;
    }

    let html = '';
    dayData.forEach(item => {
        const isBreak = item.is_break ? 'is-break' : '';
        const isSpecial = item.is_special ? 'is-special' : '';
        const badge = item.is_break ? '☕ ISTIRAHAT' : (item.is_special ? '🎖️ UPACARA' : (item.ruang || 'Kelas'));

        html += `
            <div class="time-card ${isBreak} ${isSpecial}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span class="time-range">${item.jam}</span>
                    <span class="badge ${item.is_break ? 'badge-warning' : (item.is_special ? 'badge-danger' : 'badge-navy')}">${badge}</span>
                </div>
                <div class="time-subject">${item.mapel}</div>
                ${item.guru ? `<div class="time-meta"><span>👨‍🏫 ${item.guru}</span></div>` : ''}
            </div>
        `;
    });

    container.innerHTML = html;
}

function switchSchedule(mode) {
    currentMode = mode;
    const btnBiasa = document.getElementById('btnJadwalBiasa');
    const btnUpacara = document.getElementById('btnJadwalUpacara');

    if (mode === 'biasa') {
        btnBiasa.classList.add('active');
        btnUpacara.classList.remove('active');
    } else {
        btnUpacara.classList.add('active');
        btnBiasa.classList.remove('active');
    }

    renderTimetable();
}

function selectDay(dayName) {
    currentSelectedDay = dayName;
    document.querySelectorAll('.day-pill').forEach(btn => {
        if (btn.textContent.includes(dayName)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    renderTimetable();
}

function toggleFotoJadwal() {
    const foto = document.getElementById('fotoJadwalContainer');
    const btn = document.getElementById('btnToggleFoto');
    if (foto.style.display === 'none') {
        foto.style.display = 'block';
        btn.textContent = 'Hide Foto';
    } else {
        foto.style.display = 'none';
        btn.textContent = '🖼️ Foto Asli';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    renderTimetable();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
