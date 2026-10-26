Inventory Management System
Project ini dibuat untuk memenuhi tugas kuliah. Aplikasi Inventory
Management System ini digunakan untuk membantu mengelola data barang
agar lebih mudah dicatat dan dipantau.
Teknologi yang Digunakan
- Laravel
- PHP
- MySQL
- HTML, CSS, dan JavaScript
- Bootstrap (jika digunakan pada tampilan project)
Fitur Aplikasi
Beberapa fitur yang tersedia pada aplikasi ini menyesuaikan dengan
source code project, di antaranya:
- Login pengguna
- Mengelola data barang
- Menampilkan daftar barang
- Menambah, mengubah, dan menghapus data
- Menyimpan data ke database MySQL
Cara Menjalankan Project
1. Persiapan
Pastikan sudah menginstal: - XAMPP - Composer - PHP yang sesuai dengan
versi Laravel pada project - Web browser
2. Ekstrak Project
Ekstrak folder project ke dalam direktori htdocs, contohnya:
C:\xampp\htdocs\inventory-laravel
3. Buka Terminal
Masuk ke folder project melalui terminal:
cd C:\xampp\htdocs\inventory-laravel
4. Instal Dependency
Jalankan perintah:
composer install
5. Atur File Environment
Jika file .env belum tersedia, salin file contoh:
copy .env.example .env
Kemudian buat application key:
php artisan key:generate
6. Buat Database
1. Jalankan Apache dan MySQL dari XAMPP.
2. Buka phpMyAdmin melalui http://localhost/phpmyadmin.
3. Buat database sesuai nama yang digunakan pada konfigurasi .env.
4. Sesuaikan pengaturan database pada file .env, contohnya:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uts_inventory_sales
DB_USERNAME=root
DB_PASSWORD=
Nama database di atas hanya contoh. Jika project menggunakan nama
database lain, sesuaikan dengan konfigurasi project.
7. Jalankan Migration
Jika project menyediakan migration untuk membuat tabel, jalankan:
php artisan migrate
Jika project menyediakan seeder dan memang diperlukan, jalankan:
php artisan db:seed
8. Jalankan Aplikasi
Jalankan perintah:
php artisan serve
Buka alamat yang ditampilkan terminal, biasanya:

http://127.0.0.1:8000
Struktur Folder Laravel
- app/ --- berisi model dan logika aplikasi.
- routes/ --- berisi pengaturan route atau alamat halaman.
- resources/views/ --- berisi tampilan halaman.
- database/migrations/ --- berisi file untuk membuat struktur tabel
  database.
- public/ --- berisi file publik seperti CSS, JavaScript, dan
  gambar.
- .env --- berisi konfigurasi aplikasi dan database.
Kendala yang Mungkin Terjadi
Database tidak ditemukan

Pastikan database sudah dibuat di phpMyAdmin dan nama pada .env sudah
sesuai.

Perintah php artisan tidak berjalan

Pastikan terminal sedang berada di folder utama project dan PHP sudah
terpasang serta dikenali oleh terminal.

Dependency belum tersedia

Jalankan composer install dari folder utama project.

Perubahan konfigurasi belum terbaca

Coba jalankan:
php artisan config:clear
Pengujian
Setelah aplikasi berhasil dijalankan, lakukan pengecekan secara manual:
1. Buka halaman aplikasi.
2. Coba login menggunakan akun yang memang tersedia pada project.
3. Periksa apakah data barang dapat ditampilkan.
4. Coba tambah, ubah, dan hapus data jika fitur tersebut tersedia.
5. Pastikan perubahan data tersimpan di database.
Hasil pengujian perlu diisi berdasarkan pengujian yang benar-benar
dilakukan.
Penutup
Project ini dibuat sebagai latihan penerapan framework Laravel dan
database MySQL dalam membangun aplikasi pengelolaan inventaris. Semoga
aplikasi ini bisa berjalan dengan baik dan membantu memahami dasar
pengembangan aplikasi web.
