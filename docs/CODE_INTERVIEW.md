# Panduan Code Ownership Interview

## Peta file inti
- `routes/web.php`: URL bernama, kelompok `auth`, dan route Admin.
- `app/Http/Middleware/EnsureUserIsAdmin.php`: memeriksa role di server, bukan hanya menyembunyikan menu.
- `app/Http/Controllers/AuthController.php`: login dengan username/password, session regenerate dan logout invalidate.
- `app/Models`: field, cast, serta relasi Eloquent.
- `app/Services/InventoryTransactionService.php`: inti transaksi pembelian/penjualan/pembatalan.
- `app/Http/Controllers/PurchaseController.php` dan `SaleController.php`: validasi request, daftar, detail, dispatch service.
- `database/migrations`: skema; `DatabaseSeeder.php`: data demonstrasi fiktif.
- `resources/views`: Blade dan layout reusable `layouts/app.blade.php`.

## Konsep yang perlu dijelaskan
- MVC: route memetakan HTTP ke controller; controller validasi dan mengorkestrasi; model memetakan tabel/relasi; Blade merender respons.
- `hasMany` dipakai pada sisi satu-ke-banyak seperti Sale ke SaleItem; `belongsTo` ada di detail karena foreign key `sale_id` berada di tabel detail.
- Sale mengambil harga `selling_price` dari database, tidak menerima harga/total dari browser. Harga transaksi disalin ke `sale_items.unit_price` sebagai snapshot.
- Service membungkus header, detail, saldo, dan stock movement dalam `DB::transaction()`. Exception memicu rollback.
- `lockForUpdate()` memegang lock produk sampai transaksi selesai pada MySQL/InnoDB, sehingga dua penjualan tidak sama-sama memakai saldo stok lama. Tetap tes konkurensi pada MySQL, karena SQLite testing tidak sama dengan lock row MySQL.
- Pembatalan mengubah status setelah movement kompensasi; status completed menjadi cancelled hanya sekali. Pembelian tidak dapat dibatalkan bila stok yang diterima sudah terpakai.
- CSRF ditambahkan Blade `@csrf`, output default Blade escaped, route mengharuskan auth, Admin memakai middleware pada server, validasi Laravel dan FK menjaga integritas.

## Contoh tanya-jawab
1. **Mengapa total dihitung server?** Input browser dapat diubah. Sale membaca harga dari tabel products, mengalikan quantity, menjumlah subtotal dan menyimpan hasil di service.
2. **Di mana stok negatif dicegah?** Di `InventoryTransactionService::sale()`: produk dikunci, semua kuantitas diperiksa sebelum header/item disimpan; transaksi gagal jika stok kurang.
3. **Mengapa transaksi DB?** Agar header, item, stock update, dan audit movement tidak terpisah bila ada kegagalan.
4. **Bagaimana Admin membatasi Staff?** Route create/update/delete produk, master data, laporan, koreksi stok, dan cancel dibungkus middleware `admin`; detail transaksi juga memeriksa pemilik.
5. **Bagaimana pembatalan sale bekerja?** Admin memanggil `cancelSale`, status diverifikasi `completed`, lalu saldo dipulihkan dan movement kompensasi direkam di dalam transaksi DB.
6. **Apa tujuan snapshot harga?** Riwayat lama tetap menunjukkan harga saat transaksi walau harga master berubah.
7. **Bagaimana mencoba perubahan kecil?** Ubah batas minimum stok di form/validator atau factory, jalankan `php artisan test`, lalu uji dashboard dan validasi pada lingkungan database lokal.

Pelajari kode dan jelaskan dengan kata sendiri. README tidak menggantikan pemahaman implementasi.
