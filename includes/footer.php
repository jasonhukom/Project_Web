<?php
if (!isset($base_url)) {
    $base_url = '';
}
$ext = defined('IS_STATIC_BUILD') ? '.html' : '.php';
$home_url   = ($base_url === '') ? "index{$ext}" : "../index{$ext}";
$jadwal_url = ($base_url === '') ? "page/jadwal{$ext}" : "jadwal{$ext}";
$event_url  = ($base_url === '') ? "page/event{$ext}" : "event{$ext}";
$kas_url    = ($base_url === '') ? "page/kas{$ext}" : "kas{$ext}";
$tugas_url  = ($base_url === '') ? "page/tugas{$ext}" : "tugas{$ext}";
?>
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-col brand-col">
            <div class="footer-brand">
                <img src="<?= $base_url ?>image/logo.png" alt="Logo X PPLG B" class="footer-logo">
                <p class="tagline">Pengembangan Perangkat Lunak dan GIM (PPLG) - Kelas X B</p>
                <p class="desc">Portal terpadu informasi jadwal pelajaran, transparansi kas kelas, monitoring tugas, dan agenda kegiatan.</p>
            </div>
        </div>
        <div class="footer-col">
            <h4 class="footer-heading">Navigasi Cepat</h4>
            <ul class="footer-links">
                <li><a href="<?= $home_url ?>">Beranda</a></li>
                <li><a href="<?= $jadwal_url ?>">Jadwal Pelajaran</a></li>
                <li><a href="<?= $event_url ?>">Events & Agenda</a></li>
                <li><a href="<?= $kas_url ?>">Transparansi KAS</a></li>
                <li><a href="<?= $tugas_url ?>">Daftar Tugas</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4 class="footer-heading">Motto Kelas</h4>
            <div class="motto-box">
                <span class="motto-quote">"Code with passion, collaborate with respect, innovate with impact."</span>
                <span class="badge-tag">#XPPLGBJuara</span>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="footer-bottom-inner">
            <p>&copy; <?= date('Y') ?> <b>X PPLG B</b> - Built with PHP & Modern CSS</p>
            <p class="tech-stack">PHP 8 &bull; Responsive Design</p>
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
