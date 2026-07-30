Aplikasi Sistem Informasi Akademik untuk tugas Projek Pemrograman Internet.
Backend: **PHP native (tanpa framework)**. Frontend: **HTML, CSS, JS + Bootstrap 5**.
- Login multi-role: **Admin**, **Dosen**, **Mahasiswa**
- Admin: kelola data Mahasiswa, Dosen, dan Mata Kuliah (CRUD)
- Mahasiswa: Isi KRS, lihat nilai, lihat perhitungan IPK
- Dosen: input nilai mahasiswa untuk mata kuliah yang diampu
- IPK dihitung otomatis dari nilai & SKS yang tersimpan
1. Salin folder ini ke `htdocs` (XAMPP) atau `www` (Laragon), misalnya jadi `sia`.
2. Buat database dengan mengimpor `sql/schema.sql` lewat phpMyAdmin atau:
   ```
   m
3. Sesuaikan kredensial database di `config/database.php` jika perlu
   (default: host `localhost`, database `sia_db`, user `root`, password kosong).
4. Buka `http://localhost/sia/login.php` di browser.

## Akun Demo

| Role      | Username   | Password      |
|-----------|-----------|---------------|
| Admin     | admin      | password123   |
| Dosen     | dosen1     | password123   |
| Mahasiswa | 21552001   | password123   |

> Catatan: saat admin menambah data Mahasiswa/Dosen baru, akun login otomatis
> dibuat dengan **password default = NIM/NIP** masing-masing.

## Struktur Folder

```
sia/
├── admin/            Halaman khusus admin (CRUD mahasiswa, dosen, matakuliah)
├── dosen/             Halaman khusus dosen (dashboard, input nilai)
├── mahasiswa/         Halaman khusus mahasiswa (dashboard, KRS, nilai, IPK)
├── includes/          File bersama (auth, header, footer, helper)
├── config/            Konfigurasi koneksi database
├── assets/            CSS & JS
├── sql/schema.sql      Skema database + data awal (seed)
├── login.php / logout.php / index.php
## Pengujian yang Sudah Dilakukan
Aplikasi ini sudah diuji end-to-end secara otomatis (login ketiga role, isi KRS,
input nilai oleh dosen, perhitungan IPK, dan CRUD admin) dan berjalan dengan benar.

## Catatan untuk Laporan
Ingat untuk melengkapi laporan dengan:
- Desain database (ERD) & UML (use case diagram, dsb) — lihat `sql/schema.sql` sebagai acuan tabel.
- Mockup tampilan halaman.
- Screenshot hasil run aplikasi.
