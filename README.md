Aplikasi Sistem Informasi Akademik untuk tugas Projek Pemrograman Internet.
Backend: **PHP native (tanpa framework)*

- Admin: kelola data Mahasiswa, Dosen, dan Mata Kuliah (CRUD)
- Mahasiswa: Isi KRS, lihat nilai, lihat perhitungan IPK
- Dosen: input nilai mahasiswa u
- IPK dihitung otomatis dari nilai & SKS yang tersimpan
1. Salin folder ini ke `htdocs` (XAMPP) atau `www` (Laragon), misalnya jadi `sia`.
2. Buat database dengan mengimpor `sql/schema.sql` lewat phpMyAdmin atau:
3. Sesuaikan kredensial database di `config/dat

| Role      | Username   | Password      |
|-----------|-----------|---------------|
| Admin     | admin      | password123   |
| Dosen     | dosen1     | password123   |
| Mahasiswa | 21552001   | password123   |

> Catatan: saat admin menambah data Mahasiswa/Dosen baru, akun login otomatis
> dibuat dengan **password default = NIM/NIP** masing-masing.

           Halaman khusus admin (CRUD mahasiswa, dosen, matakuliah)
├── dosen/             Halaman khusus dosen (dashboard, input nilai)
├── mahasiswa/         Halaman khusus mahasiswa (dashboard, KRS, nilai, IPK)
├── includes/          File bersama (auth, header, footer, helper)
├── config/            Konfigurasi koneksi database
├── assets/            CSS & JS
├── sql/schema.sql      Skema database + data awal (seed)
├── login.php / logout.php / index.php

Aplikasi ini sudah diuji end-to-end secara otomatis (login ketiga role, isi KRS,
input nilai oleh dosen, perhitungan IPK, dan CRUD admin) dan berjalan dengan benar.

- Desain database (ERD) & UML (use case diagram, dsb) — lihat `sql/schema.sql` sebagai acuan tabel.
- Mockup tampilan halaman.
- Screenshot hasil run aplikasi.
