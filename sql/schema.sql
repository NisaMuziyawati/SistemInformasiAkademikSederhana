-- =========================================================
-- SISTEM INFORMASI AKADEMIK (SIA) SEDERHANA
-- Skema Database
-- =========================================================

CREATE DATABASE IF NOT EXISTS sia_db;
USE sia_db;

-- Tabel Users (untuk login Admin, Dosen, Mahasiswa)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- di-hash dengan password_hash()
    role ENUM('admin', 'dosen', 'mahasiswa') NOT NULL,
    ref_id INT NULL, -- relasi ke id dosen / mahasiswa (NULL untuk admin)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Dosen
CREATE TABLE dosen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Mahasiswa
CREATE TABLE mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    prodi VARCHAR(100) NOT NULL,
    angkatan YEAR NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Mata Kuliah
CREATE TABLE matakuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_mk VARCHAR(20) NOT NULL UNIQUE,
    nama_mk VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    dosen_id INT NULL,
    semester_ajaran ENUM('Ganjil', 'Genap') NOT NULL DEFAULT 'Ganjil',
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE SET NULL
);

-- Tabel KRS (Kartu Rencana Studi) -- mahasiswa mengambil matakuliah
CREATE TABLE krs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    matakuliah_id INT NOT NULL,
    tahun_ajaran VARCHAR(9) NOT NULL, -- contoh: 2025/2026
    semester ENUM('Ganjil', 'Genap') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (matakuliah_id) REFERENCES matakuliah(id) ON DELETE CASCADE,
    UNIQUE KEY unique_krs (mahasiswa_id, matakuliah_id, tahun_ajaran, semester)
);

-- Tabel Nilai -- diisi oleh dosen untuk setiap entri KRS
CREATE TABLE nilai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    krs_id INT NOT NULL UNIQUE,
    nilai_angka DECIMAL(5,2) NULL,
    nilai_huruf VARCHAR(2) NULL,
    bobot DECIMAL(3,2) NULL, -- bobot mutu (0 - 4)
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (krs_id) REFERENCES krs(id) ON DELETE CASCADE
);

-- =========================================================
-- DATA AWAL (SEED)
-- =========================================================

-- Password default semua akun: "password123" (sudah di-hash bcrypt)
-- Hash di bawah ini adalah hasil password_hash('password123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, role, ref_id) VALUES
('admin', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'admin', NULL);

-- ---------------------------------------------------------
-- 6 Dosen
-- ---------------------------------------------------------
INSERT INTO dosen (nip, nama, email) VALUES
('198001012010011001', 'Dr. Budi Santoso, M.Kom',   'budi.santoso@kampus.ac.id'),
('198202022011012002', 'Siti Aminah, M.T',           'siti.aminah@kampus.ac.id'),
('198305152012011003', 'Ahmad Fauzi, M.Kom',         'ahmad.fauzi@kampus.ac.id'),
('198407202013012004', 'Rina Wulandari, M.T',        'rina.wulandari@kampus.ac.id'),
('198601102014011005', 'Hendra Gunawan, M.Kom',      'hendra.gunawan@kampus.ac.id'),
('198709052015012006', 'Maya Puspita, M.T',          'maya.puspita@kampus.ac.id');

INSERT INTO users (username, password, role, ref_id) VALUES
('dosen1', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'dosen', 1),
('dosen2', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'dosen', 2),
('dosen3', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'dosen', 3),
('dosen4', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'dosen', 4),
('dosen5', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'dosen', 5),
('dosen6', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'dosen', 6);

-- ---------------------------------------------------------
-- 6 Mahasiswa (3 laki-laki, 3 perempuan)
-- ---------------------------------------------------------
INSERT INTO mahasiswa (nim, nama, prodi, angkatan) VALUES
('21552001', 'Andi Pratama',        'Teknik Informatika', 2021),
('21552002', 'Dewi Lestari',        'Teknik Informatika', 2021),
('21552003', 'Muhammad Rizki',      'Teknik Informatika', 2021),
('21552004', 'Putri Ayu Ningsih',   'Teknik Informatika', 2022),
('21552005', 'Fajar Ramadhan',      'Teknik Informatika', 2022),
('21552006', 'Sri Wahyuni',         'Teknik Informatika', 2023);

INSERT INTO users (username, password, role, ref_id) VALUES
('21552001', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'mahasiswa', 1),
('21552002', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'mahasiswa', 2),
('21552003', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'mahasiswa', 3),
('21552004', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'mahasiswa', 4),
('21552005', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'mahasiswa', 5),
('21552006', '$2b$10$GYudWzMfgzI8OF2BgLagxObFPdcq/wpmnlDpDjPk0xkHZ13ZGX8Am', 'mahasiswa', 6);

-- ---------------------------------------------------------
-- 6 Mata Kuliah, masing-masing diampu 1 dosen berbeda
-- ---------------------------------------------------------
INSERT INTO matakuliah (kode_mk, nama_mk, sks, dosen_id, semester_ajaran) VALUES
('IF101', 'Pemrograman Internet',        3, 1, 'Ganjil'), -- Budi Santoso
('IF102', 'Basis Data',                  3, 2, 'Ganjil'), -- Siti Aminah
('IF103', 'Struktur Data',               3, 3, 'Ganjil'), -- Ahmad Fauzi
('IF104', 'Jaringan Komputer',           3, 4, 'Ganjil'), -- Rina Wulandari
('IF105', 'Kecerdasan Buatan',           3, 5, 'Ganjil'), -- Hendra Gunawan
('IF106', 'Rekayasa Perangkat Lunak',    3, 6, 'Ganjil'); -- Maya Puspita

-- ---------------------------------------------------------
-- Contoh KRS (setiap mahasiswa ambil beberapa mata kuliah
-- dari dosen yang berbeda-beda)
-- ---------------------------------------------------------
INSERT INTO krs (mahasiswa_id, matakuliah_id, tahun_ajaran, semester) VALUES
-- Andi Pratama (1)
(1, 1, '2025/2026', 'Ganjil'),
(1, 2, '2025/2026', 'Ganjil'),
(1, 3, '2025/2026', 'Ganjil'),
(1, 4, '2025/2026', 'Ganjil'),
-- Dewi Lestari (2)
(2, 1, '2025/2026', 'Ganjil'),
(2, 2, '2025/2026', 'Ganjil'),
(2, 5, '2025/2026', 'Ganjil'),
-- Muhammad Rizki (3)
(3, 3, '2025/2026', 'Ganjil'),
(3, 4, '2025/2026', 'Ganjil'),
(3, 6, '2025/2026', 'Ganjil'),
-- Putri Ayu Ningsih (4)
(4, 2, '2025/2026', 'Ganjil'),
(4, 5, '2025/2026', 'Ganjil'),
(4, 6, '2025/2026', 'Ganjil'),
-- Fajar Ramadhan (5) -- sengaja belum dinilai, untuk contoh status "Belum dinilai"
(5, 1, '2025/2026', 'Ganjil'),
(5, 4, '2025/2026', 'Ganjil'),
(5, 5, '2025/2026', 'Ganjil'),
-- Sri Wahyuni (6) -- sengaja belum dinilai
(6, 2, '2025/2026', 'Ganjil'),
(6, 3, '2025/2026', 'Ganjil'),
(6, 6, '2025/2026', 'Ganjil');

-- ---------------------------------------------------------
-- Contoh Nilai (sebagian sudah dinilai dosen, agar IPK
-- langsung terlihat perhitungannya; krs_id mengikuti urutan
-- INSERT KRS di atas, baris 1 s/d 13)
-- ---------------------------------------------------------
INSERT INTO nilai (krs_id, nilai_angka, nilai_huruf, bobot) VALUES
(1,  88, 'A',  4.00), -- Andi - Pemrograman Internet
(2,  75, 'AB', 3.50), -- Andi - Basis Data
(3,  60, 'C',  2.00), -- Andi - Struktur Data
(4,  92, 'A',  4.00), -- Andi - Jaringan Komputer
(5,  95, 'A',  4.00), -- Dewi - Pemrograman Internet
(6,  80, 'A',  4.00), -- Dewi - Basis Data
(7,  70, 'B',  3.00), -- Dewi - Kecerdasan Buatan
(8,  65, 'BC', 2.50), -- Rizki - Struktur Data
(9,  72, 'B',  3.00), -- Rizki - Jaringan Komputer
(10, 58, 'CD', 1.50), -- Rizki - Rekayasa Perangkat Lunak
(11, 85, 'A',  4.00), -- Putri - Basis Data
(12, 90, 'A',  4.00), -- Putri - Kecerdasan Buatan
(13, 77, 'AB', 3.50); -- Putri - Rekayasa Perangkat Lunak
-- KRS Fajar Ramadhan & Sri Wahyuni sengaja dibiarkan tanpa nilai
-- sebagai contoh status "Belum dinilai" di aplikasi.

