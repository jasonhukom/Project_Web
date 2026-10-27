<?php
if (!isset($base_url)) {
    $base_url = '';
}
if (!isset($current_page)) {
    $current_page = 'home';
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
<header class="site-header">
    <div class="navigation-bar">
        <div class="brand-group">
            <a href="<?= $home_url ?>" class="brand-link">
                <img src="<?= $base_url ?>image/logo_profile.png" alt="Profile Class" class="nav-profile-img">
                <img src="<?= $base_url ?>image/logo.png" alt="Logo X PPLG B" class="nav-logo-img">
            </a>
        </div>

        <button class="mobile-toggle" id="mobileMenuBtn" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="navigation-nav" id="mainNavigation">
            <ul class="nav-menu">
                <li class="nav-item <?= $current_page === 'home' ? 'active' : '' ?>">
                    <a href="<?= $home_url ?>">Beranda</a>
                </li>
                <li class="nav-item <?= $current_page === 'jadwal' ? 'active' : '' ?>">
                    <a href="<?= $jadwal_url ?>">Jadwal</a>
                </li>
                <li class="nav-item <?= $current_page === 'absensi' ? 'active' : '' ?>">
                    <a href="<?= $absensi_url ?>">Presensi</a>
                </li>
                <li class="nav-item <?= $current_page === 'inventaris' ? 'active' : '' ?>">
                    <a href="<?= $inventaris_url ?>">Lab & Alat</a>
                </li>
                <li class="nav-item <?= $current_page === 'materi' ? 'active' : '' ?>">
                    <a href="<?= $materi_url ?>">Materi</a>
                </li>
                <li class="nav-item <?= $current_page === 'aspirasi' ? 'active' : '' ?>">
                    <a href="<?= $aspirasi_url ?>">Aspirasi</a>
                </li>
                <li class="nav-item <?= $current_page === 'tugas' ? 'active' : '' ?>">
                    <a href="<?= $tugas_url ?>">Tugas</a>
                </li>
                <li class="nav-item <?= $current_page === 'kas' ? 'active' : '' ?>">
                    <a href="<?= $kas_url ?>">KAS</a>
                </li>
                <li class="nav-item <?= $current_page === 'event' ? 'active' : '' ?>">
                    <a href="<?= $event_url ?>">Events</a>
                </li>
            </ul>
        </nav>
    </div>
</header>
