<?php
/**
 * Static Site Builder for GitHub Pages & Static Web Hosting
 * Exports PHP pages to clean static HTML in dist/
 */

echo "🚀 Starting Static Export for Web...\n";

$rootDir = __DIR__;
$distDir = $rootDir . '/dist';

// 1. Prepare dist directories
if (!is_dir($distDir)) {
    mkdir($distDir, 0777, true);
}
if (!is_dir($distDir . '/page')) {
    mkdir($distDir . '/page', 0777, true);
}

// 2. Helper to copy directories recursively
function copyDirectory($src, $dst) {
    if (!is_dir($src)) return;
    @mkdir($dst, 0777, true);
    $files = scandir($src);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        if (is_dir($srcPath)) {
            copyDirectory($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
}

// 3. Copy static asset directories
echo "📦 Copying assets (style, image, data)...\n";
copyDirectory($rootDir . '/style', $distDir . '/style');
copyDirectory($rootDir . '/image', $distDir . '/image');
copyDirectory($rootDir . '/data', $distDir . '/data');

// Add .nojekyll for GitHub Pages compatibility
file_put_contents($distDir . '/.nojekyll', '');

// 4. List of pages to render
$pages = [
    'index.php' => 'index.html',
    'page/jadwal.php' => 'page/jadwal.html',
    'page/event.php' => 'page/event.html',
    'page/kas.php' => 'page/kas.html',
    'page/tugas.php' => 'page/tugas.html'
];

// 5. Render each page using PHP CLI subprocess for isolated context
foreach ($pages as $source => $output) {
    $sourcePath = $rootDir . '/' . $source;
    $outputPath = $distDir . '/' . $output;

    $cmd = 'php -d display_errors=1 -r "'
        . 'define(\'IS_STATIC_BUILD\', true); '
        . 'ob_start(); '
        . 'include \'' . addslashes($sourcePath) . '\'; '
        . '$html = ob_get_clean(); '
        . 'file_put_contents(\'' . addslashes($outputPath) . '\', $html); '
        . 'echo strlen($html);"';

    $bytes = (int)shell_exec($cmd);
    if ($bytes > 0 && file_exists($outputPath)) {
        echo "✅ Rendered: {$source} -> dist/{$output} ({$bytes} bytes)\n";
    } else {
        echo "❌ Failed rendering {$source}\n";
    }
}

echo "\n✨ Static site build complete! Output directory: dist/\n";
