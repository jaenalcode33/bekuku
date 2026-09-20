# identifikasi aktor sistem

1. Identifikasi Aktor (Actor Identification)
Aktor adalah pihak atau entitas luar yang berinteraksi langsung dengan sistem.

Pengunjung / Guest: Pengguna yang mengakses sistem tanpa melakukan login.

Pengguna / User (Pelanggan/Petani/Pengelola): Pengguna terdaftar yang memiliki hak akses terbatas untuk fitur utama aplikasi.

Admin / Pengelola Sistem: Pengguna dengan hak akses penuh untuk mengelola data master, mengontrol pengguna, dan melihat laporan seluruh aktivitas sistem.

2. Contoh User Story
User story mendeskripsikan kebutuhan fitur dari kacamata pengguna:

Untuk Pengunjung:

"Sebagai Pengunjung, saya ingin melihat halaman utama dan daftar katalog agar dapat mengetahui informasi layanan/produk yang disediakan."

Untuk Pengguna / Member:

"Sebagai Pengguna, saya ingin melakukan registrasi dan login agar dapat mengakses akun pribadi dan melakukan transaksi/aktivitas di dalam aplikasi."

"Sebagai Pengguna, saya ingin mengelola profil saya agar data diri selalu diperbarui."

Untuk Admin:

"Sebagai Admin, saya ingin mengelola (tambah, edit, hapus) data master agar informasi sistem tetap akurat."

"Sebagai Admin, saya ingin melihat laporan dan aktivitas seluruh akun agar dapat memantau penggunaan sistem secara keseluruhan."

3. Gambaran Use Case Diagram
Dalam pemodelan visual (Use Case Diagram), komponen di atas dihubungkan secara grafis:

Aktor di sisi kiri/kanan: Gambar ikon orang (Actor) mewakili Pengunjung, Pengguna, dan Admin.

Kotak Sistem (System Boundary): Kotak utama yang diberi nama sistem (bekuku).

Oval (Use Case): Fitur-fitur utama di dalam kotak sistem, seperti:

Melihat Informasi / Katalog (Dapat diakses oleh Pengunjung, Pengguna, Admin)

Registrasi Akun & Login

Kelola Profil

Kelola Data Master / Produk (Hanya terhubung ke Admin)

Melihat Laporan (Hanya terhubung ke Admin)