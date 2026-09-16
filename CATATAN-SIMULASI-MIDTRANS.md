# Tutorial Simulasi QRIS Midtrans di BEKUKU

Dokumen ini menjelaskan cara menguji pembuatan QRIS Midtrans dari aplikasi
BEKUKU menggunakan environment **Sandbox**. Sandbox hanya untuk pengujian dan
tidak memindahkan uang sungguhan ke DANA atau rekening merchant.

## 1. Prasyarat

Pastikan hal berikut sudah tersedia:

- Laragon dan Apache berjalan.
- PHP memiliki extension `curl` aktif.
- Database BEKUKU sudah dapat digunakan.
- Aplikasi dapat dibuka melalui browser.
- Akun Midtrans Sandbox.

Gunakan dokumentasi resmi Midtrans untuk referensi tambahan:

- [Testing Payment on Sandbox](https://docs.midtrans.com/docs/testing-payment-on-sandbox)
- [Core API](https://docs.midtrans.com/docs/custom-interface-core-api)
- [HTTP Notification/Webhook](https://docs.midtrans.com/docs/https-notification-webhooks)

## 2. Membuat akun Midtrans Sandbox

1. Buka [dashboard.sandbox.midtrans.com](https://dashboard.sandbox.midtrans.com).
2. Daftar atau login.
3. Buka menu **Settings**.
4. Buka **Access Keys**.
5. Salin **Server Key** Sandbox.

Server Key Sandbox biasanya diawali dengan:

```text
SB-Mid-server-
```

Jangan gunakan Server Key Production untuk simulasi.

## 3. Mengatur environment variable Windows

Buka PowerShell, lalu jalankan perintah berikut. Ganti nilai Server Key dengan
key milik akun Anda:

```powershell
[Environment]::SetEnvironmentVariable(
    "MIDTRANS_ENVIRONMENT",
    "sandbox",
    "User"
)

[Environment]::SetEnvironmentVariable(
    "MIDTRANS_SERVER_KEY",
    "SB-Mid-server-ISI_SERVER_KEY_ANDA",
    "User"
)
```

Tutup dan buka kembali Laragon setelah mengubah environment variable. Apache
harus direstart agar dapat membaca konfigurasi baru.

Konfigurasi ini dibaca oleh:

```text
config/midtrans.php
```

Server Key hanya digunakan di server PHP. Jangan menuliskannya di JavaScript,
HTML, atau repository.

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

## 5. Membuat QRIS dari aplikasi BEKUKU

1. Login ke aplikasi BEKUKU.
2. Buka halaman **Transaksi Baru**.
3. Pilih customer jika diperlukan.
4. Pilih minimal satu produk.
5. Atur jumlah produk.
6. Pastikan **Total Belanja** lebih besar dari `Rp 0`.
7. Pilih metode pembayaran **QRIS**.
8. Tunggu proses **Menghubungkan ke Midtrans...**.
9. QRIS akan ditampilkan jika API Midtrans berhasil merespons.

Request dari browser dikirim ke:

```text
transactions/midtrans-qris.php
```

Endpoint tersebut memanggil Midtrans Core API menggunakan Server Key di sisi
server. Implementasinya berada di:

```text
config/midtrans.php
```

## 6. Memeriksa QRIS yang dihasilkan

QRIS yang dibuat harus menampilkan gambar barcode dari URL yang diberikan
Midtrans. Untuk memeriksa request:

1. Tekan `F12` di browser.
2. Buka tab **Network**.
3. Pilih QRIS pada form transaksi.
4. Cari request `midtrans-qris.php`.
5. Periksa response JSON.

Response sukses berisi data seperti:

```json
{
  "order_id": "BEKUKU-20260916102530-a1b2c3d4",
  "qr_code_url": "https://...",
  "transaction_status": "pending"
}
```

`qr_code_url` berasal dari Midtrans dan bukan URL yang dibuat sendiri oleh
aplikasi.

## 7. Cara simulasi pembayaran

Mode Sandbox tidak menggunakan saldo DANA sungguhan. Jangan menganggap
pembayaran Sandbox sebagai transfer nyata.

Gunakan mekanisme simulasi pembayaran yang tersedia pada dashboard dan
dokumentasi akun Midtrans Sandbox. Beberapa metode pembayaran Sandbox memiliki
instruksi atau data pembayaran khusus yang berbeda dari Production.

Jika QRIS Sandbox tidak dapat dipindai menggunakan aplikasi DANA atau aplikasi
bank biasa, hal tersebut dapat terjadi karena QRIS Sandbox memang ditujukan
untuk pengujian. Ikuti instruksi testing Midtrans, bukan melakukan pembayaran
ke QRIS Sandbox dengan uang asli.

## 8. Mengirim request pengujian langsung dengan cURL

Bagian ini hanya untuk memastikan Server Key dan koneksi API dapat digunakan.
Jalankan dari PowerShell, dan ganti Server Key:

```powershell
$serverKey = "SB-Mid-server-ISI_SERVER_KEY_ANDA"
$auth = [Convert]::ToBase64String(
    [Text.Encoding]::ASCII.GetBytes($serverKey + ":")
)
$body = @{
    payment_type = "qris"
    transaction_details = @{
        order_id = "BEKUKU-TEST-$(Get-Date -Format yyyyMMddHHmmss)"
        gross_amount = 10000
    }
    qris = @{
        acquirer = "gopay"
    }
} | ConvertTo-Json -Depth 5

Invoke-RestMethod `
    -Uri "https://api.sandbox.midtrans.com/v2/charge" `
    -Method Post `
    -Headers @{
        Authorization = "Basic $auth"
        Accept = "application/json"
    } `
    -ContentType "application/json" `
    -Body $body
```

Jika berhasil, Midtrans mengembalikan response JSON yang berisi status transaksi
dan action QRIS. Jika gagal, periksa Server Key, URL Sandbox, dan status akun.

## 9. Troubleshooting

### Pesan `MIDTRANS_SERVER_KEY belum dikonfigurasi`

Apache belum membaca environment variable. Pastikan:

- Nama variable tepat: `MIDTRANS_SERVER_KEY`.
- Nilainya diawali `SB-Mid-server-`.
- Laragon dan Apache sudah direstart.
- Variable dibuat untuk user Windows yang menjalankan Laragon.

### Pesan `Gagal menghubungi Midtrans`

Periksa:

- Koneksi internet.
- Apache tidak diblokir firewall.
- URL Sandbox dapat diakses.
- Extension PHP `curl` aktif.

### QRIS tidak muncul, tetapi halaman tetap terbuka

Buka Developer Tools dengan `F12`, lalu periksa:

- Tab **Console** untuk error JavaScript.
- Tab **Network** untuk response `midtrans-qris.php`.
- Status HTTP response.
- Isi field `error` pada response JSON.

### Response `401 Unauthorized`

Server Key salah, kosong, atau tertukar dengan Client Key. Endpoint server harus
menggunakan **Server Key**, bukan Client Key.

### Response nominal tidak valid

Pastikan ada produk yang dipilih dan total transaksi lebih besar dari nol.
Nominal dikirim dalam satuan Rupiah tanpa desimal, misalnya `10000`.

## 10. Batasan implementasi saat ini

Implementasi saat ini sudah:

- Membuat QRIS melalui Midtrans Core API.
- Menggunakan nominal sesuai total transaksi.
- Menampilkan barcode dari Midtrans.
- Menyimpan Server Key hanya di sisi server.

Namun, status transaksi BEKUKU saat ini masih disimpan sebagai `selesai` ketika
form transaksi berhasil dikirim. Status tersebut belum diverifikasi melalui
notifikasi pembayaran Midtrans.

Untuk Production, alur yang disarankan adalah:

1. Buat transaksi BEKUKU dengan status `pending`.
2. Simpan `order_id` Midtrans.
3. Terima HTTP Notification/Webhook dari Midtrans.
4. Verifikasi `signature_key`.
5. Ubah transaksi menjadi `selesai` hanya jika status pembayaran berhasil.
6. Sediakan halaman pengecekan status pembayaran.

Jangan menganggap transaksi sudah dibayar hanya karena QRIS berhasil dibuat.

## 11. Checklist simulasi

- [ ] Akun Midtrans Sandbox sudah dibuat.
- [ ] Server Key Sandbox sudah disalin.
- [ ] `MIDTRANS_ENVIRONMENT=sandbox`.
- [ ] `MIDTRANS_SERVER_KEY` sudah diatur.
- [ ] Laragon dan Apache sudah direstart.
- [ ] PHP extension `curl` aktif.
- [ ] Produk dipilih pada transaksi.
- [ ] Total transaksi lebih besar dari nol.
- [ ] Metode QRIS dipilih.
- [ ] Request `midtrans-qris.php` berhasil.
- [ ] Barcode QRIS tampil.
- [ ] Pengujian pembayaran mengikuti instruksi Sandbox Midtrans.

## 12. Menggunakan gambar QRIS statis

Selain QRIS Midtrans, aplikasi menyediakan metode **QRIS Gambar** untuk
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
