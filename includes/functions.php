<?php
/**
 * Helper Functions - Project Web Kelas X PPLG B
 */

// Menentukan Base Path untuk asset & link
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Format Rupiah
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Format Hari dan Tanggal Bahasa Indonesia
function format_tanggal_id($tanggal_str) {
    if (!$tanggal_str) return '-';
    $timestamp = strtotime($tanggal_str);
    if (!$timestamp) return $tanggal_str;

    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $hari_index = date('w', $timestamp);
    $hari_nama = $hari[$hari_index];
    $tgl = date('j', $timestamp);
    $bln = $bulan[(int)date('n', $timestamp)];
    $thn = date('Y', $timestamp);

    return "{$hari_nama}, {$tgl} {$bln} {$thn}";
}

// Membaca file data JSON (mendukung Vercel & serverless /tmp fallback)
function read_json($filename, $default = []) {
    $tmp_path = sys_get_temp_dir() . '/' . $filename;
    if (file_exists($tmp_path)) {
        $content = @file_get_contents($tmp_path);
        $data = json_decode($content, true);
        if (is_array($data)) return $data;
    }

    $path = BASE_PATH . '/data/' . $filename;
    if (!file_exists($path)) {
        return $default;
    }
    $content = @file_get_contents($path);
    $data = json_decode($content, true);
    return is_array($data) ? $data : $default;
}

// Menyimpan data ke file JSON (mendukung Vercel & serverless /tmp fallback)
function write_json($filename, $data) {
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Update /tmp file (tersedia di runtime Vercel)
    $tmp_path = sys_get_temp_dir() . '/' . $filename;
    @file_put_contents($tmp_path, $encoded);

    // Coba simpan ke folder data lokal jika filesystem writable
    $dir = BASE_PATH . '/data';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $path = $dir . '/' . $filename;
    if (is_writable($dir) || (file_exists($path) && is_writable($path))) {
        return @file_put_contents($path, $encoded) !== false;
    }

    return true;
}

// Menghitung sisa hari
function sisa_hari($deadline) {
    $today = new DateTime('today');
    $target = new DateTime($deadline);
    $diff = $today->diff($target);
    $days = (int)$diff->format('%r%a');
    return $days;
}
?>
