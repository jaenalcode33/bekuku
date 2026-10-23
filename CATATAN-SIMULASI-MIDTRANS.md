# Tutorial Simulasi Midtrans Snap Checkout di BEKUKU

Dokumen ini menjelaskan cara menguji pembayaran Midtrans Snap dari aplikasi
BEKUKU menggunakan environment **Sandbox**. Sandbox hanya untuk pengujian dan
tidak memindahkan uang sungguhan.

## 1. Prasyarat

Pastikan hal berikut sudah tersedia:

- Laragon dan Apache berjalan.
- PHP memiliki extension `curl` aktif.
- Database BEKUKU sudah dapat digunakan.
- Aplikasi dapat dibuka melalui browser.
- Akun Midtrans Sandbox.

Gunakan dokumentasi resmi Midtrans untuk referensi tambahan:

- [Account Overview](https://docs.midtrans.com/docs/midtrans-account)
- [Snap](https://docs.midtrans.com/docs/snap)
- [Midtrans Payment Simulator](https://simulator.sandbox.midtrans.com/)
- [Testing Payment on Sandbox](https://docs.midtrans.com/docs/testing-payment-on-sandbox)
- [HTTP Notification/Webhook](https://docs.midtrans.com/docs/https-notification-webhooks)

## 2. Membuat akun Midtrans Sandbox

1. Buka [dashboard.sandbox.midtrans.com](https://dashboard.sandbox.midtrans.com).
2. Daftar atau login.
3. Lengkapi informasi akun di **Settings > General Settings**.
4. Pastikan pilihan environment di dashboard menunjukkan **Sandbox**.
5. Buka **Settings > Access Keys**.
6. Salin **Server Key** dan **Client Key** Sandbox.
7. Buka **Settings > Snap Checkout > Payment methods**, aktifkan QRIS atau
   metode simulasi lain yang ingin diuji, lalu simpan perubahan.

Gunakan Server Key yang ditampilkan di dashboard Sandbox. Format prefix dapat
berbeda antar akun; tentukan environment berdasarkan dashboard dan konfigurasi
`MIDTRANS_ENVIRONMENT`, bukan dengan menebak dari prefix key.

## 3. Mengatur konfigurasi melalui `.env`

Salin `.env.example` menjadi `.env` di folder utama aplikasi. Isi environment,
Server Key, dan Client Key dari dashboard Sandbox:

```text
MIDTRANS_ENVIRONMENT=sandbox
MIDTRANS_SERVER_KEY=ISI_SERVER_KEY_SANDBOX
MIDTRANS_CLIENT_KEY=ISI_CLIENT_KEY_SANDBOX
```

Saat pindah server, salin `.env.example` lalu isi key milik server tersebut.
Untuk beralih ke Production, ubah environment menjadi `production` dan ganti
kedua key dengan pasangan Production. Perubahan `.env` dibaca pada request
berikutnya; restart Apache hanya diperlukan jika konfigurasi PHP server
memang mensyaratkannya. File `.env` tidak boleh dimasukkan ke repository.

Server Key digunakan PHP untuk membuat token dan memeriksa status pembayaran.
Client Key dimuat oleh Snap.js di browser. Gunakan pasangan key Sandbox untuk
Sandbox dan pasangan Production untuk Production; jangan menaruh Server Key
di HTML atau JavaScript.

## 4. Mengecek konfigurasi PHP

Pastikan extension `curl` aktif pada PHP yang digunakan Laragon. Buka file
`php.ini` dari menu Laragon, lalu pastikan baris berikut tidak diawali tanda
titik koma:

```ini
extension=curl
```

Restart Apache setelah mengubah `php.ini`.

Untuk memastikan PHP yang aktif memiliki cURL, jalankan:

```powershell
php -m | Select-String curl
```

Jika berhasil, output akan menampilkan:

```text
curl
```

## 5. Membayar melalui Snap Checkout

1. Login ke aplikasi BEKUKU.
2. Buka halaman **Transaksi Baru**.
3. Pilih customer jika diperlukan.
4. Pilih minimal satu produk.
5. Atur jumlah produk.
6. Pastikan **Total Belanja** lebih besar dari `Rp 0`.
7. Pilih **Midtrans Snap Checkout**.
8. Klik **Simpan Transaksi**. Popup Snap akan menampilkan metode pembayaran
   yang aktif untuk environment akun Anda.
9. Selesaikan pembayaran di popup. BEKUKU hanya menyimpan transaksi setelah
   memeriksa status lunas langsung ke Midtrans.

Backend membuat token Snap melalui endpoint:

```text
transactions/midtrans-snap-token.php
```

Status pembayaran diverifikasi menggunakan Server Key di sisi server sebelum
stok dikurangi dan transaksi dicatat.

## 6. Simulasi pembayaran Sandbox

Untuk metode QRIS, ikuti instruksi simulasi yang tersedia pada popup Snap. Jika
simulator meminta **QR Code Image URL**, salin alamat gambar QRIS dari checkout,
tempelkan di [QRIS Payment Simulator](https://simulator.sandbox.midtrans.com/qris/index),
lalu klik **Scan QR**. Sandbox tidak memotong saldo sungguhan. Jangan membayar
QR Sandbox menggunakan aplikasi bank atau dompet digital dengan dana nyata.

## 7. Troubleshooting

### Pesan `MIDTRANS_SERVER_KEY belum dikonfigurasi`

Pastikan file `.env` berada di folder utama aplikasi dan memuat:

- Nama variable tepat: `MIDTRANS_SERVER_KEY`.
- Nilainya disalin dari dashboard Sandbox yang sedang digunakan.
- Key tidak memiliki spasi tambahan atau tanda kutip yang ikut tersimpan.

### Pesan `Gagal menghubungi Midtrans`

Periksa:

- Koneksi internet.
- Apache tidak diblokir firewall.
- URL Sandbox dapat diakses.
- Extension PHP `curl` aktif.

### Snap tidak muncul atau pembayaran belum tercatat

Buka Developer Tools dengan `F12`, lalu periksa:

- Tab **Console** untuk error JavaScript.
- Tab **Network** untuk response `midtrans-snap-token.php`.
- Status HTTP response.
- Isi field `error` pada response JSON.

### Response `401 Unauthorized`

Pastikan Server Key berasal dari **Access Keys** environment yang sama dengan
`MIDTRANS_ENVIRONMENT`. Server Key dipakai di backend; Client Key dipakai untuk
memuat Snap.js. Sandbox dan Production memiliki key serta data transaksi yang
berbeda.

### Memastikan environment dan Server Key sepasang

Pastikan nilai `MIDTRANS_ENVIRONMENT` sesuai dengan environment dashboard tempat
Server Key disalin:

- `sandbox` dipasangkan dengan Server Key dari dashboard Sandbox.
- `production` hanya dipasangkan dengan Server Key Production dan hanya saat
  toko siap menerima pembayaran nyata.

Jangan menentukan environment hanya dari prefix Server Key, dan jangan
mengubah environment ke Production untuk mengatasi error Sandbox.

### Pesan `Payment channel is not activated`

Periksa **Settings > Snap Checkout > Payment methods** dan aktifkan metode
tersebut untuk environment yang sama. Jika QRIS tidak tersedia di daftar,
hubungi Midtrans Support. Aktivasi Sandbox dan Production berbeda.

### Response nominal tidak valid

Pastikan ada produk yang dipilih dan total transaksi lebih besar dari nol.
Nominal dikirim dalam satuan Rupiah tanpa desimal, misalnya `10000`.

## 8. Batasan implementasi saat ini

Implementasi saat ini sudah:

- Membuat token Snap di server menggunakan Server Key.
- Memuat popup Snap menggunakan Client Key.
- Memverifikasi status lunas ke Midtrans sebelum mencatat penjualan dan
  mengurangi stok.
- Mempertahankan QRIS gambar statis sebagai metode terpisah.

Callback browser saja tidak dianggap sebagai bukti pembayaran. Untuk menerima
notifikasi otomatis jika pengguna menutup browser setelah membayar, aplikasi
perlu URL webhook HTTPS publik yang dapat dijangkau Midtrans. `localhost` hanya
dapat digunakan untuk uji coba lokal.

## 9. Checklist simulasi

- [ ] Akun Midtrans Sandbox sudah dibuat.
- [ ] Server Key Sandbox sudah disalin.
- [ ] `MIDTRANS_ENVIRONMENT=sandbox`.
- [ ] `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` berasal dari dashboard
      environment yang sama.
- [ ] Laragon dan Apache sudah direstart.
- [ ] PHP extension `curl` aktif.
- [ ] Produk dipilih pada transaksi.
- [ ] Total transaksi lebih besar dari nol.
- [ ] Snap Checkout diaktifkan di dashboard Sandbox.
- [ ] Metode pembayaran yang ingin diuji diaktifkan di pengaturan Snap.
- [ ] Metode **Midtrans Snap Checkout** dipilih.
- [ ] Popup Snap tampil dan pembayaran Sandbox berhasil.
- [ ] BEKUKU mencatat transaksi setelah status lunas diverifikasi.
- [ ] Pengujian pembayaran mengikuti instruksi Sandbox Midtrans.

## 10. Menggunakan gambar QRIS statis

Selain Snap Checkout, aplikasi menyediakan metode **QRIS Gambar** untuk
menampilkan QRIS statis milik merchant.

1. Simpan gambar QRIS resmi, misalnya:

   ```text
   assets/images/qris-merchant.png
   ```

2. Atur environment variable:

   ```text
   BEKUKU_QRIS_IMAGE_URL=assets/images/qris-merchant.png
   ```

3. Restart Apache/Laragon.
4. Pada transaksi baru, pilih **QRIS Gambar**.
5. Masukkan jumlah pembayaran sesuai total transaksi.

QRIS statis tidak membawa nominal transaksi secara otomatis. Jangan membuat
gambar QRIS sendiri dari nomor DANA; gunakan gambar QRIS resmi dari DANA,
Midtrans, atau penyedia QRIS merchant Anda.
