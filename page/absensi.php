<?php
/**
 * Sistem Informasi Presensi & Kehadiran Siswa - X PPLG B
 */
require_once dirname(__DIR__) . '/includes/functions.php';

$page_title = "Presensi & Absensi Siswa";
$current_page = "absensi";
$base_url = "../";

$absensi_data = read_json('absensi.json', [
    'tanggal_aktif' => date('Y-m-d'),
    'records' => []
]);

$message = '';
$message_type = '';

// Handle Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_single') {
        $absen = (int)($_POST['absen'] ?? 0);
        $new_status = $_POST['status'] ?? 'Hadir';
        $keterangan = trim($_POST['keterangan'] ?? '-');

        foreach ($absensi_data['records'] as &$row) {
            if ($row['absen'] === $absen) {
                $row['status'] = $new_status;
                $row['keterangan'] = $keterangan ?: '-';
                break;
            }
        }
        write_json('absensi.json', $absensi_data);
        $message = "Status kehadiran absen #{$absen} berhasil diperbarui!";
        $message_type = 'success';
    } elseif ($action === 'set_all_hadir') {
        foreach ($absensi_data['records'] as &$row) {
            $row['status'] = 'Hadir';
            $row['keterangan'] = '-';
        }
        write_json('absensi.json', $absensi_data);
        $message = "Semua siswa berhasil diatur berstatus HADIR!";
        $message_type = 'success';
    } elseif ($action === 'ajukan_izin') {
        $nama = trim($_POST['nama'] ?? '');
        $status_izin = $_POST['status_izin'] ?? 'Izin';
        $alasan = trim($_POST['alasan'] ?? '');

        if ($nama && $alasan) {
            foreach ($absensi_data['records'] as &$row) {
                if ($row['nama'] === $nama) {
                    $row['status'] = $status_izin;
                    $row['keterangan'] = $alasan;
                    break;
                }
            }
            write_json('absensi.json', $absensi_data);
            $message = "Pengajuan {$status_izin} untuk {$nama} berhasil dicatat!";
            $message_type = 'success';
        }
    }
}

// Hitung rekap statistik kehadiran
$total_siswa = count($absensi_data['records']);
$count_hadir = 0;
$count_sakit = 0;
$count_izin = 0;
$count_alpa = 0;

foreach ($absensi_data['records'] as $r) {
    if ($r['status'] === 'Hadir') $count_hadir++;
    elseif ($r['status'] === 'Sakit') $count_sakit++;
    elseif ($r['status'] === 'Izin') $count_izin++;
    elseif ($r['status'] === 'Alpa') $count_alpa++;
}

$persen_hadir = ($total_siswa > 0) ? round(($count_hadir / $total_siswa) * 100) : 0;

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="main-wrapper">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1><span>📋</span> Presensi & Absensi Siswa</h1>
            <p>Sistem pencatatan kehadiran harian siswa Kelas X PPLG B secara transparan dan akurat.</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-outline" onclick="openModalIzin()">
                ✉️ Ajukan Izin / Sakit
            </button>
            <form method="POST" action="" onsubmit="return confirm('Atur status semua siswa menjadi HADIR?')">
                <input type="hidden" name="action" value="set_all_hadir">
                <button type="submit" class="btn btn-primary">
                    ⚡ Set Semua Hadir
                </button>
            </form>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--success-light); color: var(--success);">✔</div>
            <div class="stat-info">
                <div class="stat-label">Hadir Hari Ini</div>
                <div class="stat-value" style="color: var(--success);"><?= $count_hadir ?> Siswa</div>
                <div class="stat-detail">Tingkat kehadiran: <?= $persen_hadir ?>%</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--warning-light); color: var(--accent-orange);">💊</div>
            <div class="stat-info">
                <div class="stat-label">Sakit</div>
                <div class="stat-value" style="color: var(--accent-orange);"><?= $count_sakit ?> Siswa</div>
                <div class="stat-detail">Surat terkonfirmasi</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: rgba(0, 48, 73, 0.1); color: var(--primary-navy);">📝</div>
            <div class="stat-info">
                <div class="stat-label">Izin</div>
                <div class="stat-value" style="color: var(--primary-navy);"><?= $count_izin ?> Siswa</div>
                <div class="stat-detail">Keperluan resmi</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--danger-light); color: var(--danger);">✖</div>
            <div class="stat-info">
                <div class="stat-label">Tanpa Keterangan (Alpa)</div>
                <div class="stat-value" style="color: var(--danger);"><?= $count_alpa ?> Siswa</div>
                <div class="stat-detail"><?= $count_alpa === 0 ? 'Nihil (Bagus!)' : 'Perlu ditindaklanjuti' ?></div>
            </div>
        </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>🗓️</span> Data Kehadiran: <?= format_tanggal_id($absensi_data['tanggal_aktif']) ?>
            </div>
            <div class="day-pills" style="margin-bottom: 0;">
                <button type="button" class="day-pill active" onclick="filterAbsen('Semua')">Semua (<?= $total_siswa ?>)</button>
                <button type="button" class="day-pill" onclick="filterAbsen('Hadir')">Hadir (<?= $count_hadir ?>)</button>
                <button type="button" class="day-pill" onclick="filterAbsen('Sakit')">Sakit (<?= $count_sakit ?>)</button>
                <button type="button" class="day-pill" onclick="filterAbsen('Izin')">Izin (<?= $count_izin ?>)</button>
                <button type="button" class="day-pill" onclick="filterAbsen('Alpa')">Alpa (<?= $count_alpa ?>)</button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width: 70px; text-align: center;">No. Absen</th>
                            <th>Nama Lengkap Siswa</th>
                            <th style="width: 140px; text-align: center;">Status Kehadiran</th>
                            <th>Keterangan / Alasan</th>
                            <th style="width: 180px; text-align: center;">Ubah Status</th>
                        </tr>
                    </thead>
                    <tbody id="absenTableBody">
                        <?php foreach ($absensi_data['records'] as $siswa): 
                            $badge_class = 'badge-success';
                            if ($siswa['status'] === 'Sakit') $badge_class = 'badge-warning';
                            elseif ($siswa['status'] === 'Izin') $badge_class = 'badge-navy';
                            elseif ($siswa['status'] === 'Alpa') $badge_class = 'badge-danger';
                        ?>
                            <tr class="absen-row" data-status="<?= htmlspecialchars($siswa['status']) ?>">
                                <td style="text-align: center; font-weight: 700; color: var(--text-muted);">
                                    #<?= $siswa['absen'] ?>
                                </td>
                                <td>
                                    <b><?= htmlspecialchars($siswa['nama']) ?></b>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge <?= $badge_class ?>">
                                        <?= htmlspecialchars($siswa['status']) ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.9rem;">
                                    <?= htmlspecialchars($siswa['keterangan'] ?? '-') ?>
                                </td>
                                <td style="text-align: center;">
                                    <form method="POST" action="" style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                        <input type="hidden" name="action" value="update_single">
                                        <input type="hidden" name="absen" value="<?= $siswa['absen'] ?>">
                                        <select name="status" class="form-select" style="padding: 4px 8px; font-size: 0.82rem; width: auto;" onchange="this.form.submit()">
                                            <option value="Hadir" <?= $siswa['status'] === 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                                            <option value="Sakit" <?= $siswa['status'] === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                                            <option value="Izin" <?= $siswa['status'] === 'Izin' ? 'selected' : '' ?>>Izin</option>
                                            <option value="Alpa" <?= $siswa['status'] === 'Alpa' ? 'selected' : '' ?>>Alpa</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal Ajukan Izin / Sakit -->
<div class="modal-backdrop" id="modalIzin" onclick="if(event.target === this) closeModalIzin()">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>✉️ Formulir Permohonan Izin / Sakit</h3>
            <button class="modal-close" onclick="closeModalIzin()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="ajukan_izin">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Pilih Nama Siswa <span style="color:var(--danger)">*</span></label>
                    <select name="nama" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php foreach ($absensi_data['records'] as $s): ?>
                            <option value="<?= htmlspecialchars($s['nama']) ?>">
                                #<?= $s['absen'] ?> - <?= htmlspecialchars($s['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori <span style="color:var(--danger)">*</span></label>
                    <select name="status_izin" class="form-select" required>
                        <option value="Izin">Izin (Urusan Keluarga / Lomba / Acara Resmi)</option>
                        <option value="Sakit">Sakit (Demam / Rawat Jalan / Dokter)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Alasan & Keterangan Rinci <span style="color:var(--danger)">*</span></label>
                    <textarea name="alasan" class="form-control" rows="3" placeholder="Tuliskan keterangan sakit atau keperluan izin secara lengkap..." required></textarea>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModalIzin()">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Permohonan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function filterAbsen(status) {
    document.querySelectorAll('.day-pill').forEach(btn => {
        if (btn.textContent.includes(status)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    const rows = document.querySelectorAll('.absen-row');
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (status === 'Semua' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openModalIzin() {
    document.getElementById('modalIzin').classList.add('open');
}

function closeModalIzin() {
    document.getElementById('modalIzin').classList.remove('open');
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
