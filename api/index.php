<?php
/**
 * Vercel Serverless PHP Entrypoint (api/index.php)
 * Routes incoming requests on Vercel to index.php or page/*.php
 */

$rootDir = dirname(__DIR__);
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

// Resolve target page from query parameter or request path
$page = $_GET['__page'] ?? null;
if (!$page && preg_match('#^/page/([a-zA-Z0-9_-]+)(?:\.php|\.html)?$#', $path, $matches)) {
    $page = $matches[1];
}

$allowedPages = [
    'jadwal',
    'absensi',
    'inventaris',
    'materi',
    'aspirasi',
    'tugas',
    'kas',
    'event'
];

if ($page && in_array($page, $allowedPages, true)) {
    $targetFile = $rootDir . '/page/' . $page . '.php';
    if (is_file($targetFile)) {
        chdir(dirname($targetFile));
        require $targetFile;
        exit;
    }
}

chdir($rootDir);
require $rootDir . '/index.php';
