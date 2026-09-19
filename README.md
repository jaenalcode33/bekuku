# BEKUKU

### Point of Sale & Inventory Management System for Frozen Food

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![GitHub](https://img.shields.io/badge/GitHub-Repository-181717?style=for-the-badge&logo=github&logoColor=white)

---

## About The Project

**BEKUKU** adalah aplikasi berbasis web yang dikembangkan untuk membantu mengelola operasional bisnis frozen food dalam satu sistem.

BEKUKU menggabungkan sistem **Point of Sale (POS)** dan **Inventory Management** dengan pengelolaan stok berbasis batch serta konsep **FEFO (First Expired, First Out)**.

Aplikasi ini dirancang untuk membantu proses:

- Pengelolaan produk
- Pengelolaan kategori
- Pengelolaan supplier
- Pembelian barang
- Manajemen batch
- Pengelolaan stok
- Penjualan / POS
- Pengelolaan customer
- Pembayaran
- Piutang
- Laporan
- Manajemen pengguna

---

## Main Features

### Dashboard

Dashboard digunakan untuk menampilkan informasi penting mengenai aktivitas bisnis dan kondisi inventory secara terpusat.

### Product Management

Fitur untuk mengelola data produk:

- Tambah produk
- Edit produk
- Hapus produk
- Kategori produk
- Harga produk
- Stok produk
- Informasi produk

### Category Management

Fitur untuk mengelompokkan produk berdasarkan kategori:

- Tambah kategori
- Edit kategori
- Hapus kategori
- Pengelompokan produk

### Supplier Management

Digunakan untuk mengelola data supplier:

- Data supplier
- Informasi supplier
- Riwayat pembelian

### Purchase Management

Sistem pembelian digunakan untuk mencatat barang yang masuk ke inventory.

Informasi pembelian meliputi:

- Supplier
- Produk
- Jumlah pembelian
- Harga beli
- Total pembelian
- Batch
- Tanggal masuk
- Tanggal kedaluwarsa

Setiap pembelian dapat menghasilkan batch baru sehingga stok dapat dilacak berdasarkan batch.

---

## Batch Inventory

BEKUKU menggunakan sistem inventory berbasis batch.

Setiap barang yang masuk dapat memiliki informasi:

```text
Product
   |
   v
Batch
   |
   v
Stock
   |
   v
Expired Date