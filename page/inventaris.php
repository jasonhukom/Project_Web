<?php
/**
 * Sistem Informasi Peminjaman & Inventaris Lab RPL - X PPLG B
 */
require_once dirname(__DIR__) . '/includes/functions.php';

$page_title = "Inventaris & Peminjaman Alat Lab";
$current_page = "inventaris";
$base_url = "../";

$inv_data = read_json('inventaris.json', [
    'barang' => [],
    'peminjaman' => []
]);

$message = '';
$message_type = '';

// Handle Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'pinjam') {
        $barang_id = $_POST['barang_id'] ?? '';
        $peminjam = trim($_POST['peminjam'] ?? '');
        $tgl_pinjam = trim($_POST['tgl_pinjam'] ?? date('Y-m-d'));
        $tgl_kembali = trim($_POST['tgl_kembali'] ?? date('Y-m-d', strtotime('+2 days')));
        $keperluan = trim($_POST['keperluan'] ?? '');

        // Cari nama barang
        $nama_barang = 'Peralatan Lab';
        foreach ($inv_data['barang'] as &$b) {
            if ($b['id'] === $barang_id) {
                $nama_barang = $b['nama'];
                if ($b['stok'] > 0) {
                    $b['stok']--;
                    if ($b['stok'] === 0) $b['status'] = 'Dipinjam';
                }
                break;
            }
        }

        if ($barang_id && $peminjam && $keperluan) {
            $new_pinjam = [
                'id' => 'PINJAM-' . time(),
                'barang_id' => $barang_id,
                'nama_barang' => $nama_barang,
                'peminjam' => $peminjam,
                'tgl_pinjam' => $tgl_pinjam,
                'tgl_kembali' => $tgl_kembali,
                'keperluan' => $keperluan,
                'status' => 'Dipinjam'
            ];
            array_unshift($inv_data['peminjaman'], $new_pinjam);
            write_json('inventaris.json', $inv_data);
            $message = "Peminjaman '{$nama_barang}' atas nama {$peminjam} berhasil dicatat!";
            $message_type = 'success';
        } else {
            $message = "Mohon lengkapi formulir peminjaman alat lab.";
            $message_type = 'danger';
        }
    } elseif ($action === 'kembalikan') {
        $pinjam_id = $_POST['pinjam_id'] ?? '';
        foreach ($inv_data['peminjaman'] as &$p) {
            if ($p['id'] === $pinjam_id) {
                $p['status'] = 'Dikembalikan';
                // Kembalikan stok barang
                foreach ($inv_data['barang'] as &$b) {
                    if ($b['id'] === $p['barang_id']) {
                        $b['stok']++;
                        $b['status'] = 'Tersedia';
                        break;
                    }
                }
                break;
            }
        }
        write_json('inventaris.json', $inv_data);
        $message = "Peralatan lab telah berhasil dikembalikan ke inventaris!";
        $message_type = 'success';
    }
}

// Hitung statistik
$total_katalog = count($inv_data['barang']);
$count_dipinjam = 0;
$count_tersedia = 0;

foreach ($inv_data['barang'] as $b) {
    if ($b['stok'] > 0) $count_tersedia++;
    else $count_dipinjam++;
}

$pinjam_aktif = 0;
foreach ($inv_data['peminjaman'] as $p) {
    if ($p['status'] === 'Dipinjam') $pinjam_aktif++;
}

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="main-wrapper">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1><span>🛠️</span> Inventaris & Peminjaman Lab RPL</h1>
            <p>Manajemen peminjaman alat praktik koding, jaringan komputer, dan mikrokontroler IoT Kelas X PPLG B.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openModalPinjam()">
            ➕ Ajukan Peminjaman Alat
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
            <div class="stat-icon">📦</div>
            <div class="stat-info">
                <div class="stat-label">Total Jenis Alat</div>
                <div class="stat-value"><?= $total_katalog ?> Macam</div>
                <div class="stat-detail">Katalog lab RPL</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--success-light); color: var(--success);">✔</div>
            <div class="stat-info">
                <div class="stat-label">Tersedia di Lab</div>
                <div class="stat-value" style="color: var(--success);"><?= $count_tersedia ?> Alat</div>
                <div class="stat-detail">Siap dipinjam</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--warning-light); color: var(--accent-orange);">⏳</div>
            <div class="stat-info">
                <div class="stat-label">Sedang Dipinjam</div>
                <div class="stat-value" style="color: var(--accent-orange);"><?= $pinjam_aktif ?> Unit</div>
                <div class="stat-detail">Belum dikembalikan</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: rgba(0, 48, 73, 0.1); color: var(--primary-navy);">🔬</div>
            <div class="stat-info">
                <div class="stat-label">Kondisi Alat</div>
                <div class="stat-value">100% Layak</div>
                <div class="stat-detail">Terawat & Siap Pakai</div>
            </div>
        </div>
    </div>

    <!-- Tab View -->
    <div class="card">
        <div class="card-header">
            <div class="day-pills" style="margin-bottom: 0;">
                <button type="button" class="day-pill active" id="tabBtnLog" onclick="switchInvTab('log')">
                    📋 Log Peminjaman Aktif (<?= count($inv_data['peminjaman']) ?>)
                </button>
                <button type="button" class="day-pill" id="tabBtnKatalog" onclick="switchInvTab('katalog')">
                    🧰 Katalog Peralatan Lab (<?= $total_katalog ?>)
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- TAB 1: Peminjaman Log -->
            <div id="tabContentLog">
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Nama Barang / Alat</th>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Batas Kembali</th>
                                <th>Keperluan</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inv_data['peminjaman'])): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                        Belum ada riwayat peminjaman peralatan lab.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($inv_data['peminjaman'] as $p): 
                                    $is_dipinjam = ($p['status'] === 'Dipinjam');
                                ?>
                                    <tr>
                                        <td><b><?= htmlspecialchars($p['nama_barang']) ?></b></td>
                                        <td>👨‍🎓 <?= htmlspecialchars($p['peminjam']) ?></td>
                                        <td>📅 <?= format_tanggal_id($p['tgl_pinjam']) ?></td>
                                        <td>
                                            <span style="font-weight: 600; color: <?= $is_dipinjam ? 'var(--danger)' : 'var(--text-muted)' ?>;">
                                                ⏳ <?= format_tanggal_id($p['tgl_kembali']) ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 0.9rem; color: var(--text-muted);">
                                            <?= htmlspecialchars($p['keperluan']) ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge <?= $is_dipinjam ? 'badge-danger' : 'badge-success' ?>">
                                                <?= $is_dipinjam ? 'Sedang Dipinjam' : 'Dikembalikan' ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($is_dipinjam): ?>
                                                <form method="POST" action="" onsubmit="return confirm('Konfirmasi pengembalian alat ini?')">
                                                    <input type="hidden" name="action" value="kembalikan">
                                                    <input type="hidden" name="pinjam_id" value="<?= htmlspecialchars($p['id']) ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        ✔ Kembalikan
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: Katalog Barang Grid -->
            <div id="tabContentKatalog" style="display: none;">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 18px;">
                    <?php foreach ($inv_data['barang'] as $b): 
                        $is_avail = ($b['stok'] > 0);
                    ?>
                        <div style="background: var(--bg-white); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span class="badge badge-navy"><?= htmlspecialchars($b['kategori']) ?></span>
                                    <span class="badge <?= $is_avail ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $is_avail ? 'Tersedia' : 'Habis Dipinjam' ?>
                                    </span>
                                </div>
                                <h4 style="font-size: 1.15rem; color: var(--primary-navy); margin-bottom: 6px;">
                                    <?= htmlspecialchars($b['nama']) ?>
                                </h4>
                                <p style="font-size: 0.85rem; color: var(--text-muted);">
                                    Kondisi: <b><?= htmlspecialchars($b['kondisi']) ?></b>
                                </p>
                            </div>
                            <div style="border-top: 1px solid var(--border-light); padding-top: 12px; margin-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.9rem; font-weight: 700; color: var(--primary-navy);">
                                    Sisa Stok: <span style="color: var(--accent-orange);"><?= $b['stok'] ?> Unit</span>
                                </span>
                                <?php if ($is_avail): ?>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="openModalPinjamWith('<?= $b['id'] ?>')">
                                        Pinjam &rarr;
                                    </button>
                                <?php else: ?>
                                    <span class="badge badge-warning">Tidak Tersedia</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Ajukan Peminjaman -->
<div class="modal-backdrop" id="modalPinjam" onclick="if(event.target === this) closeModalPinjam()">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>➕ Formulir Peminjaman Alat Lab RPL</h3>
            <button class="modal-close" onclick="closeModalPinjam()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="pinjam">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Pilih Peralatan Lab <span style="color:var(--danger)">*</span></label>
                    <select name="barang_id" id="selectBarang" class="form-select" required>
                        <option value="">-- Pilih Alat yang Tersedia --</option>
                        <?php foreach ($inv_data['barang'] as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= ($b['stok'] == 0) ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($b['nama']) ?> (Stok: <?= $b['stok'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Siswa Peminjam <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="peminjam" class="form-control" placeholder="Contoh: Jason Christopher" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tanggal Peminjaman <span style="color:var(--danger)">*</span></label>
                        <input type="date" name="tgl_pinjam" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Batas Pengembalian <span style="color:var(--danger)">*</span></label>
                        <input type="date" name="tgl_kembali" class="form-control" value="<?= date('Y-m-d', strtotime('+2 days')) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keperluan Peminjaman <span style="color:var(--danger)">*</span></label>
                    <textarea name="keperluan" class="form-control" rows="3" placeholder="Contoh: Praktikum perakitan kabel LAN cross atau tugas projek IoT..." required></textarea>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModalPinjam()">Batal</button>
                    <button type="submit" class="btn btn-primary">Konfirmasi Pinjam</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function switchInvTab(tab) {
    const btnLog = document.getElementById('tabBtnLog');
    const btnKatalog = document.getElementById('tabBtnKatalog');
    const contentLog = document.getElementById('tabContentLog');
    const contentKatalog = document.getElementById('tabContentKatalog');

    if (tab === 'log') {
        btnLog.classList.add('active');
        btnKatalog.classList.remove('active');
        contentLog.style.display = 'block';
        contentKatalog.style.display = 'none';
    } else {
        btnKatalog.classList.add('active');
        btnLog.classList.remove('active');
        contentLog.style.display = 'none';
        contentKatalog.style.display = 'block';
    }
}

function openModalPinjam() {
    document.getElementById('modalPinjam').classList.add('open');
}

function closeModalPinjam() {
    document.getElementById('modalPinjam').classList.remove('open');
}

function openModalPinjamWith(barangId) {
    openModalPinjam();
    const sel = document.getElementById('selectBarang');
    if (sel) sel.value = barangId;
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
