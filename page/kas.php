<?php
/**
 * Transparansi Kas Kelas - X PPLG B
 */
require_once dirname(__DIR__) . '/includes/functions.php';

$page_title = "Transparansi Kas Kelas";
$current_page = "kas";
$base_url = "../";

$kas_data = read_json('kas.json', [
    'nominal_mingguan' => 5000,
    'transaksi' => [],
    'siswa' => []
]);

$message = '';
$message_type = '';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_trx') {
        $tipe = $_POST['tipe'] ?? 'masuk';
        $kategori = trim($_POST['kategori'] ?? 'Lainnya');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $nominal = (int)($_POST['nominal'] ?? 0);
        $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $petugas = trim($_POST['petugas'] ?? 'Bendahara');

        if ($deskripsi && $nominal > 0) {
            $new_trx = [
                'id' => 'trx-' . time(),
                'tanggal' => $tanggal,
                'tipe' => $tipe,
                'kategori' => $kategori,
                'deskripsi' => $deskripsi,
                'nominal' => $nominal,
                'petugas' => $petugas
            ];
            array_unshift($kas_data['transaksi'], $new_trx);
            write_json('kas.json', $kas_data);
            $message = "Transaksi {$tipe} sebesar " . format_rupiah($nominal) . " berhasil dicatat!";
            $message_type = 'success';
        } else {
            $message = "Mohon isi deskripsi dan nominal transaksi dengan benar.";
            $message_type = 'danger';
        }
    } elseif ($action === 'delete_trx') {
        $id = $_POST['id'] ?? '';
        $kas_data['transaksi'] = array_values(array_filter($kas_data['transaksi'], function($item) use ($id) {
            return $item['id'] !== $id;
        }));
        write_json('kas.json', $kas_data);
        $message = "Transaksi kas berhasil dihapus.";
        $message_type = 'success';
    } elseif ($action === 'toggle_siswa_kas') {
        $absen = (int)($_POST['absen'] ?? 0);
        $minggu = $_POST['minggu'] ?? ''; // 'm1', 'm2', 'm3', 'm4'
        if ($absen > 0 && in_array($minggu, ['m1', 'm2', 'm3', 'm4'])) {
            foreach ($kas_data['siswa'] as &$s) {
                if ($s['absen'] === $absen) {
                    $s[$minggu] = !$s[$minggu];
                    break;
                }
            }
            write_json('kas.json', $kas_data);
            $message = "Status pembayaran kas siswa berhasil diperbarui!";
            $message_type = 'success';
        }
    }
}

// Hitung total kas masuk, keluar, dan saldo
$total_masuk = 0;
$total_keluar = 0;
foreach ($kas_data['transaksi'] as $trx) {
    if ($trx['tipe'] === 'masuk') {
        $total_masuk += $trx['nominal'];
    } elseif ($trx['tipe'] === 'keluar') {
        $total_keluar += $trx['nominal'];
    }
}
$saldo = $total_masuk - $total_keluar;
$nominal_mingguan = $kas_data['nominal_mingguan'] ?? 5000;

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="main-wrapper">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1><span>💵</span> Transparansi Kas Kelas</h1>
            <p>Laporan pencatatan arus kas dan status iuran siswa X PPLG B secara terbuka dan akuntabel.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openTrxModal()">
            ➕ Catat Transaksi Baru
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
            <div class="stat-icon" style="background-color: var(--success-light); color: var(--success);">📥</div>
            <div class="stat-info">
                <div class="stat-label">Total Pemasukan</div>
                <div class="stat-value" style="color: var(--success);"><?= format_rupiah($total_masuk) ?></div>
                <div class="stat-detail">Dari iuran kas & donasi</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--danger-light); color: var(--danger);">📤</div>
            <div class="stat-info">
                <div class="stat-label">Total Pengeluaran</div>
                <div class="stat-value" style="color: var(--danger);"><?= format_rupiah($total_keluar) ?></div>
                <div class="stat-detail">Kebutuhan operasional kelas</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <div class="stat-label">Saldo Kas Saat Ini</div>
                <div class="stat-value"><?= format_rupiah($saldo) ?></div>
                <div class="stat-detail">Dana siap digunakan</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🏷️</div>
            <div class="stat-info">
                <div class="stat-label">Iuran Mingguan</div>
                <div class="stat-value"><?= format_rupiah($nominal_mingguan) ?></div>
                <div class="stat-detail">Per siswa tiap minggu</div>
            </div>
        </div>
    </div>

    <!-- Tab View: Transaksi vs Matrix Siswa -->
    <div class="card">
        <div class="card-header">
            <div class="day-pills" style="margin-bottom: 0;">
                <button type="button" class="day-pill active" id="tabBtnTrx" onclick="switchKasTab('trx')">
                    📜 Riwayat Transaksi (<?= count($kas_data['transaksi']) ?>)
                </button>
                <button type="button" class="day-pill" id="tabBtnSiswa" onclick="switchKasTab('siswa')">
                    👥 Status Iuran Siswa (<?= count($kas_data['siswa']) ?> Siswa)
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- TAB 1: Transaksi Table -->
            <div id="tabContentTrx">
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th>Nominal</th>
                                <th>Petugas</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($kas_data['transaksi'])): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                        Belum ada riwayat transaksi kas yang dicatat.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($kas_data['transaksi'] as $t): 
                                    $is_masuk = ($t['tipe'] === 'masuk');
                                ?>
                                    <tr>
                                        <td>📅 <?= format_tanggal_id($t['tanggal']) ?></td>
                                        <td>
                                            <span class="badge <?= $is_masuk ? 'badge-success' : 'badge-danger' ?>">
                                                <?= $is_masuk ? 'Pemasukan' : 'Pengeluaran' ?>
                                            </span>
                                        </td>
                                        <td><span class="badge badge-navy"><?= htmlspecialchars($t['kategori']) ?></span></td>
                                        <td><b><?= htmlspecialchars($t['deskripsi']) ?></b></td>
                                        <td style="font-weight: 700; color: <?= $is_masuk ? 'var(--success)' : 'var(--danger)' ?>;">
                                            <?= $is_masuk ? '+' : '-' ?> <?= format_rupiah($t['nominal']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($t['petugas'] ?? 'Bendahara') ?></td>
                                        <td style="text-align: center;">
                                            <form method="POST" action="" onsubmit="return confirm('Hapus transaksi ini?')">
                                                <input type="hidden" name="action" value="delete_trx">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($t['id']) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                                    🗑️
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: Status Iuran Siswa Matrix -->
            <div id="tabContentSiswa" style="display: none;">
                <p style="margin-bottom: 14px; font-size: 0.9rem; color: var(--text-muted);">
                    Klik tombol status Minggu (M1 - M4) untuk mengubah status pembayaran siswa secara langsung.
                </p>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Absen</th>
                                <th>Nama Lengkap Siswa</th>
                                <th style="text-align: center;">Minggu 1</th>
                                <th style="text-align: center;">Minggu 2</th>
                                <th style="text-align: center;">Minggu 3</th>
                                <th style="text-align: center;">Minggu 4</th>
                                <th style="text-align: center;">Terbayar</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($kas_data['siswa'] as $s): 
                                $bayar_count = ($s['m1'] ? 1 : 0) + ($s['m2'] ? 1 : 0) + ($s['m3'] ? 1 : 0) + ($s['m4'] ? 1 : 0);
                                $total_siswa_bayar = $bayar_count * $nominal_mingguan;
                                $is_lunas = ($bayar_count === 4);
                            ?>
                                <tr>
                                    <td style="font-weight: 700; text-align: center; color: var(--text-muted);">#<?= $s['absen'] ?></td>
                                    <td><b><?= htmlspecialchars($s['nama']) ?></b></td>
                                    
                                    <!-- M1 -->
                                    <td style="text-align: center;">
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_siswa_kas">
                                            <input type="hidden" name="absen" value="<?= $s['absen'] ?>">
                                            <input type="hidden" name="minggu" value="m1">
                                            <button type="submit" class="badge <?= $s['m1'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;">
                                                <?= $s['m1'] ? 'Lunas' : 'Belum' ?>
                                            </button>
                                        </form>
                                    </td>

                                    <!-- M2 -->
                                    <td style="text-align: center;">
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_siswa_kas">
                                            <input type="hidden" name="absen" value="<?= $s['absen'] ?>">
                                            <input type="hidden" name="minggu" value="m2">
                                            <button type="submit" class="badge <?= $s['m2'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;">
                                                <?= $s['m2'] ? 'Lunas' : 'Belum' ?>
                                            </button>
                                        </form>
                                    </td>

                                    <!-- M3 -->
                                    <td style="text-align: center;">
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_siswa_kas">
                                            <input type="hidden" name="absen" value="<?= $s['absen'] ?>">
                                            <input type="hidden" name="minggu" value="m3">
                                            <button type="submit" class="badge <?= $s['m3'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;">
                                                <?= $s['m3'] ? 'Lunas' : 'Belum' ?>
                                            </button>
                                        </form>
                                    </td>

                                    <!-- M4 -->
                                    <td style="text-align: center;">
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_siswa_kas">
                                            <input type="hidden" name="absen" value="<?= $s['absen'] ?>">
                                            <input type="hidden" name="minggu" value="m4">
                                            <button type="submit" class="badge <?= $s['m4'] ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;">
                                                <?= $s['m4'] ? 'Lunas' : 'Belum' ?>
                                            </button>
                                        </form>
                                    </td>

                                    <td style="text-align: center; font-weight: 600;">
                                        <?= format_rupiah($total_siswa_bayar) ?>
                                    </td>

                                    <td style="text-align: center;">
                                        <span class="badge <?= $is_lunas ? 'badge-success' : 'badge-warning' ?>">
                                            <?= $is_lunas ? 'LUNAS BULAN INI' : 'KURANG ' . (4 - $bayar_count) . ' MG' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Catat Transaksi Baru -->
<div class="modal-backdrop" id="modalTrx" onclick="if(event.target === this) closeTrxModal()">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>➕ Catat Transaksi Kas Kelas</h3>
            <button class="modal-close" onclick="closeTrxModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_trx">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipe Transaksi <span style="color:var(--danger)">*</span></label>
                        <select name="tipe" class="form-select" required>
                            <option value="masuk">Pemasukan (Uang Kas Masuk)</option>
                            <option value="keluar">Pengeluaran (Biaya / Belanja)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select">
                            <option value="Kas Mingguan">Kas Mingguan</option>
                            <option value="Perlengkapan Kelas">Perlengkapan Kelas</option>
                            <option value="Kebersihan">Kebersihan & Sanitasi</option>
                            <option value="Kegiatan Kelas">Kegiatan Kelas</option>
                            <option value="Sosial & Donasi">Sosial & Donasi</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan Transaksi <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="deskripsi" class="form-control" placeholder="Contoh: Beli spidol & penghapus whiteboard" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nominal (Rp) <span style="color:var(--danger)">*</span></label>
                        <input type="number" name="nominal" class="form-control" placeholder="Contoh: 50000" min="1000" step="500" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Transaksi</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Dicatat Oleh (Petugas)</label>
                    <input type="text" name="petugas" class="form-control" value="Bendahara Kelas">
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeTrxModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function switchKasTab(tab) {
    const btnTrx = document.getElementById('tabBtnTrx');
    const btnSiswa = document.getElementById('tabBtnSiswa');
    const contentTrx = document.getElementById('tabContentTrx');
    const contentSiswa = document.getElementById('tabContentSiswa');

    if (tab === 'trx') {
        btnTrx.classList.add('active');
        btnSiswa.classList.remove('active');
        contentTrx.style.display = 'block';
        contentSiswa.style.display = 'none';
    } else {
        btnSiswa.classList.add('active');
        btnTrx.classList.remove('active');
        contentTrx.style.display = 'none';
        contentSiswa.style.display = 'block';
    }
}

function openTrxModal() {
    document.getElementById('modalTrx').classList.add('open');
}

function closeTrxModal() {
    document.getElementById('modalTrx').classList.remove('open');
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
