<?php
if (!isset($base_url)) {
    $base_url = '';
}
$ext = defined('IS_STATIC_BUILD') ? '.html' : '.php';
$home_url       = ($base_url === '') ? "index{$ext}" : "../index{$ext}";
$jadwal_url     = ($base_url === '') ? "page/jadwal{$ext}" : "jadwal{$ext}";
$absensi_url    = ($base_url === '') ? "page/absensi{$ext}" : "absensi{$ext}";
$inventaris_url = ($base_url === '') ? "page/inventaris{$ext}" : "inventaris{$ext}";
$materi_url     = ($base_url === '') ? "page/materi{$ext}" : "materi{$ext}";
$aspirasi_url   = ($base_url === '') ? "page/aspirasi{$ext}" : "aspirasi{$ext}";
$tugas_url      = ($base_url === '') ? "page/tugas{$ext}" : "tugas{$ext}";
$kas_url        = ($base_url === '') ? "page/kas{$ext}" : "kas{$ext}";
$event_url      = ($base_url === '') ? "page/event{$ext}" : "event{$ext}";
?>
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-col brand-col">
            <div class="footer-brand">
                <img src="<?= $base_url ?>image/logo.png" alt="Logo X PPLG B" class="footer-logo">
                <p class="tagline">Sistem Informasi Akademik & Siswa X PPLG B</p>
                <p class="desc">Sistem informasi terpadu: presensi siswa, peminjaman lab RPL, bank materi koding, aspirasi & voting kelas, transparansi kas, monitoring tugas, dan jadwal KBM.</p>
            </div>
        </div>
        <div class="footer-col">
            <h4 class="footer-heading">Navigasi Sistem</h4>
            <ul class="footer-links">
                <li><a href="<?= $home_url ?>">Beranda Dashboard</a></li>
                <li><a href="<?= $jadwal_url ?>">Jadwal Pelajaran & Piket</a></li>
                <li><a href="<?= $absensi_url ?>">Presensi & Absensi Siswa</a></li>
                <li><a href="<?= $inventaris_url ?>">Peminjaman Alat Lab RPL</a></li>
                <li><a href="<?= $materi_url ?>">Bank Modul & Materi</a></li>
                <li><a href="<?= $aspirasi_url ?>">Kotak Aspirasi & Polling</a></li>
                <li><a href="<?= $tugas_url ?>">Daftar Tugas & PR</a></li>
                <li><a href="<?= $kas_url ?>">Transparansi KAS Kelas</a></li>
                <li><a href="<?= $event_url ?>">Events & Kalender</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4 class="footer-heading">Identitas & Motto</h4>
            <div class="motto-box">
                <span class="motto-quote">"Code with passion, collaborate with respect, innovate with impact."</span>
                <span class="badge-tag">#XPPLGBJuara</span>
            </div>
            <div style="margin-top: 14px; font-size: 0.82rem; color: #94A3B8;">
                <p><b>Kompetensi Keahlian:</b> Pengembangan Perangkat Lunak dan GIM (PPLG)</p>
                <p><b>SMK Negeri / Swasta Terakreditasi</b></p>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="footer-bottom-inner">
            <p>&copy; <?= date('Y') ?> <b>SIMAS X PPLG B</b> - Sistem Informasi Siswa & Kesiswaan</p>
            <p class="tech-stack">PHP 8 &bull; Vercel Serverless Ready</p>
        </div>
    </div>
</footer>

<script>
// Mobile navigation toggle
document.addEventListener('DOMContentLoaded', () => {
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNavigation');
    if (mobileBtn && mainNav) {
        mobileBtn.addEventListener('click', () => {
            mainNav.classList.toggle('nav-open');
            mobileBtn.classList.toggle('is-active');
        });
    }
});
</script>
</body>
</html>
