<?php
/**
 * Jadwal Pelajaran & Piket Kelas X PPLG B
 */
require_once dirname(__DIR__) . '/includes/functions.php';

$page_title = "Jadwal Pelajaran & Piket";
$current_page = "jadwal";
$base_url = "../";

$schedules = read_json('schedule.json');

// Hari saat ini
$daftar_hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$hari_ini = $daftar_hari[date('l')] ?? 'Senin';
$is_weekday = in_array($hari_ini, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="main-wrapper">
    <div class="page-header">
        <h1><span>📅</span> Jadwal Pelajaran & Piket</h1>
        <p>Jadwal lengkap kegiatan belajar mengajar (KBM) dan regu piket kebersihan Kelas X PPLG B.</p>
    </div>

    <!-- Mode Selector & Controls -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>⚡</span> Kontrol Mode Tampilan
            </div>
            <div class="buttonjadwal" style="margin: 0;">
                <button type="button" class="buttonjadwalbiasa active" id="btnJadwalBiasa" onclick="setMode('biasa')">
                    <b>Hari Biasa</b>
                </button>
                <button type="button" class="buttonjadwalupacara" id="btnJadwalUpacara" onclick="setMode('upacara')">
                    <b>Hari Upacara</b>
                </button>
                <button type="button" class="btn btn-outline" id="btnModalFoto" onclick="openFotoModal()">
                    🔍 Lihat Dokumen Asli
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Day Filter Pills -->
            <div class="day-pills">
                <button type="button" class="day-pill" onclick="filterDay('Semua')">Semua Hari</button>
                <?php 
                $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                foreach ($days as $day): 
                    $is_today = ($day === $hari_ini);
                ?>
                    <button type="button" class="day-pill <?= $is_today ? 'active' : '' ?>" onclick="filterDay('<?= $day ?>')">
                        <?= $day ?> <?= $is_today ? '<span style="font-size:0.75rem; color:var(--accent-orange);">(Hari Ini)</span>' : '' ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Schedule Containers -->
            <div id="scheduleGrid" style="display: flex; flex-direction: column; gap: 24px;">
                <!-- Populated dynamically via JS -->
            </div>
        </div>
    </div>

    <!-- Jadwal Piket Kebersihan -->
    <div class="card" style="margin-top: 32px;">
        <div class="card-header">
            <div class="card-title">
                <span>🧹</span> Regu Piket Kebersihan Kelas
            </div>
            <span class="badge badge-success">Tertib & Nyaman</span>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <?php 
                $piket_data = $schedules['piket'] ?? [];
                foreach ($days as $day): 
                    $is_piket_today = ($day === $hari_ini);
                    $anggota = $piket_data[$day] ?? [];
                ?>
                    <div style="background: var(--bg-white); border: 2px solid <?= $is_piket_today ? 'var(--accent-orange)' : 'var(--border-color)' ?>; border-radius: var(--radius-md); padding: 18px; box-shadow: var(--shadow-sm); position: relative;">
                        <?php if ($is_piket_today): ?>
                            <span class="badge badge-primary" style="position: absolute; top: -10px; right: 14px;">Tugas Hari Ini</span>
                        <?php endif; ?>
                        <h4 style="font-size: 1.15rem; color: var(--primary-navy); margin-bottom: 12px; border-bottom: 1px solid var(--border-light); padding-bottom: 6px;">
                            <?= $day ?>
                        </h4>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 8px;">
                            <?php foreach ($anggota as $nama): ?>
                                <li style="display: flex; align-items: center; gap: 8px; font-size: 0.92rem; color: var(--text-navy);">
                                    <span style="color: var(--accent-orange); font-weight: bold;">✔</span>
                                    <span><?= htmlspecialchars($nama) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Catatan Piket -->
            <div style="margin-top: 20px; background: rgba(0, 48, 73, 0.05); padding: 14px 18px; border-radius: var(--radius-sm); font-size: 0.88rem; color: var(--text-muted);">
                <b>📌 Kewajiban Petugas Piket:</b> Datang 15 menit sebelum bel masuk (06.45 WIB), menyapu lantai dan membuang sampah, merapikan meja guru & spidol whiteboard, serta mengunci pintu dan jendela setelah bel pulang.
            </div>
        </div>
    </div>
</main>

<!-- Modal Lightbox for Schedule Photo -->
<div class="modal-backdrop" id="fotoModal" onclick="if(event.target === this) closeFotoModal()">
    <div class="modal-dialog" style="max-width: 900px;">
        <div class="modal-header">
            <h3>Dokumen Jadwal Resmi X PPLG B</h3>
            <button class="modal-close" onclick="closeFotoModal()">&times;</button>
        </div>
        <div class="modal-body" style="text-align: center;">
            <img src="../image/jadwal_hari_biasa.jpg" alt="Jadwal Asli" style="max-height: 80vh; margin: 0 auto; border-radius: var(--radius-sm);">
        </div>
    </div>
</div>

<script>
const scheduleData = <?= json_encode($schedules, JSON_UNESCAPED_UNICODE) ?>;
let activeMode = 'biasa';
let activeDayFilter = '<?= $is_weekday ? $hari_ini : "Semua" ?>';

const allDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

function renderSchedule() {
    const container = document.getElementById('scheduleGrid');
    if (!container) return;

    const daysToRender = (activeDayFilter === 'Semua') ? allDays : [activeDayFilter];
    let html = '';

    daysToRender.forEach(day => {
        let items = [];
        if (activeMode === 'upacara' && scheduleData.upacara && scheduleData.upacara[day]) {
            items = scheduleData.upacara[day];
        } else if (scheduleData.biasa && scheduleData.biasa[day]) {
            items = scheduleData.biasa[day];
        }

        html += `
            <div style="background: var(--bg-white); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px;">
                    <h3 style="color: var(--primary-navy); font-size: 1.3rem;">
                        🗓️ Hari ${day} ${activeMode === 'upacara' ? '<span class="badge badge-danger">Mode Upacara</span>' : ''}
                    </h3>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">${items.length} Sesi Terjadwal</span>
                </div>
                <div class="timetable-grid">
        `;

        if (!items || items.length === 0) {
            html += `<p style="grid-column: 1 / -1; color: var(--text-muted); text-align: center; padding: 20px;">Tidak ada sesi pada hari ini.</p>`;
        } else {
            items.forEach(item => {
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
        }

        html += `
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function setMode(mode) {
    activeMode = mode;
    document.getElementById('btnJadwalBiasa').classList.toggle('active', mode === 'biasa');
    document.getElementById('btnJadwalUpacara').classList.toggle('active', mode === 'upacara');
    renderSchedule();
}

function filterDay(day) {
    activeDayFilter = day;
    document.querySelectorAll('.day-pill').forEach(btn => {
        if ((day === 'Semua' && btn.textContent.trim().startsWith('Semua')) || btn.textContent.includes(day)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    renderSchedule();
}

function openFotoModal() {
    document.getElementById('fotoModal').classList.add('open');
}

function closeFotoModal() {
    document.getElementById('fotoModal').classList.remove('open');
}

document.addEventListener('DOMContentLoaded', () => {
    // Sync initial active day pill
    document.querySelectorAll('.day-pill').forEach(btn => {
        if ((activeDayFilter === 'Semua' && btn.textContent.trim().startsWith('Semua')) || btn.textContent.includes(activeDayFilter)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    renderSchedule();
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
