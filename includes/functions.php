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

// Membaca file data JSON
function read_json($filename, $default = []) {
    $path = BASE_PATH . '/data/' . $filename;
    if (!file_exists($path)) {
        return $default;
    }
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    return is_array($data) ? $data : $default;
}

// Menyimpan data ke file JSON
function write_json($filename, $data) {
    $dir = BASE_PATH . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $path = $dir . '/' . $filename;
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
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
