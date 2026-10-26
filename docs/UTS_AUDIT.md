# Audit awal, gap, dan pemetaan requirement UTS

## Acuan
Bahan presentasi `BAHAN UTS PW.pptx` (28 slide): skenario usaha, role, master data, purchase/sales, skema, Eloquent, transaksi atomik, keamanan, dashboard, testing, deliverables, code ownership, dan Definition of Done.

## Kondisi project ZIP sebelum perubahan

### Sudah tersedia dan terbukti dari kode
- Laravel 11 / PHP >=8.2 dideklarasikan dalam `composer.json`; `composer.lock` tidak ada dalam arsip, sehingga versi dependency belum terkunci; dependensi runtime belum tersedia di sandbox.
- Routes login, logout, dashboard dan resource category/product/supplier; middleware `auth` dan alias `admin`.
- Model `User`, `Category`, `Product`, `Supplier`, `StockMovement`; migrations users, categories, suppliers, products, pivot pemasok, dan stok movement.
- Password model menggunakan hashed cast; controller login meregenerasi session; logout invalidate session.
- CRUD dasar master data dengan validasi, SKU unik, query search/filter/pagination untuk produk; produk tidak membolehkan penghapusan jika punya movement.
- StockController memakai `DB::transaction`, `lockForUpdate`, cek stok OUT, update stok, dan catat movement.
- Blade layout reusable dan CSRF; `.env.example` dan `.gitignore` tersedia.

### Tersedia tetapi perlu diperbaiki
- Seeder akun demo hanya memakai username; role sudah admin/staff, namun email belum ada.
- Satu `products.price` dipakai untuk seluruh harga; belum ada purchase price, selling price, dan minimum stock.
- Dashboard hanya menghitung master data dan batas stok statis 5; belum ada angka transaksi/penjualan.
- Laporan hanya ringkasan stok dan tidak memiliki daftar transaksi/tanggal/status.
- Produk dapat ditambah/edit oleh staff melalui route resource tanpa admin middleware.
- Stock in/out adalah movement generik, bukan purchase/sale header-detail.
- README menyarankan migrate:fresh tanpa peringatan kontekstual yang cukup kuat.

### Belum tersedia
- Tabel/model/controller/view purchase, purchase_items, sales, sale_items.
- Alur multi-item transaksi dengan header, snapshot harga, pembatalan idempotent, dan rollback teruji.
- Daftar/detail transaksi dengan filter tanggal/status serta relasi transaksi pada produk.
- Fitur testing alur utama (semula hanya smoke test `/up`).
- ERD final dan panduan code ownership.

### Risiko awal teridentifikasi
- Staff dapat memanggil URL CRUD produk langsung, meskipun UI berpotensi menyembunyikan tombol.
- Pemeriksaan stok generik tidak menyimpan riwayat penjualan/pembelian yang dapat ditelusuri sebagai transaksi bisnis.
- `stock <= 5` tidak dapat dikonfigurasi per produk.
- `price` tunggal tidak memenuhi pemisahan harga beli dan jual.
- Runtime PHP/Composer tidak ada pada lingkungan audit; hasil eksekusi migration/test belum dapat dibuktikan.

## Perubahan/implementasi saat ini
- Migration tambahan `000006` memberi email dan harga beli/jual/minimum stock sambil mempertahankan kolom `price` lama sebagai kompatibilitas; data lama dipetakan dari `price`.
- Migration `000007` menambahkan empat tabel transaksi dengan FK, unique number, decimal money, timestamp/status, dan index.
- Eloquent: relasi Purchase, Sale, PurchaseItem, SaleItem; relasi tambahan User/Supplier/Product.
- Role gate route: staff hanya daftar/detail produk, transaksi milik sendiri, create purchase/sale; admin mengelola master/produk, laporan, koreksi stok dan pembatalan.
- Service mengunci produk terurut, hitung harga/total server-side, perbarui stok dan stock movement atomik; cancellation records compensation movement and prevents double application.
- Dashboard aktual, search/filter/pagination, transaction lists/detail, seed data, 6 feature tests, ERD, interview guide, README.

## Pemetaan slide/rubrik ke implementasi
- Authentication / role: `AuthController`, `EnsureUserIsAdmin`, route grouping, role checks in transaction detail; otomatis dites sebagian.
- Master data: controllers/views existing retained; product edit/create admin-only and pricing/stock fields validated; duplicate SKU validation+unique DB index.
- Purchase: `PurchaseController`, `InventoryTransactionService::purchase`, `purchases` + `purchase_items`; stock in and movement atomic.
- Sale: `SaleController`, `InventoryTransactionService::sale`; DB price, stock row lock, insufficiency validation, atomic rollback, unique sale number.
- Database/Eloquent: migration 6/7, model relations, FK/indexes, decimal and integer quantity types.
- Dashboard/search/report: `DashboardController`, product index, transaction controllers, `ReportController`, pagination with query string.
- Security/reproducibility: CSRF/Blade escaping, auth/admin middleware, `.env.example`, `.gitignore`, README setup and demo accounts.
- Deliverables: ERD and ownership notes included; screenshots not captured because application runtime unavailable.

## Verifikasi yang benar-benar dilakukan
- Source ZIP (76 entries) was extracted and source reviewed; slides text was read (28 slides).
- Static inspection confirmed relevant route/controller/migration/model/view files and required code was added.
- Automated runtime tests, PHP lint, route:list, migrations and browser screenshots **not run**: PHP and Composer executables are not installed in the available sandbox. Do not interpret authored tests as passing tests.

## Required next checks on a PHP machine
1. `composer install`, configure `.env` for a fresh MySQL database, `php artisan migrate --seed`, then `php artisan route:list`.
2. `php artisan test` and fix any failures before claiming readiness.
3. Test with a clean MySQL/InnoDB database: login/logout, Admin vs Staff direct URL, duplicate SKU/negative price, purchase stock-in, sales stock-out, manipulated client price, insufficient stock, one bad row rollback, repeated cancellation, purchase cancel after stock consumption, filters/search/pagination, and seeded relations.
4. Try concurrent sales of the same last units using two sessions; confirm lock behavior in MySQL.
5. Capture requested screenshots from the running app. No screenshot was fabricated.

## Current acceptance risk
The critical code paths have been implemented but not runtime-verified. Because the original workspace contains no `vendor` and this environment lacks PHP/Composer, it is not accurate to call the result UTS-ready until those checks pass on the student's machine.
