<?php
/**
 * Mendeteksi otomatis base URL/subfolder tempat aplikasi di-deploy.
 * Ini penting karena aplikasi bisa diakses lewat:
 *   - http://localhost/sia/...      (di dalam subfolder htdocs)
 *   - http://localhost/...          (jika DocumentRoot langsung ke folder ini)
 * Tanpa ini, redirect/link dengan path absolut ("/admin/dashboard.php")
 * akan salah dan menyebabkan 404 saat project ada di dalam subfolder.
 */

if (!defined('BASE_URL')) {
    // Path folder project ini di server (root folder "sia")
    $appRoot = str_replace('\\', '/', dirname(__DIR__));

    // Path root folder web server (htdocs/www)
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));

    $base = '';
    if ($docRoot !== '' && strpos($appRoot, $docRoot) === 0) {
        $base = substr($appRoot, strlen($docRoot));
    }

    // Encode tiap segmen folder (penting jika nama folder mengandung spasi,
    // tanda kurung, atau karakter khusus lain) tanpa meng-encode karakter "/"
    $segments = array_map('rawurlencode', explode('/', $base));
    $base = implode('/', $segments);

    // Normalisasi: tanpa trailing slash, contoh hasil: "" atau "/sia"
    define('BASE_URL', rtrim($base, '/'));
}
