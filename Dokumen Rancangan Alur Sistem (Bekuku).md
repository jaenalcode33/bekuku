# Dokumen Rancangan Alur Sistem (Bekuku)

## 1. Activity Diagram

### A. Activity Diagram: Proses Login Pengguna

* **Pengguna:** Membuka halaman login.
* **Sistem:** Menampilkan form login (Email dan Password).
* **Pengguna:** Memasukkan Email dan Password.
* **Pengguna:** Mengklik tombol Login.
* **Sistem:** Memvalidasi kredensial pengguna ke database.
* **Sistem (Pengecekan Data):**
  * **Jika Data Tidak Valid:** Sistem menampilkan pesan error *"Email atau Password Salah"*, lalu mengarahkan kembali ke form login.
  * **Jika Data Valid:** Sistem memeriksa hak akses pengguna (*Role*).
* **Sistem (Pengecekan Role):**
  * **Jika Role = Admin:** Sistem mengarahkan ke Dashboard Admin.
  * **Jika Role = Member:** Sistem mengarahkan ke Dashboard Member.
* **Pengguna:** Masuk ke halaman utama sesuai hak akses.

---

### B. Activity Diagram: Pengelolaan Data Master (CRUD) oleh Admin

* **Admin:** Memilih menu kelola data pada Dashboard Admin.
* **Sistem:** Mengambil data dari database dan menampilkan tabel daftar data.
* **Admin:** Memilih salah satu aksi operasional:
  * **Pilihan Tambah Data:**
    1. Admin mengklik tombol **Tambah Data**.
    2. Sistem menampilkan form input baru.
    3. Admin mengisi form dan mengklik tombol **Simpan**.
    4. Sistem menyimpan data baru ke database.
  * **Pilihan Edit Data:**
    1. Admin memilih item data dan mengklik tombol **Edit**.
    2. Sistem menampilkan form edit berisi data lama.
    3. Admin mengubah informasi data dan mengklik tombol **Update**.
    4. Sistem memperbarui data di database.
  * **Pilihan Hapus Data:**
    1. Admin memilih item data dan mengklik tombol **Hapus**.
    2. Sistem menampilkan dialog konfirmasi.
    3. Admin mengklik tombol **Ya / Hapus**.
    4. Sistem menghapus data dari database.
* **Sistem:** Menampilkan notifikasi sukses dan memperbarui tampilan tabel data.
* **Admin:** Menerima konfirmasi dan melihat tampilan data terbaru.

---

## 2. Workflow Sistem (Alur Kerja Operasional)

### Tahap 1: Onboarding dan Autentikasi
1. Pengunjung membuka situs web.
2. Pengunjung mendaftar akun baru (*Register*) dengan mengisi data diri.
3. Sistem menyimpan data akun baru ke database.
4. Pengguna melakukan login menggunakan Email dan Password terdaftar.

### Tahap 2: Aktivitas Utama Pengguna (Member)
1. Pengguna mengakses Dashboard Member setelah berhasil login.
2. Pengguna memilih fitur utama, seperti melihat katalog, menginput data transaksi, atau mengedit profil.
3. Pengguna mengirimkan input atau transaksi ke sistem.
4. Sistem memproses data, memperbarui database, dan memberikan bukti atau notifikasi konfirmasi.

### Tahap 3: Manajemen Data dan Pengawasan (Admin)
1. Admin masuk ke sistem melalui halaman Login Admin.
2. Admin memantau transaksi dan aktivitas pengguna melalui Dashboard Admin.
3. Admin memverifikasi data atau transaksi yang masuk.
4. Admin melakukan kelola data master (**Tambah, Edit, Hapus**) jika ada perubahan data pada sistem.

### Tahap 4: Pelaporan dan Rekapitulasi Data
1. Sistem mengolah seluruh riwayat transaksi secara otomatis.
2. Admin membuka menu **Laporan** pada panel kontrol.
3. Sistem menyajikan rekap data dalam bentuk tabel atau grafik ringkasan.
4. Admin mengunduh atau mencetak laporan rekapitulasi sesuai kebutuhan.