BEKUKU.

Point of Sale & Inventory Management System for Frozen Food.

About The Project.

BEKUKU adalah aplikasi berbasis web untuk membantu mengelola operasional bisnis frozen food dalam satu sistem.

Aplikasi ini menggabungkan Point of Sale (POS) dan Inventory Management dengan sistem batch inventory dan FEFO (First Expired, First Out).

Aplikasi ini dirancang untuk membantu proses:

• Pengelolaan produk.
• Pengelolaan kategori.
• Pengelolaan supplier.
• Pembelian barang.
• Manajemen batch.
• Pengelolaan stok.
• Penjualan / POS.
• Customer.
• Pembayaran.
• Piutang.
• Laporan.
• Manajemen pengguna.

Main Features.

Dashboard.

Dashboard digunakan untuk menampilkan informasi penting mengenai aktivitas bisnis dan kondisi inventory secara terpusat.

Product Management.

Fitur untuk mengelola data produk:

• Tambah produk.
• Edit produk.
• Hapus produk.
• Kategori produk.
• Harga produk.
• Stok produk.
• Informasi produk.

Category Management.

Fitur untuk mengelompokkan produk berdasarkan kategori:

• Tambah kategori.
• Edit kategori.
• Hapus kategori.
• Pengelompokan produk.

Supplier Management.

Digunakan untuk mengelola data supplier:

• Data supplier.
• Informasi supplier.
• Riwayat pembelian.

Purchase Management.

Sistem pembelian digunakan untuk mencatat barang yang masuk ke inventory.

Informasi pembelian meliputi:

• Supplier.
• Produk.
• Jumlah pembelian.
• Harga beli.
• Total pembelian.
• Batch.
• Tanggal masuk.
• Tanggal kedaluwarsa.

Setiap pembelian dapat menghasilkan batch baru sehingga stok dapat dilacak berdasarkan batch.

Batch Inventory.

BEKUKU menggunakan sistem inventory berbasis batch.

Setiap barang yang masuk dapat memiliki informasi:

Product.
→ Batch.
→ Stock.
→ Expired Date.

Dengan sistem batch, stok dapat dilacak berdasarkan:

• Produk.
• Batch.
• Jumlah stok.
• Tanggal masuk.
• Tanggal kedaluwarsa.

FEFO Inventory System.

BEKUKU menerapkan konsep FEFO — First Expired, First Out.

Batch dengan tanggal kedaluwarsa yang paling dekat akan diprioritaskan ketika stok digunakan.

Contoh:

Batch A → Expired 10 Jan 2027 → Stock 10.
Batch B → Expired 20 Feb 2027 → Stock 20.
Batch C → Expired 15 Mar 2027 → Stock 30.

Jika terjadi penjualan sebanyak 12 produk:

Batch A → 10.
Batch B → 2.

Dengan konsep FEFO, penggunaan stok dapat disesuaikan dengan tanggal kedaluwarsa sehingga pengelolaan barang menjadi lebih terstruktur.

Sales / Point of Sale.

Fitur penjualan digunakan untuk memproses transaksi customer.

Fitur meliputi:

• Pembuatan transaksi.
• Pemilihan customer.
• Pemilihan produk.
• Perhitungan subtotal.
• Perhitungan total.
• Metode pembayaran.
• Pengurangan stok.
• Penerapan FEFO.
• Penyimpanan batch yang digunakan.
• Riwayat transaksi.

Customer Management.

Digunakan untuk mengelola data customer:

• Tambah customer.
• Edit customer.
• Hapus customer.
• Data customer.
• Riwayat transaksi.
• Informasi pembayaran.
• Pengelolaan piutang.

Payment & Piutang.

BEKUKU dapat mencatat transaksi berdasarkan status pembayaran:

• Lunas.
• Sebagian Dibayar.
• Belum Lunas.

Sistem juga mendukung pembayaran menggunakan QRIS melalui payment gateway.

Reports.

Modul laporan digunakan untuk membantu monitoring aktivitas bisnis.

Laporan dapat digunakan untuk melihat:

• Penjualan.
• Inventory.
• Stok.
• Transaksi.
• Aktivitas operasional.

User Management.

Sistem menyediakan fitur pengelolaan pengguna:

• Login.
• Logout.
• User management.
• Pengelolaan akses pengguna.

Business Flow.

SUPPLIER.
↓
PURCHASE.
↓
PRODUCT BATCH.
↓
INVENTORY.
↓
FEFO.
↓
SALES / POS.
↓
PAYMENT.
↓
CUSTOMER.
↓
REPORT.

Tech Stack.

PHP — Backend.
MySQL / MariaDB — Database.
JavaScript — Frontend Interaction.
HTML5 — Structure.
CSS3 — Styling.
Bootstrap — UI Components.
AdminLTE — Dashboard Interface.
Laragon — Local Development.
Git — Version Control.
GitHub — Repository.
Midtrans — QRIS Payment.

Project Structure.

Bekuku.
• assets.
• categories.
• config.
• customers.
• dashboard.
• database.
• includes.
• landing.
• products.
• purchases.
• reports.
• suppliers.
• transactions.
• users.
• tests.
• .gitignore.
• INSTALL.md.
• README.md.
• index.php.
• login.php.
• logout.php.

Requirements.

Untuk menjalankan BEKUKU secara lokal:

• Windows.
• PHP 8.x.
• MySQL / MariaDB.
• Apache.
• Git.
• Web Browser.
• Laragon atau local development environment lainnya.

Installation.

1. Clone Repository.

git clone https://github.com/jaenalcode33/bekuku.git

2. Masuk ke Folder Project.

cd bekuku

3. Letakkan Project.

C:\laragon\www\Bekuku

4. Buat Database.

Database:

bekuku

Kemudian import database dari:

database/bekuku.sql

5. Konfigurasi Database.

Sesuaikan konfigurasi database pada:

config/database.php

6. Jalankan Laragon.

Aktifkan Apache dan MySQL / MariaDB.

Kemudian buka:

http://localhost/Bekuku

Security.

Jangan menyimpan credential sensitif secara langsung di repository.

Contohnya:

• Database password.
• API Key.
• Midtrans Server Key.
• Secret Key.
• Credential lainnya.

Gunakan environment variable atau konfigurasi lokal.

Jangan commit file .env yang berisi credential asli.

Testing.

Testing dasar tersedia pada:

tests/smoke.php

Testing dapat dikembangkan untuk memvalidasi:

• Database connection.
• Product management.
• Purchase transaction.
• Stock management.
• FEFO.
• Sales transaction.
• Payment.
• Data validation.

Project Status.

Active Development.

BEKUKU masih dalam tahap pengembangan dan beberapa fitur dapat terus mengalami peningkatan.

Future Development.

• Barcode Scanner.
• Low Stock Notification.
• Expired Product Notification.
• PDF Export.
• Excel Export.
• Automated Database Backup.
• Advanced Role & Permission.
• Extended Audit Log.
• Automated Testing.
• Improved UI/UX.

Project Goals.

BEKUKU dikembangkan sebagai sistem terintegrasi untuk membantu bisnis frozen food dalam mengelola:

PRODUCT.
↓
PURCHASE.
↓
BATCH.
↓
INVENTORY.
↓
FEFO.
↓
SALES / POS.
↓
PAYMENT.
↓
REPORT.

Tujuan utamanya adalah membuat proses pengelolaan inventory dan transaksi menjadi lebih terstruktur, mudah dipantau, dan dapat ditelusuri.

Developer.

Jaenal.

Web Developer.

GitHub:

https://github.com/jaenalcode33

Repository:

https://github.com/jaenalcode33/bekuku

License.

Project ini dibuat sebagai project pembelajaran, pengembangan, dan portfolio.