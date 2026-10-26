# Inventory & Sales Management System

Aplikasi UTS Pemrograman Web 2 berbasis Laravel 11 dan MySQL. Proyek awal memakai stok movement, master produk/kategori/pemasok, dan login username. Implementasi ini mempertahankan fitur yang relevan lalu menambahkan pembelian, penjualan, detail transaksi, pembatasan role, laporan transaksi, dan pengujian.

## Persyaratan
> Arsip sumber awal tidak menyertakan `composer.lock`; dependency diselesaikan saat `composer install`. Commit lockfile setelah menjalankan Composer pada environment PHP yang sesuai untuk reproducibility yang lebih kuat.
- PHP 8.2+ dengan ekstensi Laravel umum termasuk PDO MySQL, OpenSSL, Mbstring, XML, Ctype, JSON, Fileinfo, dan BCMath disarankan.
- Composer 2+, MySQL 8+/MariaDB yang mendukung InnoDB. SQLite juga digunakan oleh automated tests jika ekstensi PDO SQLite tersedia.
- Node.js tidak wajib untuk project ini: CSS disajikan dari `public/css/app.css`, tidak ada build pipeline frontend.

## Setup lokal dari repository bersih
```bash
git clone <URL-REPOSITORY>
cd inventory-laravel
composer install
cp .env.example .env
# Atur DB_CONNECTION=mysql dan DB_* sesuai database lokal
php artisan key:generate
php artisan migrate --seed
php artisan serve
```
Buat database kosong terlebih dahulu (default nama `inventory_app`). Buka `http://127.0.0.1:8000`. Jangan jalankan `migrate:fresh` pada database yang menyimpan data penting. `.env` tidak boleh dikomit.

## Akun demo lokal
- Admin: username `admin`, password `Admin12345!`
- Staff: username `staff`, password `Staff12345!`
- Email demo: `admin@example.test` dan `staff@example.test`

Akun dan kata sandi ini hanya untuk database demonstrasi lokal. Ganti sebelum deployment. Password disimpan dengan Laravel hashed cast.

## Fitur dan izin
- Login/logout, password hash, route internal dilindungi `auth`.
- Admin mengelola kategori, produk, pemasok, dan akun; melihat seluruh transaksi/laporan, koreksi stok manual, dan membatalkan transaksi.
- Staff membaca produk dan membuat pembelian/penjualan. Daftar/detail transaksi dibatasi ke transaksi miliknya. Pembatalan dan data master hanya Admin.
- Search SKU/nama, filter kategori, sorting dan pagination produk; filter nomor/status/tanggal dan pagination transaksi.
- Dashboard menghitung produk, transaksi selesai yang dapat dilihat user, penjualan hari/bulan ini, stok rendah berbasis `minimum_stock`, dan penjualan terakhir.
- Purchase dan sale memiliki header/detail. Harga jual/total penjualan dihitung dari data server. Stok dan stock movements diperbarui di `DB::transaction()` dengan `lockForUpdate()`.
- Produk yang sudah memiliki riwayat movement atau detail transaksi tidak dapat dihapus karena pemeriksaan aplikasi dan foreign key.

## Database dan relasi
Tabel: `users`, `categories`, `suppliers`, `products`, `product_suppliers`, `stock_movements`, `purchases`, `purchase_items`, `sales`, `sale_items`, dan tabel bawaan `password_reset_tokens`.

- Category `hasMany` Product; Product `belongsTo` Category.
- Supplier `hasMany` Purchase; Purchase `belongsTo` Supplier dan User, `hasMany` PurchaseItem.
- User `hasMany` Sale; Sale `belongsTo` User dan `hasMany` SaleItem.
- PurchaseItem/SaleItem `belongsTo` transaksi dan Product; Product `hasMany` kedua jenis item.
- `product_suppliers` dan `stock_movements` dipertahankan dari sistem lama. Movement adalah jejak audit stok, saldo stok tetap di `products.stock`.

ERD Mermaid: [`docs/ERD.md`](docs/ERD.md). Audit & pemetaan persyaratan: [`docs/UTS_AUDIT.md`](docs/UTS_AUDIT.md). Panduan interview: [`docs/CODE_INTERVIEW.md`](docs/CODE_INTERVIEW.md).

## Aturan stok
1. Purchase: supplier/item/kuantitas/harga beli divalidasi server. Header, item, stok bertambah, dan movement masuk dibuat atomik. Harga beli dicatat pada item sebagai snapshot.
2. Sale: produk dan kuantitas divalidasi server. Harga jual dibaca ulang dari DB, baris produk dikunci, stok diperiksa sebelum insert, kemudian detail, total, pengurangan stok, dan movement keluar tersimpan atomik. Satu item gagal berarti rollback semua.
3. Sale dibatalkan Admin: stok dipulihkan dan status menjadi `cancelled` dalam satu transaksi. Pembatalan ulang ditolak. Purchase dibatalkan Admin hanya jika stok masih cukup, karena pembelian dapat sudah terpakai untuk penjualan.
4. Status hanya `completed` atau `cancelled`; transaksi tidak diedit setelah dibuat. Pembatalan dicatat sebagai movement kompensasi.

## Testing
Automated tests disiapkan pada `tests/Feature/InventoryWorkflowTest.php` dan pemeriksaan smoke di `tests/Feature/SmokeTest.php`.
Jalankan pada environment PHP/Composer lengkap:
```bash
composer install
php artisan test
php artisan route:list
```
**Status verifikasi paket ini:** pengujian runtime belum dapat dijalankan pada lingkungan penyusunan karena executable PHP/Composer tidak tersedia. Karena itu tidak ada klaim test lulus. Lihat checklist manual dan test yang disiapkan pada `docs/UTS_AUDIT.md`; jalankan semua test di komputer dengan PHP 8.2+ dan PDO MySQL/SQLite sebelum demo.

## Screenshot untuk pengumpulan
Belum ada screenshot runtime yang diklaim. Setelah menjalankan aplikasi, ambil screenshot: (1) dashboard dan stok rendah, (2) master produk dengan search/filter, (3) form dan detail pembelian, (4) penjualan berhasil dan detail harga/total, (5) penjualan stok tidak cukup dengan error, (6) daftar transaksi/laporan filter, (7) staff ditolak pada URL Admin.

## Batasan yang diketahui
- UI transaksi menyediakan lima baris input; hanya satu atau lebih item terisi yang dikirim, tidak ada keranjang dinamis.
- Purchase cancellation memerlukan stok yang dibeli masih tersedia; tidak ada costing FIFO/akuntansi.
- Email ditambahkan nullable/unique pada migrasi tambahan untuk kompatibilitas data lama; login tetap menggunakan username.
- Perlu verifikasi penuh runtime dan UI pada mesin pengembangan, karena runtime PHP tidak tersedia saat audit/perubahan.
