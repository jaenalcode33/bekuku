# BEKUKU

### Point of Sale & Inventory Management System for Frozen Food

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white) ![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white) ![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black) ![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white) ![GitHub](https://img.shields.io/badge/GitHub-Repository-181717?style=for-the-badge&logo=github&logoColor=white)

---

## About BEKUKU

**BEKUKU** adalah aplikasi berbasis web yang dibuat untuk membantu bisnis frozen food dalam mengelola penjualan, stok, pembelian, customer, pembayaran, dan laporan dalam satu sistem.

BEKUKU menghubungkan proses mulai dari barang masuk, pencatatan stok, pengelolaan batch, transaksi penjualan, pembayaran, hingga laporan.

Sistem inventory BEKUKU menggunakan konsep **FEFO (First Expired, First Out)** untuk membantu memprioritaskan stok berdasarkan tanggal kedaluwarsa.

Dengan konsep tersebut, BEKUKU tidak hanya berfungsi sebagai kasir digital, tetapi juga membantu mengelola perjalanan barang dari supplier hingga menjadi transaksi penjualan.

---

## What Makes BEKUKU Different?

Perbedaan sederhananya:

> **POS biasanya berfokus pada barang yang terjual, sedangkan BEKUKU mengelola perjalanan barang dari masuk hingga terjual.**

Pada POS sederhana:

Barang
   ↓
Penjualan
   ↓
Pembayaran

Sedangkan BEKUKU menghubungkan proses yang lebih lengkap:

Supplier
   ↓
Pembelian
   ↓
Stok + Batch
   ↓
Tanggal Kedaluwarsa
   ↓
Penjualan
   ↓
Pembayaran
   ↓
Laporan

Dengan alur tersebut, data pembelian, stok, dan penjualan dapat saling terhubung dalam satu sistem.

Why Choose BEKUKU?

Bayangkan BEKUKU seperti satu buku pengelolaan usaha digital.

Bukan hanya mencatat barang yang terjual, tetapi juga membantu mengetahui:

Barang datang dari mana
Berapa stok yang tersedia
Stok berasal dari batch mana
Kapan barang masuk
Kapan barang kedaluwarsa
Barang apa saja yang terjual
Bagaimana status pembayarannya
Bagaimana hasil penjualannya

Semua proses tersebut dikelola dalam satu aplikasi.

BEKUKU membantu menyatukan penjualan dan pengelolaan stok dalam satu sistem.

Main Features
Dashboard

Menampilkan informasi penting mengenai kondisi bisnis dan inventory dalam satu tampilan.

Product Management

Digunakan untuk mengelola data produk seperti:

Tambah produk
Edit produk
Hapus produk
Kategori produk
Harga produk
Stok produk
Informasi produk
Category Management

Digunakan untuk mengatur dan mengelompokkan produk berdasarkan kategori.

Fitur meliputi:

Tambah kategori
Edit kategori
Hapus kategori
Supplier Management

Digunakan untuk menyimpan dan mengelola informasi supplier serta data pembelian.

Purchase Management

Digunakan untuk mencatat barang yang masuk ke dalam inventory.

Data pembelian meliputi:

Supplier
Produk
Jumlah pembelian
Harga beli
Total pembelian
Batch
Tanggal masuk
Tanggal kedaluwarsa

Setiap pembelian dapat menghasilkan batch baru sehingga stok dapat ditelusuri berdasarkan batch.

Batch Inventory

BEKUKU menggunakan sistem inventory berbasis batch.

Product
   ↓
Batch
   ↓
Stock
   ↓
Expired Date

Setiap batch dapat menyimpan informasi seperti:

Produk
Jumlah stok
Tanggal masuk
Tanggal kedaluwarsa

Hal ini membantu mengetahui asal dan kondisi stok yang tersedia.

FEFO Inventory System

BEKUKU menggunakan konsep FEFO (First Expired, First Out).

Artinya, stok dengan tanggal kedaluwarsa yang lebih dekat dapat diprioritaskan untuk digunakan terlebih dahulu.

Contoh:

Batch A → Expired 10 Jan 2027 → Stock 10
Batch B → Expired 20 Feb 2027 → Stock 20
Batch C → Expired 15 Mar 2027 → Stock 30

Jika terdapat penjualan sebanyak 12 produk:

Batch A → 10
Batch B →  2

Dengan konsep ini, pengelolaan stok menjadi lebih teratur dan dapat membantu mengurangi risiko stok lama tertinggal.

Sales / Point of Sale

Digunakan untuk memproses transaksi penjualan.

Fitur meliputi:

Membuat transaksi
Memilih customer
Memilih produk
Menghitung subtotal
Menghitung total
Memilih metode pembayaran
Mengurangi stok
Menggunakan batch
Menyimpan riwayat transaksi
Customer Management

Digunakan untuk mengelola informasi customer.

Fitur meliputi:

Tambah customer
Edit customer
Hapus customer
Data customer
Riwayat transaksi
Informasi pembayaran
Pengelolaan piutang
Payment & Piutang

BEKUKU dapat mencatat status pembayaran transaksi:

Lunas
Sebagian Dibayar
Belum Lunas

Sistem juga mendukung pembayaran menggunakan QRIS melalui payment gateway.

Reports

Modul laporan digunakan untuk membantu melihat aktivitas bisnis.

Informasi yang dapat dipantau meliputi:

Penjualan
Inventory
Stok
Transaksi
Aktivitas operasional
User Management

BEKUKU menyediakan fitur untuk mengelola pengguna sistem:

Login
Logout
User management
Pengaturan akses pengguna
Business Flow
                 SUPPLIER
                    ↓
                PURCHASE
                    ↓
               PRODUCT BATCH
                    ↓
                INVENTORY
                    ↓
                  FEFO
                    ↓
               SALES / POS
                 ↓     ↓
              CUSTOMER PAYMENT
                    ↓
                 REPORT
System Concept

BEKUKU menggunakan konsep Batch-Based Inventory Management.

Product
PRODUCT
   |
   +-- CATEGORY
   |
   +-- BATCH
          |
          +-- STOCK
          +-- ENTRY DATE
          +-- EXPIRED DATE
Sales
CUSTOMER
    ↓
  SALE
    ↓
SALE DETAIL
    |
    +-- PRODUCT
    |
    +-- BATCH

Dengan menyimpan informasi batch pada transaksi, penggunaan stok dapat ditelusuri dengan lebih jelas.

Tech Stack
Technology	Usage
PHP	Backend
MySQL / MariaDB	Database
JavaScript	Frontend Interaction
HTML5	Structure
CSS3	Styling
Bootstrap	UI Components
AdminLTE	Dashboard Interface
Laragon	Local Development
Git	Version Control
GitHub	Repository
Midtrans	QRIS Payment
Project Structure
Bekuku/
|
+-- assets/
|   +-- css/
|   +-- js/
|   +-- images/
|
+-- categories/
+-- config/
+-- customers/
+-- dashboard/
+-- database/
+-- includes/
+-- landing/
+-- products/
+-- purchases/
+-- reports/
+-- suppliers/
+-- transactions/
+-- users/
+-- tests/
|
+-- .gitignore
+-- INSTALL.md
+-- README.md
+-- index.php
+-- login.php
+-- logout.php
Requirements

Untuk menjalankan BEKUKU secara lokal, diperlukan:

Windows
PHP 8.x
MySQL / MariaDB
Apache
Git
Web Browser
Laragon atau local development environment lainnya
Installation
1. Clone Repository
git clone https://github.com/jaenalcode33/bekuku.git
2. Masuk ke Folder Project
cd bekuku
3. Letakkan Project

Jika menggunakan Laragon:

C:\laragon\www\Bekuku
4. Buat Database

Buat database:

bekuku

Kemudian import database dari:

database/bekuku.sql
5. Konfigurasi Database

Sesuaikan konfigurasi database pada:

config/database.php
6. Jalankan Laragon

Aktifkan:

Apache
MySQL / MariaDB

Kemudian buka:

http://localhost/Bekuku
Security

Jangan menyimpan informasi sensitif secara langsung di repository.

Contohnya:

Database password
API Key
Midtrans Server Key
Secret Key
Credential lainnya

Gunakan environment variable atau konfigurasi lokal.

Jangan commit file .env yang berisi credential asli.

Testing

Testing dasar tersedia pada:

tests/smoke.php

Testing dapat dikembangkan untuk memeriksa:

Database connection
Product management
Purchase transaction
Stock management
FEFO
Sales transaction
Payment
Data validation
Project Status

Active Development

BEKUKU masih dalam tahap pengembangan dan dapat terus dikembangkan sesuai kebutuhan.

Future Development

Beberapa fitur yang direncanakan untuk dikembangkan:

 Barcode Scanner
 Low Stock Notification
 Expired Product Notification
 PDF Export
 Excel Export
 Automated Database Backup
 Advanced Role & Permission
 Extended Audit Log
 Automated Testing
 Improved UI/UX
Project Goals

BEKUKU dibuat untuk membantu menyederhanakan pengelolaan bisnis frozen food melalui satu sistem yang saling terhubung.

Alur utamanya:

PRODUCT
   ↓
PURCHASE
   ↓
BATCH
   ↓
INVENTORY
   ↓
FEFO
   ↓
SALES / POS
   ↓
PAYMENT
   ↓
REPORT

Tujuannya adalah membuat data penjualan dan inventory lebih mudah dikelola, dipantau, dan ditelusuri.

Developer
Jaenal

Web Developer

GitHub:

https://github.com/jaenalcode33

Repository:

https://github.com/jaenalcode33/bekuku

License

Project ini dibuat sebagai project pembelajaran, pengembangan, dan portfolio.

<p align="center"> <strong>BEKUKU</strong> <br> Point of Sale & Inventory Management System <br><br> Made by <strong>Jaenal</strong> </p>