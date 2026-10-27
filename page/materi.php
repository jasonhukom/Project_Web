<?php
/**
 * Bank Modul & Materi Koding - X PPLG B
 */
require_once dirname(__DIR__) . '/includes/functions.php';

$page_title = "Bank Modul & Materi Koding";
$current_page = "materi";
$base_url = "../";

$materi_list = read_json('materi.json', []);
$message = '';
$message_type = '';

// Handle Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $mapel = trim($_POST['mapel'] ?? '');
        $kategori = trim($_POST['kategori'] ?? 'Pemrograman');
        $judul = trim($_POST['judul'] ?? '');
        $guru = trim($_POST['guru'] ?? 'Guru Pengampu');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $link_modul = trim($_POST['link_modul'] ?? '');
        $snippet_code = trim($_POST['snippet_code'] ?? '');
        $snippet_lang = trim($_POST['snippet_lang'] ?? 'php');

        if ($mapel && $judul) {
            $new_mat = [
                'id' => 'mat-' . time(),
                'mapel' => $mapel,
                'kategori' => $kategori,
                'judul' => $judul,
                'guru' => $guru,
                'deskripsi' => $deskripsi,
                'link_modul' => $link_modul,
                'snippet_lang' => $snippet_lang,
                'snippet_code' => $snippet_code
            ];
            array_unshift($materi_list, $new_mat);
            write_json('materi.json', $materi_list);
            $message = "Materi '{$judul}' berhasil ditambahkan ke bank belajar!";
            $message_type = 'success';
        } else {
            $message = "Mohon lengkapi mata pelajaran dan judul materi.";
            $message_type = 'danger';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $materi_list = array_values(array_filter($materi_list, function($m) use ($id) {
            return $m['id'] !== $id;
        }));
        write_json('materi.json', $materi_list);
        $message = "Materi berhasil dihapus dari daftar.";
        $message_type = 'success';
    }
}

// Hitung kategori unik
$categories = [];
foreach ($materi_list as $m) {
    if (!empty($m['kategori']) && !in_array($m['kategori'], $categories)) {
        $categories[] = $m['kategori'];
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="main-wrapper">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1><span>📚</span> Bank Modul & Materi Koding</h1>
            <p>Kumpulan materi pembelajaran kejuruan Software Engineering, modul praktikum, dan referensi kode program X PPLG B.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openModalMateri()">
            ➕ Bagikan Materi Baru
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
            <div class="stat-icon">📖</div>
            <div class="stat-info">
                <div class="stat-label">Total Modul Belajar</div>
                <div class="stat-value"><?= count($materi_list) ?> Modul</div>
                <div class="stat-detail">Tersedia online</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--success-light); color: var(--success);">💻</div>
            <div class="stat-info">
                <div class="stat-label">Bidang Studi PPLG</div>
                <div class="stat-value" style="color: var(--success);"><?= count($categories) ?> Kategori</div>
                <div class="stat-detail">Web, DPK, Git, AI</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--warning-light); color: var(--accent-orange);">⚡</div>
            <div class="stat-info">
                <div class="stat-label">Code Snippets</div>
                <div class="stat-value" style="color: var(--accent-orange);">Siap Pakai</div>
                <div class="stat-detail">Tinggal copy & paste</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: rgba(0, 48, 73, 0.1); color: var(--primary-navy);">🎓</div>
            <div class="stat-info">
                <div class="stat-label">Akses Materi</div>
                <div class="stat-value">Gratis 24/7</div>
                <div class="stat-detail">Untuk seluruh siswa</div>
            </div>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px 20px;">
            <div class="day-pills" style="margin-bottom: 0;">
                <button type="button" class="day-pill active" onclick="filterKategori('Semua')">Semua Kategori (<?= count($materi_list) ?>)</button>
                <?php foreach ($categories as $cat): ?>
                    <button type="button" class="day-pill" onclick="filterKategori('<?= htmlspecialchars($cat) ?>')">
                        <?= htmlspecialchars($cat) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Materials Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px;" id="materiContainer">
        <?php foreach ($materi_list as $m): ?>
            <div class="card materi-item" data-kategori="<?= htmlspecialchars($m['kategori'] ?? 'Lainnya') ?>" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div class="card-header" style="padding: 14px 18px;">
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <span class="badge badge-navy"><?= htmlspecialchars($m['mapel']) ?></span>
                            <span class="badge badge-warning"><?= htmlspecialchars($m['kategori'] ?? 'Umum') ?></span>
                        </div>
                        <form method="POST" action="" onsubmit="return confirm('Hapus modul materi ini?')" style="margin: 0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($m['id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 2px 7px; font-size: 0.75rem;" title="Hapus Materi">
                                🗑️
                            </button>
                        </form>
                    </div>

                    <div class="card-body" style="padding: 20px;">
                        <h3 style="font-size: 1.25rem; color: var(--primary-navy); margin-bottom: 8px;">
                            <?= htmlspecialchars($m['judul']) ?>
                        </h3>
                        <p style="font-size: 0.85rem; color: var(--accent-orange); font-weight: 600; margin-bottom: 12px;">
                            👨‍🏫 <?= htmlspecialchars($m['guru']) ?>
                        </p>
                        <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 16px;">
                            <?= nl2br(htmlspecialchars($m['deskripsi'])) ?>
                        </p>

                        <?php if (!empty($m['snippet_code'])): ?>
                            <div style="margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; background: #1E293B; color: #94A3B8; padding: 6px 12px; border-top-left-radius: var(--radius-sm); border-top-right-radius: var(--radius-sm); font-size: 0.78rem; font-family: monospace;">
                                    <span>CONTOH KODE (<?= strtoupper($m['snippet_lang'] ?? 'CODE') ?>)</span>
                                    <button type="button" class="btn btn-outline btn-sm" style="color: #FCBF49; border-color: rgba(255,255,255,0.2); padding: 2px 8px; font-size: 0.75rem;" onclick="copyCode(this)">
                                        📋 Salin
                                    </button>
                                </div>
                                <pre style="background: #0F172A; color: #F8FAFC; padding: 14px; border-bottom-left-radius: var(--radius-sm); border-bottom-right-radius: var(--radius-sm); font-size: 0.85rem; overflow-x: auto; font-family: 'Consolas', 'Monaco', monospace; margin: 0; line-height: 1.5;"><code><?= htmlspecialchars($m['snippet_code']) ?></code></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer" style="padding: 12px 20px; display: flex; justify-content: flex-end;">
                    <?php if (!empty($m['link_modul'])): ?>
                        <a href="<?= htmlspecialchars($m['link_modul']) ?>" target="_blank" class="btn btn-primary btn-sm">
                            🔗 Buka Tautan Modul &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Modal Bagikan Materi Baru -->
<div class="modal-backdrop" id="modalMateri" onclick="if(event.target === this) closeModalMateri()">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h3>➕ Bagikan Materi / Modul Koding Baru</h3>
            <button class="modal-close" onclick="closeModalMateri()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Mata Pelajaran <span style="color:var(--danger)">*</span></label>
                        <select name="mapel" class="form-select" required>
                            <option value="Dasar Program Keahlian">Dasar Program Keahlian (DPK)</option>
                            <option value="Informatika">Informatika</option>
                            <option value="KKA (Koding & AI)">KKA (Koding & Kecerdasan Artifisial)</option>
                            <option value="Matematika">Matematika</option>
                            <option value="Bahasa Inggris">Bahasa Inggris</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori Materi</label>
                        <select name="kategori" class="form-select">
                            <option value="Pemrograman">Pemrograman & Algoritma</option>
                            <option value="Web Development">Web Development</option>
                            <option value="Styling">Styling & UI Design</option>
                            <option value="Version Control">Version Control (Git)</option>
                            <option value="Kecerdasan Buatan">Kecerdasan Buatan (AI)</option>
                            <option value="Basis Data">Basis Data</option>
                            <option value="Teori">Teori & Modul</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Judul Materi / Modul <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: Pengenalan Object-Oriented Programming di PHP" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Guru Pengampu / Kontributor</label>
                    <input type="text" name="guru" class="form-control" value="Bpk. Budi Santoso S.Kom">
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi / Ringkasan Modul</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan ringkasan isi materi yang dibahas dalam modul ini..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tautan Download / Bacaan Modul (Google Drive / GitHub / Web)</label>
                    <input type="url" name="link_modul" class="form-control" placeholder="https://drive.google.com/...">
                </div>

                <div class="form-group">
                    <label class="form-label">Contoh Kode Snippet (Opsional)</label>
                    <textarea name="snippet_code" class="form-control" rows="4" style="font-family: monospace; font-size: 0.85rem;" placeholder="// Tuliskan potongan kode referensi praktikum di sini..."></textarea>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModalMateri()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Materi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function filterKategori(cat) {
    document.querySelectorAll('.day-pill').forEach(btn => {
        if (btn.textContent.includes(cat)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    const items = document.querySelectorAll('.materi-item');
    items.forEach(item => {
        const itemCat = item.getAttribute('data-kategori');
        if (cat === 'Semua' || itemCat === cat) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function openModalMateri() {
    document.getElementById('modalMateri').classList.add('open');
}

function closeModalMateri() {
    document.getElementById('modalMateri').classList.remove('open');
}

function copyCode(btn) {
    const pre = btn.closest('div').nextElementSibling;
    if (!pre) return;
    const code = pre.textContent;
    navigator.clipboard.writeText(code).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✅ Disalin!';
        setTimeout(() => { btn.textContent = orig; }, 1800);
    });
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
