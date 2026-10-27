<?php
/**
 * Beranda - Portal Resmi Kelas X PPLG B
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Beranda Portal Kelas";
$current_page = "home";
$base_url = "";

// Ambil data untuk dashboard
$schedules = read_json('schedule.json');
$tugas_list = read_json('tugas.json');
$kas_data = read_json('kas.json');
$events_list = read_json('events.json');

// Hitung statistik dashboard
// 1. Tugas pending
$tugas_pending = 0;
foreach ($tugas_list as $t) {
    if ($t['status'] !== 'Selesai') {
        $tugas_pending++;
    }
}

// 2. Saldo Kas saat ini
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

// 3. Hari ini & Pelajaran Hari Ini
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

// 4. Event Terdekat
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

// Waktu sapaan
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
        <span class="hero-badge"><span style="color:#fcbf49;">●</span> X PPLG B PORTAL</span>
        <h1 class="hero-title">Yo, What's up!</h1>
        <p class="hero-subtitle">
            <?= $sapaan ?> kawan-kawan! Di sini kalian dapat mengakses Website resmi kelas, yang akan sangat membantu kalian dalam mengatur jadwal pelajaran, memantau tugas sekolah, transparansi uang kas, dan agenda kegiatan kelas.
        </p>
        <div class="hero-actions">
            <a href="page/jadwal.php" class="btn btn-primary btn-lg">Lihat Jadwal Lengkap</a>
            <a href="page/tugas.php" class="btn btn-secondary btn-lg">Cek Tugas Aktif (<?= $tugas_pending ?>)</a>
        </div>
    </section>

    <!-- Quick Stats Grid -->
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-info">
                <div class="stat-label">Hari Ini</div>
                <div class="stat-value"><?= $hari_ini ?></div>
                <div class="stat-detail"><?= $is_weekend ? 'Libur Akhir Pekan' : count($jadwal_hari_ini) . ' Sesi Pelajaran' ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-info">
                <div class="stat-label">Tugas Pending</div>
                <div class="stat-value"><?= $tugas_pending ?> Tugas</div>
                <div class="stat-detail">Perlu diselesaikan</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <div class="stat-label">Saldo KAS Kelas</div>
                <div class="stat-value"><?= format_rupiah($saldo_kas) ?></div>
                <div class="stat-detail">Transparan & Terkelola</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🎉</div>
            <div class="stat-info">
                <div class="stat-label">Event Terdekat</div>
                <div class="stat-value" style="font-size: 1.15rem;"><?= $event_terdekat ? htmlspecialchars($event_terdekat['judul']) : 'Belum Ada' ?></div>
                <div class="stat-detail">
                    <?= $event_terdekat ? ($hari_tersisa_event === 0 ? 'Hari Ini!' : $hari_tersisa_event . ' hari lagi') : 'Tidak ada agenda' ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Schedule Section -->
    <section class="card">
        <div class="card-header">
            <div class="card-title">
                <span>🗓️</span> Jadwal Pembelajaran Kelas
            </div>
            <div style="font-size: 0.9rem; color: var(--text-muted);">
                Pilih mode jadwal untuk melihat detail sesi
            </div>
        </div>
        <div class="card-body">
            <!-- Mode Buttons matching original index.html styling -->
            <div class="buttonjadwal">
                <button type="button" class="buttonjadwalbiasa active" id="btnJadwalBiasa" onclick="switchSchedule('biasa')">
                    <b>Hari Biasa</b>
                </button>
                <button type="button" class="buttonjadwalupacara" id="btnJadwalUpacara" onclick="switchSchedule('upacara')">
                    <b>Hari Upacara</b>
                </button>
                <button type="button" class="btn btn-outline" id="btnToggleFoto" onclick="toggleFotoJadwal()" style="margin-left: auto;">
                    🖼️ Tampilkan Foto Asli
                </button>
            </div>

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
                    <!-- Populated dynamically via JavaScript from JSON data -->
                </div>
            </div>

            <!-- Original Image Display (as provided in index.html) -->
            <div class="imgjadwal-wrapper" id="fotoJadwalContainer" style="display: none;">
                <p style="margin-bottom: 12px; font-weight: 600; color: var(--primary-navy);">
                    Dokumen Jadwal Resmi X PPLG B
                </p>
                <img src="image/jadwal_hari_biasa.jpg" alt="Jadwal Kelas di hari Biasa" class="imgjadwal">
            </div>
        </div>
    </section>

    <!-- Quick Navigation Features Grid -->
    <section style="margin-top: 36px;">
        <h2 style="font-size: 1.6rem; margin-bottom: 18px; color: var(--primary-navy);">Fitur Utama Kelas</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="text-align: center; padding: 28px 20px;">
                    <div style="font-size: 2.5rem; margin-bottom: 12px;">⏰</div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 8px;">Jadwal & Piket</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 16px;">
                        Cek jadwal pelajaran lengkap mingguan, jam istirahat, serta jadwal piket kebersihan kelas harian.
                    </p>
                    <a href="page/jadwal.php" class="btn btn-outline btn-sm">Buka Jadwal &rarr;</a>
                </div>
            </div>

            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="text-align: center; padding: 28px 20px;">
                    <div style="font-size: 2.5rem; margin-bottom: 12px;">📌</div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 8px;">Daftar Tugas</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 16px;">
                        Monitoring PR dan tugas sekolah, tenggat waktu (deadline), link pengumpulan, dan update status.
                    </p>
                    <a href="page/tugas.php" class="btn btn-outline btn-sm">Buka Tugas &rarr;</a>
                </div>
            </div>

            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="text-align: center; padding: 28px 20px;">
                    <div style="font-size: 2.5rem; margin-bottom: 12px;">💵</div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 8px;">Kas Kelas</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 16px;">
                        Laporan kas terbuka dan transparan: pemasukan, pengeluaran perlengkapan, serta status bayar per siswa.
                    </p>
                    <a href="page/kas.php" class="btn btn-outline btn-sm">Buka Kas &rarr;</a>
                </div>
            </div>

            <div class="card" style="margin-bottom: 0;">
                <div class="card-body" style="text-align: center; padding: 28px 20px;">
                    <div style="font-size: 2.5rem; margin-bottom: 12px;">🏆</div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 8px;">Events & Agenda</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 16px;">
                        Kalender agenda kegiatan: Ujian Semester, Class Meeting, Kunjungan Industri, dan pameran karya siswa.
                    </p>
                    <a href="page/event.php" class="btn btn-outline btn-sm">Buka Events &rarr;</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Info Kelas & Struktur Organisasi -->
    <section class="card" style="margin-top: 36px;">
        <div class="card-header">
            <div class="card-title">
                <span>👥</span> Struktur Organisasi Kelas X PPLG B
            </div>
            <span class="badge badge-primary">Tahun Ajaran 2026/2027</span>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div style="background: var(--bg-white); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center;">
                    <span style="font-size: 0.8rem; color: var(--accent-orange); font-weight: 700; text-transform: uppercase;">Wali Kelas</span>
                    <h4 style="font-size: 1.05rem; margin-top: 4px;">Bpk. Budi Santoso, S.Kom</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Guru Kejuruan RPL / PPLG</p>
                </div>
                <div style="background: var(--bg-white); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center;">
                    <span style="font-size: 0.8rem; color: var(--accent-orange); font-weight: 700; text-transform: uppercase;">Ketua Kelas</span>
                    <h4 style="font-size: 1.05rem; margin-top: 4px;">Jason Christopher</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Koordinator Utama</p>
                </div>
                <div style="background: var(--bg-white); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center;">
                    <span style="font-size: 0.8rem; color: var(--accent-orange); font-weight: 700; text-transform: uppercase;">Wakil Ketua</span>
                    <h4 style="font-size: 1.05rem; margin-top: 4px;">Aditya Pratama</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Wakil Koordinator</p>
                </div>
                <div style="background: var(--bg-white); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center;">
                    <span style="font-size: 0.8rem; color: var(--accent-orange); font-weight: 700; text-transform: uppercase;">Sekretaris</span>
                    <h4 style="font-size: 1.05rem; margin-top: 4px;">Annisa Rahmawati</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted);">Administrasi & Notulensi</p>
                </div>
                <div style="background: var(--bg-white); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center;">
                    <span style="font-size: 0.8rem; color: var(--accent-orange); font-weight: 700; text-transform: uppercase;">Bendahara</span>
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
let currentMode = 'biasa'; // 'biasa' or 'upacara'
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
        btn.textContent = 'Hide Foto Asli';
    } else {
        foto.style.display = 'none';
        btn.textContent = '🖼️ Tampilkan Foto Asli';
    }
}

// Initial render
document.addEventListener('DOMContentLoaded', () => {
    renderTimetable();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
