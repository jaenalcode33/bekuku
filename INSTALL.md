# Tutorial Instalasi BEKUKU POS

Ikuti langkah berikut dari awal sampai aplikasi bisa digunakan.

## Yang perlu disiapkan

- Windows
- Laragon
- Browser, misalnya Chrome
- Source code BEKUKU POS

## Langkah 1: Install dan jalankan Laragon

1. Install Laragon.
2. Buka Laragon.
3. Klik **Start All**.
4. Pastikan Apache dan MySQL sudah berjalan.

Jika tombolnya berubah menjadi **Stop**, berarti service sudah aktif.

## Langkah 2: Masukkan folder BEKUKU

Salin folder proyek ke folder `www` Laragon.

Lokasi yang benar:

```text
C:\laragon\www\Bekuku
```

Pastikan file berikut ada:

```text
C:\laragon\www\Bekuku\index.php
C:\laragon\www\Bekuku\login.php
C:\laragon\www\Bekuku\database\bekuku.sql
```

## Langkah 3: Buat database

1. Buka browser.
2. Masuk ke:

   ```text
   http://localhost/phpmyadmin
   ```

3. Klik menu **Databases**.
4. Buat database baru dengan nama:

   ```text
   bekuku
   ```

5. Gunakan collation:

   ```text
   utf8mb4_unicode_ci
   ```

6. Klik **Create**.

## Langkah 4: Impor tabel BEKUKU

1. Di phpMyAdmin, klik database **bekuku**.
2. Klik tab **Import**.
3. Klik **Choose File**.
4. Pilih file:

   ```text
   C:\laragon\www\Bekuku\database\bekuku.sql
   ```

5. Scroll ke bawah.
6. Klik **Import** atau **Go**.

Jika berhasil, akan muncul tabel seperti:

```text
categories
suppliers
customers
products
transactions
transaction_details
stock_movements
purchases
purchase_details
batches
users
audit_logs
```

Untuk database BEKUKU yang sudah berisi data lama, jalankan juga
`database/migrations/001_security_users_audit.sql` pada database `bekuku`.
Koneksi aplikasi juga melakukan bootstrap idempoten untuk dua tabel keamanan
tersebut, sehingga data lama tetap dipertahankan.

## Langkah 5: Periksa koneksi database

Buka file:

```text
C:\laragon\www\Bekuku\config\database.php
```

Untuk Laragon dengan pengaturan standar, isinya harus seperti ini:

```php
$host = "localhost";
$dbname = "bekuku";
$username = "root";
$password = "";
```

Password kosong ditulis dengan dua tanda kutip:

```php
$password = "";
```

Jika MySQL Anda memakai password, isi sesuai password MySQL:

```php
$password = "password_mysql_anda";
```

## Konfigurasi QRIS Midtrans

Isi Server Key Midtrans dan environment pada environment variable berikut:

```text
MIDTRANS_ENVIRONMENT=sandbox
MIDTRANS_SERVER_KEY=SB-Mid-server-...
```

QRIS akan dibuat melalui endpoint Midtrans ketika metode pembayaran QRIS
dipilih pada transaksi. Gunakan `sandbox` untuk pengujian dan `production`
setelah akun Midtrans aktif. Server Key hanya boleh disimpan di environment
server dan jangan ditulis ke JavaScript atau repository.

## Konfigurasi QRIS gambar

Jika ingin menggunakan gambar QRIS statis milik merchant, tambahkan opsi
berikut:

```text
BEKUKU_QRIS_IMAGE_URL=assets/images/qris-merchant.png
```

Simpan file gambar QRIS pada folder tersebut, lalu pilih metode **QRIS Gambar**
di transaksi baru. Gambar QRIS harus berasal dari merchant/provider resmi.
Nominal pembayaran tetap perlu diisi manual karena QRIS statis tidak mengubah
nominal secara otomatis.

Simpan file setelah selesai.

## Langkah 6: Buka aplikasi

Buka alamat berikut:

```text
http://localhost/Bekuku/
```

Landing page BEKUKU akan tampil.

Untuk masuk ke aplikasi, buka:

```text
http://localhost/Bekuku/login.php
```

## Langkah 7: Login

Akun awal dibuat di database dari `database/bekuku.sql` (atau otomatis saat
koneksi aplikasi pertama kali berhasil). Password tetap disimpan sebagai hash,
bukan teks biasa. Setelah login sebagai admin, gunakan menu **Pengguna** untuk
menambah, menonaktifkan, atau mengganti password pengguna.

Username yang tersedia:

```text
admin
kasir
gudang
```

Password menggunakan hash sehingga tidak ditulis sebagai password biasa di dokumentasi.

Jika password belum diketahui, buat hash password baru:

1. Buka Terminal Laragon.
2. Jalankan:

   ```powershell
   php -r "echo password_hash('PasswordBaru123!', PASSWORD_DEFAULT), PHP_EOL;"
   ```

3. Salin hasil hash.
4. Gunakan menu Pengguna setelah login admin untuk mengganti password, atau
   perbarui kolom `users.password_hash` di database secara manual.

## Langkah 8: Tes aplikasi

Setelah berhasil login, coba urutan berikut:

1. Buka menu **Kategori** dan buat kategori.
2. Buka menu **Supplier** dan buat supplier.
3. Buka menu **Produk** dan buat produk.
4. Isi stok produk.
5. Buka menu **Transaksi** dan buat transaksi.
6. Buka **Laporan Penjualan**.
7. Buka **Laporan Stok**.

Jika semua halaman terbuka dan data tersimpan, instalasi berhasil.

## Jika muncul error koneksi database

Jika muncul pesan **Koneksi database gagal**, periksa hal berikut:

1. Laragon sudah menjalankan MySQL.
2. Nama database adalah `bekuku`.
3. Username database adalah `root`.
4. Password di `config/database.php` benar.
5. File `database/bekuku.sql` sudah diimpor.
6. Tabel database sudah terlihat di phpMyAdmin.

## Jika halaman tidak bisa dibuka

Periksa lokasi folder. Folder harus berada tepat di:

```text
C:\laragon\www\Bekuku
```

Bukan seperti:

```text
C:\laragon\www\Bekuku\Bekuku
```

Lalu pastikan Apache Laragon aktif dan coba buka kembali:

```text
http://localhost/Bekuku/
```

## Jika CSS atau tampilan tidak berubah

Tekan tombol berikut di browser:

```text
Ctrl + F5
```

Tombol ini memuat ulang file CSS dan JavaScript terbaru.

## Struktur folder asset

Semua CSS dan JavaScript aplikasi berada di dalam folder `assets`. Entry point landing dan dashboard juga sudah dipisahkan:

```text
assets/
├── css/
│   ├── adminlte.min.css
│   ├── style.css
│   ├── products-index.css
│   └── ...
├── js/
│   ├── adminlte.min.js
│   ├── ui.js
│   └── ...
├── landing/
│   ├── css/landing.css
│   └── js/landing.js
└── dashboard/
    └── js/dashboard.js
```

Halaman utama:

```text
landing/index.php
dashboard/index.php
```

File `index.php` di folder utama hanya mengarahkan pengunjung ke landing page atau dashboard sesuai status login.

## Checklist selesai

- [ ] Laragon aktif
- [ ] Folder berada di `C:\laragon\www\Bekuku`
- [ ] Database `bekuku` dibuat
- [ ] File `bekuku.sql` berhasil diimpor
- [ ] Tabel terlihat di phpMyAdmin
- [ ] Koneksi database sesuai
- [ ] Landing page bisa dibuka
- [ ] Login bisa dibuka
- [ ] Dashboard bisa dibuka
- [ ] Produk bisa dibuat
- [ ] Transaksi bisa dibuat
- [ ] Laporan bisa dibuka

## Catatan keamanan

- Jangan membagikan password database.
- Jangan memasukkan password asli ke file dokumentasi.
- Gunakan password MySQL yang kuat untuk server online.
- Jangan menggunakan user `root` untuk server produksi.
- Backup database secara rutin.
- Session dilindungi cookie HttpOnly, SameSite, timeout 30 menit, dan token CSRF.
- Perubahan autentikasi dan transaksi penting dicatat di tabel `audit_logs`.
- File error aplikasi dicatat di `storage/logs/app.log`; batasi akses folder ini
  pada konfigurasi Apache produksi.
