<?php
/**
 * File otentikasi & otorisasi.
 * Panggil requireLogin('admin') / requireLogin('dosen') / requireLogin('mahasiswa')
 * di bagian paling atas setiap halaman yang butuh proteksi.
 */

require_once __DIR__ . '/../config/base.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Wajibkan login dengan role tertentu.
 * @param string|array $roles satu role atau array beberapa role yang diizinkan
 */
function requireLogin($roles) {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    $allowed = is_array($roles) ? $roles : [$roles];

    if (!in_array($_SESSION['role'], $allowed, true)) {
        header('Location: ' . BASE_URL . '/login.php?error=unauthorized');
        exit;
    }
}

function currentUser() {
    return [
        'id'     => $_SESSION['user_id']  ?? null,
        'role'   => $_SESSION['role']     ?? null,
        'ref_id' => $_SESSION['ref_id']   ?? null,
        'nama'   => $_SESSION['nama']     ?? null,
    ];
}

/** Helper sederhana untuk mencegah XSS saat menampilkan data ke HTML */
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
