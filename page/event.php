<?php
/**
 * Events & Agenda Kegiatan - X PPLG B
 */
require_once dirname(__DIR__) . '/includes/functions.php';

$page_title = "Events & Agenda Kegiatan";
$current_page = "event";
$base_url = "../";

$events_list = read_json('events.json', []);
$message = '';
$message_type = '';

// Handle Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $judul = trim($_POST['judul'] ?? '');
        $tanggal = trim($_POST['tanggal'] ?? '');
        $waktu = trim($_POST['waktu'] ?? 'Sepanjang Hari');
        $lokasi = trim($_POST['lokasi'] ?? 'Sekolah');
        $kategori = trim($_POST['kategori'] ?? 'Akademik');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($judul && $tanggal) {
            $new_event = [
                'id' => 'event-' . time(),
                'judul' => $judul,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'lokasi' => $lokasi,
                'kategori' => $kategori,
                'deskripsi' => $deskripsi,
                'status' => ($tanggal >= date('Y-m-d')) ? 'Akan Datang' : 'Selesai'
            ];
            $events_list[] = $new_event;
            // Sort by date ascending
            usort($events_list, function($a, $b) {
                return strcmp($a['tanggal'], $b['tanggal']);
            });
            write_json('events.json', $events_list);
            $message = "Agenda '{$judul}' berhasil ditambahkan ke kalender kelas!";
            $message_type = 'success';
        } else {
            $message = "Mohon lengkapi judul agenda dan tanggal kegiatan.";
            $message_type = 'danger';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $events_list = array_values(array_filter($events_list, function($item) use ($id) {
            return $item['id'] !== $id;
        }));
        write_json('events.json', $events_list);
        $message = "Agenda kegiatan berhasil dihapus.";
        $message_type = 'success';
    }
}

// Sort events by date
usort($events_list, function($a, $b) {
    return strcmp($a['tanggal'], $b['tanggal']);
});

$today_str = date('Y-m-d');
$count_upcoming = 0;
$count_past = 0;

foreach ($events_list as $ev) {
    if ($ev['tanggal'] >= $today_str) $count_upcoming++;
    else $count_past++;
}

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="main-wrapper">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1><span>🏆</span> Events & Agenda Kelas</h1>
            <p>Jadwal kegiatan penting kelas X PPLG B, ujian, lomba, class meeting, dan agenda sekolah.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openEventModal()">
            ➕ Tambah Agenda Baru
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-info">
                <div class="stat-label">Total Agenda</div>
                <div class="stat-value"><?= count($events_list) ?></div>
                <div class="stat-detail">Terdaftar di kalender</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--success-light); color: var(--success);">⏳</div>
            <div class="stat-info">
                <div class="stat-label">Akan Datang</div>
                <div class="stat-value" style="color: var(--accent-orange);"><?= $count_upcoming ?></div>
                <div class="stat-detail">Agenda mendatang</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--warning-light); color: var(--warning);">🎉</div>
            <div class="stat-info">
                <div class="stat-label">Telah Terlaksana</div>
                <div class="stat-value" style="color: var(--success);"><?= $count_past ?></div>
                <div class="stat-detail">Kegiatan selesai</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px 20px;">
            <div style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
                <div class="day-pills" style="margin-bottom: 0;">
                    <button type="button" class="day-pill active" onclick="filterStatus('Semua')">Semua Agenda (<?= count($events_list) ?>)</button>
                    <button type="button" class="day-pill" onclick="filterStatus('Akan Datang')">Akan Datang (<?= $count_upcoming ?>)</button>
                    <button type="button" class="day-pill" onclick="filterStatus('Selesai')">Selesai (<?= $count_past ?>)</button>
                </div>
                <div style="min-width: 200px;">
                    <select id="filterKategori" class="form-select" onchange="applyFilters()">
                        <option value="Semua">Semua Kategori</option>
                        <option value="Akademik">Akademik & Ujian</option>
                        <option value="Class Meeting">Class Meeting & Lomba</option>
                        <option value="Study Tour">Study Tour / Industri</option>
                        <option value="Pameran">Pameran & Expo</option>
                        <option value="Sekolah">Sekolah / Nasional</option>
                        <option value="Ulang Tahun">Ulang Tahun</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Event List -->
    <div class="event-list" id="eventContainer">
        <?php if (empty($events_list)): ?>
            <div style="background: var(--bg-white); padding: 48px; text-align: center; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <h3>🎉 Belum ada agenda kegiatan!</h3>
                <p style="color: var(--text-muted); margin-top: 8px;">Tambahkan event baru untuk mulai mengorganisir jadwal kegiatan kelas.</p>
            </div>
        <?php else: ?>
            <?php foreach ($events_list as $ev): 
                $sisa = sisa_hari($ev['tanggal']);
                $is_past = ($sisa < 0);
                $status_real = $is_past ? 'Selesai' : 'Akan Datang';

                $timestamp = strtotime($ev['tanggal']);
                $tgl_num = date('d', $timestamp);
                $bulan_nama = date('M', $timestamp);
            ?>
                <div class="event-card" 
                     data-status="<?= $status_real ?>"
                     data-kategori="<?= htmlspecialchars($ev['kategori']) ?>"
                     style="<?= $is_past ? 'opacity: 0.75; border-left-color: var(--text-muted);' : '' ?>">
                    
                    <div class="event-date-box" style="<?= $is_past ? 'background-color: var(--text-muted);' : '' ?>">
                        <div class="day-num"><?= $tgl_num ?></div>
                        <div class="month-text"><?= $bulan_nama ?></div>
                    </div>

                    <div class="event-content">
                        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                            <span class="badge badge-navy"><?= htmlspecialchars($ev['kategori']) ?></span>
                            <span class="badge <?= $is_past ? 'badge-success' : 'badge-warning' ?>"><?= $status_real ?></span>
                        </div>
                        <h3 class="event-title"><?= htmlspecialchars($ev['judul']) ?></h3>
                        <div class="event-details">
                            <span>🕒 <?= htmlspecialchars($ev['waktu']) ?></span>
                            <span>📍 <?= htmlspecialchars($ev['lokasi']) ?></span>
                            <span>📅 <?= format_tanggal_id($ev['tanggal']) ?></span>
                        </div>
                        <?php if (!empty($ev['deskripsi'])): ?>
                            <p class="event-desc"><?= nl2br(htmlspecialchars($ev['deskripsi'])) ?></p>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                        <div class="event-countdown">
                            <?php if ($is_past): ?>
                                <div class="countdown-num" style="color: var(--text-muted);">✓</div>
                                <div class="countdown-text">Terlaksana</div>
                            <?php elseif ($sisa === 0): ?>
                                <div class="countdown-num" style="color: var(--danger);">Hari Ini!</div>
                                <div class="countdown-text">Sedang Berlangsung</div>
                            <?php elseif ($sisa === 1): ?>
                                <div class="countdown-num" style="color: var(--accent-orange);">Besok</div>
                                <div class="countdown-text">Siapkan Diri</div>
                            <?php else: ?>
                                <div class="countdown-num"><?= $sisa ?></div>
                                <div class="countdown-text">Hari Lagi</div>
                            <?php endif; ?>
                        </div>

                        <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda ini?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($ev['id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 10px; font-size: 0.8rem;" title="Hapus Agenda">
                                🗑️ Hapus
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Tambah Event Baru -->
<div class="modal-backdrop" id="modalEvent" onclick="if(event.target === this) closeEventModal()">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>➕ Tambah Agenda / Event Baru</h3>
            <button class="modal-close" onclick="closeEventModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Agenda / Kegiatan <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: Class Meeting Futsal & E-Sports" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tanggal Pelaksanaan <span style="color:var(--danger)">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Waktu Pelaksanaan</label>
                        <input type="text" name="waktu" class="form-control" placeholder="Contoh: 08.00 - 15.00 WIB" value="08.00 - 15.00 WIB">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Kategori Agenda</label>
                        <select name="kategori" class="form-select">
                            <option value="Akademik">Akademik & Ujian</option>
                            <option value="Class Meeting">Class Meeting & Lomba</option>
                            <option value="Study Tour">Study Tour / Kunjungan Industri</option>
                            <option value="Pameran">Pameran & Expo Karya</option>
                            <option value="Sekolah">Sekolah / Nasional</option>
                            <option value="Ulang Tahun">Ulang Tahun Siswa</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Lab RPL 1 / Lapangan Sekolah" value="Ruang Kelas X PPLG B">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi & Catatan Kegiatan</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Informasi tambahan perlengkapan yang harus dibawa, dresscode, dll..."></textarea>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeEventModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Agenda</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let currentStatusFilter = 'Semua';

function openEventModal() {
    document.getElementById('modalEvent').classList.add('open');
}

function closeEventModal() {
    document.getElementById('modalEvent').classList.remove('open');
}

function filterStatus(status) {
    currentStatusFilter = status;
    document.querySelectorAll('.day-pill').forEach(btn => {
        if (btn.textContent.includes(status)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyFilters();
}

function applyFilters() {
    const selectedKategori = document.getElementById('filterKategori').value;
    const cards = document.querySelectorAll('.event-card');

    cards.forEach(card => {
        const status = card.getAttribute('data-status');
        const kategori = card.getAttribute('data-kategori');

        const matchStatus = (currentStatusFilter === 'Semua' || status === currentStatusFilter);
        const matchKategori = (selectedKategori === 'Semua' || kategori === selectedKategori);

        if (matchStatus && matchKategori) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
