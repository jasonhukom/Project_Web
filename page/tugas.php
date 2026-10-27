<?php
/**
 * Manajemen Tugas - Kelas X PPLG B
 */
require_once dirname(__DIR__) . '/includes/functions.php';

$page_title = "Daftar Tugas & PR";
$current_page = "tugas";
$base_url = "../";

$tugas_list = read_json('tugas.json', []);
$message = '';
$message_type = '';

// Handle Form Submissions (Add, Update Status, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $mapel = trim($_POST['mapel'] ?? '');
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');
        $prioritas = trim($_POST['prioritas'] ?? 'Sedang');
        $link = trim($_POST['link'] ?? '');

        if ($mapel && $judul && $deadline) {
            $new_tugas = [
                'id' => 'tugas-' . time(),
                'mapel' => $mapel,
                'judul' => $judul,
                'deskripsi' => $deskripsi,
                'deadline' => $deadline,
                'status' => 'Belum Selesai',
                'prioritas' => $prioritas,
                'link' => $link
            ];
            array_unshift($tugas_list, $new_tugas);
            write_json('tugas.json', $tugas_list);
            $message = "Tugas baru '{$judul}' berhasil ditambahkan!";
            $message_type = 'success';
        } else {
            $message = "Mohon lengkapi mata pelajaran, judul tugas, dan tanggal deadline.";
            $message_type = 'danger';
        }
    } elseif ($action === 'update_status') {
        $id = $_POST['id'] ?? '';
        $new_status = $_POST['status'] ?? 'Belum Selesai';
        foreach ($tugas_list as &$item) {
            if ($item['id'] === $id) {
                $item['status'] = $new_status;
                break;
            }
        }
        write_json('tugas.json', $tugas_list);
        $message = "Status tugas berhasil diperbarui!";
        $message_type = 'success';
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $tugas_list = array_values(array_filter($tugas_list, function($item) use ($id) {
            return $item['id'] !== $id;
        }));
        write_json('tugas.json', $tugas_list);
        $message = "Tugas berhasil dihapus.";
        $message_type = 'success';
    }
}

// Hitung statistik
$count_total = count($tugas_list);
$count_belum = 0;
$count_proses = 0;
$count_selesai = 0;

foreach ($tugas_list as $t) {
    if ($t['status'] === 'Selesai') $count_selesai++;
    elseif ($t['status'] === 'Sedang Dikerjakan') $count_proses++;
    else $count_belum++;
}

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="main-wrapper">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1><span>📝</span> Daftar Tugas & PR</h1>
            <p>Pantau tenggat waktu, deskripsi penugasan, dan status pengerjaan tugas kelas X PPLG B.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openModal()">
            ➕ Tambah Tugas Baru
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
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <div class="stat-label">Total Tugas</div>
                <div class="stat-value"><?= $count_total ?></div>
                <div class="stat-detail">Seluruh penugasan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--danger-light); color: var(--danger);">⏳</div>
            <div class="stat-info">
                <div class="stat-label">Belum Selesai</div>
                <div class="stat-value" style="color: var(--danger);"><?= $count_belum ?></div>
                <div class="stat-detail">Perlu dikerjakan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--warning-light); color: var(--warning);">⚡</div>
            <div class="stat-info">
                <div class="stat-label">Sedang Dikerjakan</div>
                <div class="stat-value" style="color: var(--accent-orange);"><?= $count_proses ?></div>
                <div class="stat-detail">Dalam proses</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--success-light); color: var(--success);">✅</div>
            <div class="stat-info">
                <div class="stat-label">Selesai</div>
                <div class="stat-value" style="color: var(--success);"><?= $count_selesai ?></div>
                <div class="stat-detail">Tuntas dikumpulkan</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px 20px;">
            <div style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
                <div class="day-pills" style="margin-bottom: 0;">
                    <button type="button" class="day-pill active" onclick="filterStatus('Semua')">Semua (<?= $count_total ?>)</button>
                    <button type="button" class="day-pill" onclick="filterStatus('Belum Selesai')">Belum Selesai (<?= $count_belum ?>)</button>
                    <button type="button" class="day-pill" onclick="filterStatus('Sedang Dikerjakan')">Sedang Dikerjakan (<?= $count_proses ?>)</button>
                    <button type="button" class="day-pill" onclick="filterStatus('Selesai')">Selesai (<?= $count_selesai ?>)</button>
                </div>
                <div style="min-width: 200px;">
                    <select id="filterMapel" class="form-select" onchange="applyFilters()">
                        <option value="Semua">Semua Mata Pelajaran</option>
                        <option value="Informatika">Informatika</option>
                        <option value="Dasar Program Keahlian">Dasar Program Keahlian</option>
                        <option value="Matematika">Matematika</option>
                        <option value="Bahasa Inggris">Bahasa Inggris</option>
                        <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                        <option value="IPAS">IPAS</option>
                        <option value="KKA">KKA</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tugas Grid -->
    <div class="tugas-grid" id="tugasContainer">
        <?php if (empty($tugas_list)): ?>
            <div style="grid-column: 1 / -1; background: var(--bg-white); padding: 48px; text-align: center; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <h3>🎉 Tidak ada tugas saat ini!</h3>
                <p style="color: var(--text-muted); margin-top: 8px;">Semua tugas telah diselesaikan atau belum ada tugas baru yang ditambahkan.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tugas_list as $item): 
                $sisa = sisa_hari($item['deadline']);
                $is_completed = ($item['status'] === 'Selesai');
                $is_urgent = (!$is_completed && $sisa <= 2 && $sisa >= 0);
                $is_overdue = (!$is_completed && $sisa < 0);

                $priority_class = 'badge-warning';
                if ($item['prioritas'] === 'Tinggi') $priority_class = 'badge-danger';
                if ($item['prioritas'] === 'Rendah') $priority_class = 'badge-navy';

                $status_class = 'badge-warning';
                if ($item['status'] === 'Selesai') $status_class = 'badge-success';
                if ($item['status'] === 'Belum Selesai') $status_class = 'badge-danger';
            ?>
                <div class="tugas-card <?= $is_completed ? 'completed' : '' ?>" 
                     data-status="<?= htmlspecialchars($item['status']) ?>"
                     data-mapel="<?= htmlspecialchars($item['mapel']) ?>">
                    <div>
                        <div class="tugas-header">
                            <span class="badge badge-navy"><?= htmlspecialchars($item['mapel']) ?></span>
                            <span class="badge <?= $priority_class ?>"><?= htmlspecialchars($item['prioritas']) ?></span>
                        </div>
                        <h3 class="tugas-title"><?= htmlspecialchars($item['judul']) ?></h3>
                        <p class="tugas-desc"><?= nl2br(htmlspecialchars($item['deskripsi'])) ?></p>
                        
                        <?php if (!empty($item['link'])): ?>
                            <a href="<?= htmlspecialchars($item['link']) ?>" target="_blank" class="btn btn-outline btn-sm" style="margin-bottom: 14px; display: inline-flex;">
                                🔗 Buka Link Tugas &rarr;
                            </a>
                        <?php endif; ?>
                    </div>

                    <div>
                        <div class="tugas-meta">
                            <div>
                                <span style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); display: block;">Deadline</span>
                                <span class="tugas-deadline <?= ($is_urgent || $is_overdue) ? 'urgent' : '' ?>">
                                    📅 <?= format_tanggal_id($item['deadline']) ?>
                                    <?php if ($is_overdue): ?>
                                        <span class="badge badge-danger">Lewat</span>
                                    <?php elseif ($is_urgent): ?>
                                        <span class="badge badge-warning"><?= $sisa === 0 ? 'Hari Ini!' : $sisa . ' Hari Lagi' ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <span class="badge <?= $status_class ?>"><?= htmlspecialchars($item['status']) ?></span>
                        </div>

                        <!-- Update Status & Action Controls -->
                        <div style="margin-top: 14px; display: flex; gap: 8px; align-items: center; justify-content: space-between;">
                            <form method="POST" action="" style="display: inline-flex; align-items: center; gap: 6px;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>">
                                <select name="status" class="form-select" style="padding: 4px 8px; font-size: 0.82rem; width: auto;" onchange="this.form.submit()">
                                    <option value="Belum Selesai" <?= $item['status'] === 'Belum Selesai' ? 'selected' : '' ?>>Belum Selesai</option>
                                    <option value="Sedang Dikerjakan" <?= $item['status'] === 'Sedang Dikerjakan' ? 'selected' : '' ?>>Dikerjakan</option>
                                    <option value="Selesai" <?= $item['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                </select>
                            </form>

                            <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px; font-size: 0.8rem;" title="Hapus Tugas">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Tambah Tugas Baru -->
<div class="modal-backdrop" id="modalTambahTugas" onclick="if(event.target === this) closeModal()">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>➕ Tambah Tugas / PR Baru</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Mata Pelajaran <span style="color:var(--danger)">*</span></label>
                    <select name="mapel" class="form-select" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <option value="Informatika">Informatika</option>
                        <option value="Dasar Program Keahlian">Dasar Program Keahlian (DPK)</option>
                        <option value="Matematika">Matematika</option>
                        <option value="Bahasa Inggris">Bahasa Inggris</option>
                        <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                        <option value="IPAS">IPAS</option>
                        <option value="KKA">KKA (Koding & AI)</option>
                        <option value="Seni Budaya">Seni Budaya</option>
                        <option value="Agama">Agama</option>
                        <option value="Pendidikan Pancasila">Pendidikan Pancasila</option>
                        <option value="Bahasa Jawa">Bahasa Jawa</option>
                        <option value="PJOK">PJOK</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Judul Tugas <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: Praktikum Array & Looping" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi & Instruksi Pengerjaan</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan detail petunjuk tugas atau nomor soal..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tanggal Deadline <span style="color:var(--danger)">*</span></label>
                        <input type="date" name="deadline" class="form-control" required value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tingkat Prioritas</label>
                        <select name="prioritas" class="form-select">
                            <option value="Sedang">Sedang</option>
                            <option value="Tinggi">Tinggi (Mendesak)</option>
                            <option value="Rendah">Rendah (Santai)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Link Pengumpulan / Referensi (Opsional)</label>
                    <input type="url" name="link" class="form-control" placeholder="https://classroom.google.com/...">
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Tugas</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let currentStatus = 'Semua';

function openModal() {
    document.getElementById('modalTambahTugas').classList.add('open');
}

function closeModal() {
    document.getElementById('modalTambahTugas').classList.remove('open');
}

function filterStatus(status) {
    currentStatus = status;
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
    const selectedMapel = document.getElementById('filterMapel').value;
    const cards = document.querySelectorAll('.tugas-card');

    cards.forEach(card => {
        const status = card.getAttribute('data-status');
        const mapel = card.getAttribute('data-mapel');

        const matchStatus = (currentStatus === 'Semua' || status === currentStatus);
        const matchMapel = (selectedMapel === 'Semua' || mapel === selectedMapel);

        if (matchStatus && matchMapel) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
