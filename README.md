# BEKUKU

### Point of Sale & Inventory Management System for Frozen Food

**BEKUKU** adalah aplikasi **Point of Sale (POS) dan Inventory Management** yang dirancang untuk membantu pengelolaan bisnis frozen food secara terintegrasi.

BEKUKU menggabungkan pengelolaan produk, kategori, supplier, pembelian, stok berbasis batch, tanggal kedaluwarsa, sistem FEFO, customer, piutang, penjualan, laporan, dan pembayaran QRIS dalam satu sistem.

> BEKUKU — Sistem POS dan Manajemen Inventori untuk Bisnis Frozen Food.

---

## Project Overview

BEKUKU dikembangkan sebagai sistem informasi untuk membantu proses operasional toko frozen food.

Sistem dirancang tidak hanya untuk mencatat jumlah stok, tetapi juga untuk mengelola stok berdasarkan **batch dan tanggal kedaluwarsa**.

Konsep **FEFO (First Expired, First Out)** digunakan dalam proses pengeluaran stok sehingga batch dengan tanggal kedaluwarsa yang lebih dekat dapat diprioritaskan.

---

## Features

### Dashboard

* Ringkasan informasi bisnis.
* Informasi produk.
* Informasi stok.
* Ringkasan transaksi.
* Informasi penjualan.
* Monitoring aktivitas operasional.

### Product Management

* Tambah produk.
* Edit produk.
* Hapus produk.
* Daftar produk.
* Kategori produk.
* Harga jual.
* Informasi stok.
* Riwayat pergerakan stok.

### Category Management

* Tambah kategori.
* Edit kategori.
* Hapus kategori.
* Pengelompokan produk berdasarkan kategori.

### Supplier Management

* Data supplier.
* Informasi supplier.
* Riwayat pembelian berdasarkan supplier.

### Purchase Management

* Pencatatan pembelian.
* Detail pembelian.
* Supplier.
* Produk.
* Jumlah barang.
* Harga beli.
* Batch produk.
* Tanggal kedaluwarsa.
* Stok barang masuk.

### Batch Management

Setiap barang yang masuk dapat disimpan berdasarkan batch.

Informasi batch meliputi:

* Produk.
* Jumlah stok.
* Tanggal masuk.
* Tanggal kedaluwarsa.
* Riwayat penggunaan batch.

### FEFO

BEKUKU menerapkan konsep:

**First Expired, First Out**

Sistem memprioritaskan batch dengan tanggal kedaluwarsa paling dekat ketika stok digunakan untuk transaksi.

Contoh:

```text
Produk: Nugget Ayam

Batch       Expired          Stock
-----------------------------------
BATCH-001   10 Jan 2027      10
BATCH-002   20 Feb 2027      20
BATCH-003   15 Mar 2027      30
```

Jika customer membeli 12 produk:

```text
BATCH-001 -> 10
BATCH-002 -> 2
BATCH-003 -> 0
```

Stok setelah transaksi:

```text
BATCH-001 -> 0
BATCH-002 -> 18
BATCH-003 -> 30
```

Detail transaksi menyimpan batch yang digunakan sehingga penggunaan stok dapat ditelusuri.

### Sales / POS

* Pembuatan transaksi penjualan.
* Pemilihan customer.
* Pemilihan produk.
* Perhitungan subtotal.
* Perhitungan total.
* Pemilihan metode pembayaran.
* Pemrosesan stok.
* Penerapan FEFO.
* Penyimpanan batch yang digunakan.
* Riwayat transaksi.

### Automatic Stock Management

Stok diperbarui berdasarkan aktivitas pembelian dan penjualan.

Alur utama:

```text
Purchase
    |
    v
Product Batch
    |
    v
Inventory
    |
    v
Sales Transaction
    |
    v
FEFO Selection
    |
    v
Batch Stock Decrease
```

### Customer Management

* Data customer.
* Informasi customer.
* Riwayat transaksi.
* Informasi pembayaran.
* Pengelolaan piutang.

### Debt / Piutang

Sistem dapat mencatat transaksi customer yang belum dibayar penuh.

Status pembayaran dapat digunakan untuk membedakan:

```text
Lunas
Belum Lunas
Sebagian Dibayar
```

### QRIS Payment

BEKUKU menyediakan dukungan pembayaran QRIS melalui integrasi payment gateway.

Konfigurasi payment gateway menggunakan environment variable sehingga credential tidak ditulis langsung pada source code.

Contoh:

```env
MIDTRANS_ENVIRONMENT=sandbox
MIDTRANS_SERVER_KEY=YOUR_SERVER_KEY
```

Credential asli tidak boleh dimasukkan ke repository.

### Reports

BEKUKU menyediakan modul laporan untuk membantu melihat data operasional.

Laporan meliputi:

* Laporan penjualan.
* Laporan stok.
* Riwayat transaksi.
* Informasi persediaan.

### Authentication & User Management

* Login.
* Logout.
* User management.
* Pengelolaan akses pengguna.

---

## System Concept

BEKUKU menggunakan pendekatan **batch-based inventory**.

Konsep hubungan produk dan batch:

```text
PRODUCT
    |
    +------ CATEGORY
    |
    +------ BATCH
              |
              +------ STOCK
```

Pada transaksi penjualan:

```text
CUSTOMER
    |
    v
  SALE
    |
    v
SALE_DETAIL
    |
    +------ PRODUCT
    |
    +------ BATCH
```

Dengan menyimpan `batch_id` pada detail penjualan, sistem dapat mengetahui batch mana yang digunakan dalam suatu transaksi.

---

## FEFO Workflow

Proses FEFO:

```text
Customer membeli produk
          |
          v
Cari batch aktif
          |
          v
Urutkan berdasarkan expired date
          |
          v
Ambil batch dengan expired terdekat
          |
          v
Apakah stok mencukupi?
       /        \
     Ya          Tidak
     |             |
     v             v
Kurangi       Gunakan batch
stok batch    berikutnya
     |             |
     +------ + ----+
            |
            v
      Transaksi selesai
```

---

## Business Flow

```text
SUPPLIER
    |
    v
PURCHASE
    |
    v
PRODUCT BATCH
    |
    v
INVENTORY
    |
    v
FEFO
    |
    v
SALES / POS
    |
    +-------- CUSTOMER
    |
    +-------- PAYMENT
    |
    v
REPORT
```

---

## Database

Database utama:

```text
database/bekuku.sql
```

Migration:

```text
database/
├── bekuku.sql
└── migrations/
    ├── 001_security_users_audit.sql
    └── 002_qris_image_payment_method.sql
```

Konsep database mencakup:

* Products
* Categories
* Suppliers
* Customers
* Purchases
* Purchase Details
* Batches
* Sales
* Sale Details
* Debt
* Debt Payments
* Users
* Audit / supporting tables

---

## Tech Stack

| Technology      | Purpose                  |
| --------------- | ------------------------ |
| PHP             | Backend                  |
| MySQL / MariaDB | Database                 |
| HTML5           | Markup                   |
| CSS3            | Styling                  |
| JavaScript      | Client-side interaction  |
| AdminLTE        | Admin interface          |
| Laragon         | Local development        |
| Git             | Version control          |
| GitHub          | Repository               |
| Midtrans        | QRIS payment integration |

---

## Project Structure

```text
Bekuku/
|
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
|
├── categories/
|
├── config/
│   ├── app.php
│   ├── database.php
│   └── midtrans.php
|
├── customers/
|
├── dashboard/
|
├── database/
│   ├── bekuku.sql
│   └── migrations/
|
├── includes/
|
├── landing/
|
├── products/
|
├── purchases/
|
├── reports/
|
├── suppliers/
|
├── transactions/
|
├── users/
|
├── tests/
|
├── .gitignore
├── INSTALL.md
└── index.php
```

---

## Requirements

Untuk menjalankan BEKUKU secara lokal:

* Windows
* PHP
* MySQL atau MariaDB
* Apache
* Git
* Browser modern
* Laragon atau local development environment lainnya

---

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/jaenalcode33/bekuku.git
```

Masuk ke folder project:

```bash
cd bekuku
```

### 2. Letakkan Project

Jika menggunakan Laragon:

```text
C:\laragon\www\bekuku
```

### 3. Setup Database

Buat database:

```text
bekuku
```

Kemudian import:

```text
database/bekuku.sql
```

### 4. Konfigurasi Database

Sesuaikan konfigurasi:

```text
config/database.php
```

dengan konfigurasi MySQL/MariaDB lokal.

### 5. Environment Configuration

Jika project menggunakan `.env`, buat file:

```text
.env
```

Contoh:

```env
DB_HOST=127.0.0.1
DB_NAME=bekuku
DB_USER=root
DB_PASS=

MIDTRANS_ENVIRONMENT=sandbox
MIDTRANS_SERVER_KEY=YOUR_SERVER_KEY
```

Jangan memasukkan credential asli ke repository GitHub.

### 6. Jalankan Laragon

Aktifkan:

```text
Apache
MySQL / MariaDB
```

Kemudian buka:

```text
http://localhost/bekuku
```

---

## Git Workflow

### Update Project

Setelah melakukan perubahan:

```bash
git status
```

Tambahkan perubahan:

```bash
git add .
```

Buat commit:

```bash
git commit -m "Deskripsi perubahan"
```

Upload ke GitHub:

```bash
git push
```

### Mengambil Update

Jika bekerja menggunakan komputer lain:

```bash
git pull
```

### Clone ke Komputer Baru

```bash
git clone https://github.com/jaenalcode33/bekuku.git
```

---

## Security

Informasi sensitif tidak boleh disimpan langsung di repository.

Contohnya:

* Database password.
* API key.
* Midtrans Server Key.
* Secret key.
* Credential lainnya.

Gunakan environment variable atau konfigurasi lokal.

File `.env` telah dimasukkan ke `.gitignore` agar tidak ikut di-commit.

---

## Testing

Testing dasar tersedia pada:

```text
tests/smoke.php
```

Testing dapat dikembangkan untuk memvalidasi:

* Koneksi database.
* Proses transaksi.
* Pengurangan stok.
* FEFO.
* Pembayaran.
* Validasi data.
* Integritas transaksi.

---

## Development Roadmap

* [x] Product Management
* [x] Category Management
* [x] Supplier Management
* [x] Customer Management
* [x] Purchase Management
* [x] Batch Management
* [x] Stock Management
* [x] FEFO
* [x] Sales / POS
* [x] Sales Reports
* [x] Stock Reports
* [x] Customer Debt
* [x] User Management
* [x] QRIS Integration
* [ ] Barcode Scanner
* [ ] Low Stock Notification
* [ ] Expired Product Notification
* [ ] PDF Export
* [ ] Excel Export
* [ ] Automated Database Backup
* [ ] Advanced Role & Permission
* [ ] Expanded Audit Log
* [ ] Improved UI/UX
* [ ] Automated Testing

---

## Project Goals

BEKUKU dikembangkan untuk menyediakan sistem pengelolaan frozen food yang mencakup:

1. Point of Sale.
2. Inventory Management.
3. Batch Management.
4. Expired Date Management.
5. FEFO Inventory System.
6. Purchase Management.
7. Sales Management.
8. Customer Management.
9. Debt Management.
10. Payment Management.
11. Reporting.
12. User Management.

---

## Developer

**Jaenal**

GitHub:

https://github.com/jaenalcode33

Repository:

https://github.com/jaenalcode33/bekuku

---

## License

Project ini dapat menggunakan lisensi sesuai kebutuhan pengembang.

Jika repository akan digunakan sebagai project open-source, tambahkan file `LICENSE` dan tentukan jenis lisensi yang digunakan.

---

## BEKUKU

**Point of Sale & Inventory Management System for Frozen Food**

Aplikasi untuk mengelola produk, batch, stok, expired date, FEFO, pembelian, penjualan, customer, pembayaran, dan laporan dalam satu sistem.
