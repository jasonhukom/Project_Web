<?php
if (!isset($base_url)) {
    $base_url = '';
}
if (!isset($current_page)) {
    $current_page = 'home';
}

$home_url   = ($base_url === '') ? 'index.php' : '../index.php';
$jadwal_url = ($base_url === '') ? 'page/jadwal.php' : 'jadwal.php';
$event_url  = ($base_url === '') ? 'page/event.php' : 'event.php';
$kas_url    = ($base_url === '') ? 'page/kas.php' : 'kas.php';
$tugas_url  = ($base_url === '') ? 'page/tugas.php' : 'tugas.php';
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
                <li class="nav-item <?= $current_page === 'event' ? 'active' : '' ?>">
                    <a href="<?= $event_url ?>">Events</a>
                </li>
                <li class="nav-item <?= $current_page === 'kas' ? 'active' : '' ?>">
                    <a href="<?= $kas_url ?>">KAS</a>
                </li>
                <li class="nav-item <?= $current_page === 'tugas' ? 'active' : '' ?>">
                    <a href="<?= $tugas_url ?>">Tugas</a>
                </li>
            </ul>
        </nav>
    </div>
</header>
