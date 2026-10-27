<?php
/**
 * Kotak Aspirasi & Polling Kelas - X PPLG B
 */
require_once dirname(__DIR__) . '/includes/functions.php';

$page_title = "Kotak Aspirasi & Polling Kelas";
$current_page = "aspirasi";
$base_url = "../";

$aspirasi_data = read_json('aspirasi.json', [
    'polling' => [],
    'aspirasi' => []
]);

$message = '';
$message_type = '';

// Handle Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah_aspirasi') {
        $kategori = trim($_POST['kategori'] ?? 'Lainnya');
        $pengirim = trim($_POST['pengirim'] ?? 'Siswa (Anonim)');
        $pesan = trim($_POST['pesan'] ?? '');

        if ($pesan) {
            $new_asp = [
                'id' => 'asp-' . time(),
                'tanggal' => date('Y-m-d'),
                'pengirim' => $pengirim ?: 'Anonim',
                'kategori' => $kategori,
                'pesan' => $pesan,
                'status' => 'Diterima',
                'tanggapan' => 'Aspirasi telah diterima dan akan dibahas pada rapat kelas berikutnya.'
            ];
            array_unshift($aspirasi_data['aspirasi'], $new_asp);
            write_json('aspirasi.json', $aspirasi_data);
            $message = "Terima kasih! Aspirasi Anda berhasil disampaikan.";
            $message_type = 'success';
        } else {
            $message = "Mohon tuliskan isi aspirasi atau saran Anda.";
            $message_type = 'danger';
        }
    } elseif ($action === 'vote_poll') {
        $poll_id = $_POST['poll_id'] ?? '';
        $opsi_id = (int)($_POST['opsi_id'] ?? 0);

        foreach ($aspirasi_data['polling'] as &$poll) {
            if ($poll['id'] === $poll_id) {
                foreach ($poll['opsi'] as &$opt) {
                    if ($opt['id'] === $opsi_id) {
                        $opt['votes']++;
                        $poll['total_votes']++;
                        break;
                    }
                }
                break;
            }
        }
        write_json('aspirasi.json', $aspirasi_data);
        $message = "Suara voting Anda berhasil dihitung!";
        $message_type = 'success';
    }
}

// Hitung total suara dan statistik
$total_aspirasi = count($aspirasi_data['aspirasi']);
$total_polling = count($aspirasi_data['polling']);
$total_suara = 0;
foreach ($aspirasi_data['polling'] as $p) {
    $total_suara += ($p['total_votes'] ?? 0);
}

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="main-wrapper">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1><span>🗳️</span> Kotak Aspirasi & Polling Kelas</h1>
            <p>Wadah penyampaian saran, kritik membangun, dan pemungutan suara (voting) musyawarah X PPLG B.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openModalAsp()">
            ✉️ Sampaikan Aspirasi
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
            <div class="stat-icon">📩</div>
            <div class="stat-info">
                <div class="stat-label">Total Aspirasi</div>
                <div class="stat-value"><?= $total_aspirasi ?> Pesan</div>
                <div class="stat-detail">Dari siswa & anggota kelas</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--success-light); color: var(--success);">✔</div>
            <div class="stat-info">
                <div class="stat-label">Status Respon</div>
                <div class="stat-value" style="color: var(--success);">100% Ditanggapi</div>
                <div class="stat-detail">Oleh pengurus & wali kelas</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--warning-light); color: var(--accent-orange);">📊</div>
            <div class="stat-info">
                <div class="stat-label">Polling Aktif</div>
                <div class="stat-value" style="color: var(--accent-orange);"><?= $total_polling ?> Voting</div>
                <div class="stat-detail">Musyawarah mufakat</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: rgba(0, 48, 73, 0.1); color: var(--primary-navy);">🗳️</div>
            <div class="stat-info">
                <div class="stat-label">Suara Terkumpul</div>
                <div class="stat-value"><?= $total_suara ?> Suara</div>
                <div class="stat-detail">Partisipasi aktif siswa</div>
            </div>
        </div>
    </div>

    <!-- Polling / Voting Section -->
    <section class="card" style="margin-bottom: 32px;">
        <div class="card-header">
            <div class="card-title">
                <span>📊</span> Polling & Pemungutan Suara Musyawarah Kelas
            </div>
            <span class="badge badge-primary">Demokratis</span>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
                <?php foreach ($aspirasi_data['polling'] as $poll): 
                    $total_v = max(1, $poll['total_votes']);
                ?>
                    <div style="background: var(--bg-white); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 22px; box-shadow: var(--shadow-sm);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span class="badge badge-navy">Polling Kelas</span>
                            <span class="badge badge-success"><?= $poll['status'] ?></span>
                        </div>
                        <h3 style="font-size: 1.2rem; color: var(--primary-navy); margin-bottom: 6px;">
                            <?= htmlspecialchars($poll['judul']) ?>
                        </h3>
                        <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 18px;">
                            <?= htmlspecialchars($poll['deskripsi']) ?>
                        </p>

                        <!-- Voting Options & Progress Bars -->
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            <?php foreach ($poll['opsi'] as $opt): 
                                $persen = round(($opt['votes'] / $total_v) * 100);
                            ?>
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; margin-bottom: 4px;">
                                        <span style="font-weight: 600; color: var(--primary-navy);">
                                            <?= htmlspecialchars($opt['teks']) ?>
                                        </span>
                                        <span style="font-weight: 700; color: var(--accent-orange);">
                                            <?= $opt['votes'] ?> Suara (<?= $persen ?>%)
                                        </span>
                                    </div>
                                    <div style="width: 100%; height: 10px; background: #E2E8F0; border-radius: var(--radius-full); overflow: hidden; margin-bottom: 6px;">
                                        <div style="width: <?= $persen ?>%; height: 100%; background: linear-gradient(90deg, var(--accent-orange), var(--accent-gold)); border-radius: var(--radius-full); transition: width 0.5s ease;"></div>
                                    </div>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="vote_poll">
                                        <input type="hidden" name="poll_id" value="<?= htmlspecialchars($poll['id']) ?>">
                                        <input type="hidden" name="opsi_id" value="<?= $opt['id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" style="padding: 3px 10px; font-size: 0.78rem;">
                                            🗳️ Vote Opsi Ini
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="margin-top: 18px; border-top: 1px solid var(--border-light); padding-top: 10px; font-size: 0.82rem; color: var(--text-muted); text-align: right;">
                            Total Partisipasi: <b><?= $poll['total_votes'] ?> Siswa</b>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Aspirasi Siswa List -->
    <section class="card">
        <div class="card-header">
            <div class="card-title">
                <span>💬</span> Suara & Masukan Siswa X PPLG B
            </div>
            <span class="badge badge-success">Terbuka & Akuntabel</span>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($aspirasi_data['aspirasi'] as $asp): 
                    $badge_status = 'badge-success';
                    if ($asp['status'] === 'Diterima') $badge_status = 'badge-navy';
                    elseif ($asp['status'] === 'Sedang Dibahas') $badge_status = 'badge-warning';
                ?>
                    <div style="background: var(--bg-white); border-left: 5px solid var(--accent-orange); border-radius: var(--radius-sm); padding: 18px 22px; box-shadow: var(--shadow-sm);">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;">
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <span class="badge badge-navy"><?= htmlspecialchars($asp['kategori']) ?></span>
                                <span class="badge <?= $badge_status ?>"><?= htmlspecialchars($asp['status']) ?></span>
                            </div>
                            <span style="font-size: 0.82rem; color: var(--text-muted);">
                                📅 <?= format_tanggal_id($asp['tanggal']) ?> &bull; Oleh: <b><?= htmlspecialchars($asp['pengirim']) ?></b>
                            </span>
                        </div>
                        <p style="font-size: 0.98rem; color: var(--primary-navy); margin-bottom: 12px; line-height: 1.5;">
                            "<?= htmlspecialchars($asp['pesan']) ?>"
                        </p>
                        <?php if (!empty($asp['tanggapan'])): ?>
                            <div style="background: rgba(247, 127, 0, 0.08); border-left: 3px solid var(--accent-orange); padding: 10px 14px; border-radius: var(--radius-sm); font-size: 0.88rem; color: var(--primary-navy);">
                                <b>📢 Tanggapan Pengurus / Wali Kelas:</b> <?= htmlspecialchars($asp['tanggapan']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<!-- Modal Sampaikan Aspirasi -->
<div class="modal-backdrop" id="modalAsp" onclick="if(event.target === this) closeModalAsp()">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>✉️ Sampaikan Aspirasi & Usulan Kelas</h3>
            <button class="modal-close" onclick="closeModalAsp()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="tambah_aspirasi">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Kategori Aspirasi <span style="color:var(--danger)">*</span></label>
                    <select name="kategori" class="form-select" required>
                        <option value="Fasilitas Kelas">Fasilitas & Perlengkapan Kelas</option>
                        <option value="KBM & Belajar">Kegiatan Belajar Mengajar (KBM)</option>
                        <option value="Kebersihan">Kebersihan & Ketertiban</option>
                        <option value="Kegiatan & Event">Kegiatan & Lomba Kelas</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Pengirim (Boleh Kosongkan jika ingin Anonim)</label>
                    <input type="text" name="pengirim" class="form-control" placeholder="Nama Anda atau kosongkan untuk anonim">
                </div>

                <div class="form-group">
                    <label class="form-label">Isi Aspirasi / Saran / Masukan <span style="color:var(--danger)">*</span></label>
                    <textarea name="pesan" class="form-control" rows="4" placeholder="Tuliskan usulan, saran perbaikan kelas, atau ide kegiatan yang ingin Anda sampaikan secara sopan dan konstruktif..." required></textarea>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModalAsp()">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Aspirasi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openModalAsp() {
    document.getElementById('modalAsp').classList.add('open');
}

function closeModalAsp() {
    document.getElementById('modalAsp').classList.remove('open');
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
