<?php

require_once __DIR__ . "/../config/app.php";

if (!bekuku_is_authenticated()) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>BEKUKU POS - Kelola Bisnis Lebih Mudah</title>
        <link rel="stylesheet" href="<?= bekuku_url('assets/css/adminlte.min.css') ?>">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091655">
        <link rel="stylesheet" href="<?= bekuku_url('assets/landing/css/landing.css') ?>?v=2026091721">
        <script src="<?= bekuku_url('assets/landing/js/landing.js') ?>?v=2026091721" defer></script>
    </head>
    <body class="bekuku-landing-page landing-reference-page">
        <div class="landing-shell">
            <nav class="bekuku-landing-nav">
                <a class="bekuku-auth-brand" href="<?= bekuku_url() ?>">
                    <span class="bekuku-brand-mark"><i class="bi bi-snow2"></i></span>
                    <span>BEKUKU <small>POINT OF SALE</small></span>
                </a>
                <div class="landing-nav-actions">
                    <a href="#fitur"><i class="bi bi-arrow-up-right-circle"></i> Fitur</a>
                    <a href="#tentang">Tentang</a>
                    <a class="bekuku-landing-login" href="<?= bekuku_url('login.php') ?>">Login <i class="bi bi-box-arrow-in-right"></i></a>
                </div>
                <button class="landing-menu-toggle" type="button" aria-expanded="false" aria-controls="landing-navigation">
                    <i class="bi bi-list"></i>
                </button>
            </nav>
            <main class="landing-reference-hero">
                <div class="landing-reference-copy">
                    <p class="landing-reference-badge"> Sistem POS Modern untuk Bisnis Frozen Food</p>
                    <h1>Kelola Bisnis Frozen Food<br><em>Lebih Mudah,<br>Lebih Untung!</em></h1>
                    <p class="bekuku-landing-description">Aplikasi Point of Sale (POS) yang dirancang khusus untuk toko frozen food. Kelola transaksi, stok, dan laporan penjualan dalam satu sistem yang simpel, cepat, dan modern.</p>
                    <div class="landing-reference-cta">
                        <a class="bekuku-landing-cta" href="<?= bekuku_url('login.php') ?>"></i> Mulai Sekarang <i class="bi bi-arrow-right"></i></a>
                        <a class="landing-demo-button" href="#fitur"><i class="bi bi-play-circle"></i> Lihat Demo</a>
                    </div>
                    <div class="bekuku-landing-trust">
                        <span><i class="bi bi-check-circle-fill"></i> Mudah Digunakan</span>
                        <span><i class="bi bi-check-circle-fill"></i> Aman & Stabil</span>
                        <span><i class="bi bi-check-circle-fill"></i> Support 24/7</span>
                    </div>
                </div>
                <div class="landing-reference-visual">
                    <div class="landing-glow"></div>
                    <div class="landing-dashboard-card">
                        <div class="landing-dashboard-top"><strong><i class="bi bi-snow2"></i> BEKUKU <small>POINT OF SALE</small></strong><span><b></b> Online</span><time><i class="bi bi-calendar3"></i> 15 Sep 2026yuio</time></div>
                        <div class="landing-dashboard-stats">
                            <div><i class="bi bi-receipt"></i><small>Total Penjualan Hari Ini</small><strong>Rp 12.840.000</strong><em>â†— 18.4% dari kemarin</em></div>
                            <div><i class="bi bi-box-seam"></i><small>Stok Aktif</small><strong>1.248 Item</strong></div>
                        </div>
                        <div class="landing-chart"><div class="landing-chart-label"><span><i class="bi bi-graph-up-arrow"></i> Penjualan 7 Hari Terakhir</span><b>Rp 12.8 jt</b></div><div class="landing-bars"><i style="height:42%"></i><i style="height:58%"></i><i style="height:52%"></i><i style="height:72%"></i><i style="height:63%"></i><i style="height:78%"></i><i style="height:96%"></i></div><div class="landing-chart-days"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div></div>
                    </div>
                </div>
            </main>
            <section class="landing-feature-strip" id="fitur">
                <div><i class="bi bi-cart-check"></i><span><strong>Transaksi Cepat</strong><small>Proses penjualan lebih cepat dengan antarmuka sederhana.</small></span></div>
                <div><i class="bi bi-box-seam"></i><span><strong>Manajemen Stok</strong><small>Pantau stok secara real-time dan hindari kehabisan produk.</small></span></div>
                <div><i class="bi bi-bar-chart-line"></i><span><strong>Laporan Lengkap</strong><small>Dapatkan laporan penjualan, laba rugi, dan stok kapan saja.</small></span></div>
                <div><i class="bi bi-shield-check"></i><span><strong>Keamanan Data</strong><small>Data bisnis aman dengan sistem backup otomatis.</small></span></div>
            </section>
            <section class="landing-section landing-problem-section" data-reveal>
                <div class="landing-section-heading">
                    <p class="landing-kicker">MASIH MENGELOLA PENJUALAN SECARA MANUAL?</p>
                    <h2>Semakin banyak transaksi, semakin sulit mengontrol bisnis.</h2>
                    <p>Catatan penjualan, stok, dan laporan yang terpisah membuat waktu habis untuk pekerjaan administratif.</p>
                </div>
                <div class="landing-problem-grid">
                    <div><i class="bi bi-x-circle"></i><span>Catatan penjualan berantakan</span></div>
                    <div><i class="bi bi-x-circle"></i><span>Sulit mengetahui stok aktual</span></div>
                    <div><i class="bi bi-x-circle"></i><span>Rekap laporan membutuhkan waktu</span></div>
                    <div><i class="bi bi-x-circle"></i><span>Risiko salah hitung semakin besar</span></div>
                </div>
                <div class="landing-before-after">
                    <div><small>BEFORE</small><strong>Catatan manual</strong><strong>Stok tidak terpantau</strong><strong>Rekap lama</strong></div>
                    <i class="bi bi-arrow-right"></i>
                    <div><small>AFTER</small><strong>Transaksi digital</strong><strong>Stok realtime</strong><strong>Laporan otomatis</strong></div>
                </div>
            </section>
            <section class="landing-section landing-feature-section-new" data-reveal>
                <div class="landing-section-heading centered">
                    <p class="landing-kicker">FITUR UTAMA</p>
                    <h2>Semua yang Dibutuhkan<br>Bisnis Frozen Food Anda.</h2>
                    <p>BEKUKU membantu Anda mengelola operasional bisnis dari transaksi hingga laporan dalam satu sistem.</p>
                </div>
                <div class="landing-product-features">
                    <article><i class="bi bi-cart-check"></i><h3>Transaksi Lebih Cepat</h3><p>Proses transaksi dengan alur kasir yang sederhana.</p><ul><li>Keranjang belanja</li><li>Perhitungan otomatis</li><li>Cash, QRIS, dan transfer</li></ul></article>
                    <article><i class="bi bi-box-seam"></i><h3>Produk Terorganisir</h3><p>Kelola produk dengan informasi harga, kategori, stok, dan status.</p><ul><li>Data produk dan kategori</li><li>Harga jual dan modal</li><li>SKU dan barcode</li></ul></article>
                    <article><i class="bi bi-boxes"></i><h3>Stok Selalu Terpantau</h3><p>Ketahui produk yang mulai menipis sebelum kehabisan.</p><ul><li>Stok masuk dan keluar</li><li>Minimum stok</li><li>Notifikasi stok menipis</li></ul></article>
                    <article><i class="bi bi-bar-chart-line"></i><h3>Laporan Bisnis Jelas</h3><p>Lihat perkembangan penjualan berdasarkan periode.</p><ul><li>Laporan harian dan bulanan</li><li>Total transaksi dan penjualan</li><li>Produk terlaris</li></ul></article>
                    <article><i class="bi bi-receipt"></i><h3>Riwayat Tersimpan</h3><p>Setiap transaksi tersimpan dan mudah diperiksa kembali.</p><ul><li>ID dan customer</li><li>Produk dan pembayaran</li><li>Status transaksi</li></ul></article>
                    <article><i class="bi bi-layout-dashboard"></i><h3>Pantau Sekilas</h3><p>Gambaran kondisi bisnis tersedia dalam satu dashboard.</p><ul><li>Penjualan hari ini</li><li>Produk terlaris</li><li>Stok menipis</li></ul></article>
                </div>
            </section>
            <section class="landing-section landing-benefit-section" data-reveal>
                <div class="landing-preview-mini"><div class="landing-mini-chart"><i style="height:35%"></i><i style="height:55%"></i><i style="height:45%"></i><i style="height:75%"></i><i style="height:92%"></i></div><strong>Rp 12.840.000</strong><small>Ringkasan penjualan</small></div>
                <div class="landing-section-heading"><p class="landing-kicker">KENAPA BEKUKU?</p><h2>Lebih fokus menjual, bukan sibuk menghitung.</h2><div class="landing-benefit-list"><div><i class="bi bi-lightning-charge"></i><span><strong>Hemat Waktu</strong><small>Kurangi pekerjaan pencatatan dan rekap manual.</small></span></div><div><i class="bi bi-graph-up-arrow"></i><span><strong>Ambil Keputusan dengan Data</strong><small>Lihat kondisi penjualan dan stok dengan lebih jelas.</small></span></div><div><span><strong>Bisnis Lebih Teratur</strong><small>Semua aktivitas penting berada dalam satu sistem.</small></span></div></div></div>
            </section>
            <section class="landing-section landing-steps-section" data-reveal>
                <div class="landing-section-heading centered"><p class="landing-kicker">CARA KERJA</p><h2>Mulai Mengelola Bisnis dengan BEKUKU</h2></div>
                <div class="landing-steps"><div><b>01</b><i class="bi bi-box-seam"></i><h3>Masukkan Produk</h3><p>Tambahkan produk, harga, kategori, dan stok.</p></div><div><b>02</b><i class="bi bi-cart-check"></i><h3>Catat Transaksi</h3><p>Gunakan halaman kasir untuk memproses penjualan.</p></div><div><b>03</b><i class="bi bi-bar-chart-line"></i><h3>Pantau Bisnis</h3><p>Lihat penjualan, stok, dan laporan melalui dashboard.</p></div></div>
            </section>
            <section class="landing-section landing-preview-section" data-reveal>
                <div class="landing-section-heading centered"><p class="landing-kicker">DASHBOARD PREVIEW</p><h2>Semua Data Bisnis dalam Satu Tampilan.</h2><p>Informasi penting tersedia dalam satu dashboard yang ringkas dan mudah dipahami.</p></div>
                <div class="landing-large-preview"><div class="landing-preview-sidebar"><strong><i class="bi bi-snow2"></i> BEKUKU</strong><span class="active">Dashboard</span><span>Transaksi</span><span>Produk</span><span>Stok</span><span>Laporan</span></div><div class="landing-preview-content"><div><small>Penjualan hari ini</small><strong>Rp 12.840.000</strong></div><div><small>Transaksi</small><strong>86</strong></div><div><small>Stok aktif</small><strong>1.248</strong></div><div class="landing-preview-graph"><i style="height:35%"></i><i style="height:55%"></i><i style="height:46%"></i><i style="height:68%"></i><i style="height:58%"></i><i style="height:85%"></i><i style="height:72%"></i></div></div></div>
            </section>
            <section class="landing-final-cta" data-reveal><p class="landing-kicker">SIAP UNTUK BERKEMBANG?</p><h2>Siap Membuat Bisnis Frozen Food<br>Lebih Teratur?</h2><p>Mulai kelola transaksi, stok, dan laporan bisnis Anda dalam satu sistem dengan BEKUKU.</p><div><a class="bekuku-landing-cta" href="<?= bekuku_url('login.php') ?>">Mulai Sekarang <i class="bi bi-arrow-right"></i></a><a class="landing-demo-button" href="#fitur">Lihat Fitur</a></div></section>
            <section class="landing-bottom-statement" id="tentang">
                <p>KENAPA MEMILIH BEKUKU?</p>
                <h2>Solusi Lengkap untuk Bisnis <em>Frozen Food</em> Anda</h2>
            </section>
            <footer class="landing-footer"><div><a class="bekuku-auth-brand" href="<?= bekuku_url() ?>"><span class="bekuku-brand-mark"><i class="bi bi-snow2"></i></span><span>BEKUKU <small>POINT OF SALE</small></span></a><p>Solusi POS modern untuk membantu bisnis frozen food menjadi lebih teratur, cepat, dan siap berkembang.</p></div><div><strong>Menu</strong><a href="#fitur">Fitur</a><a href="#tentang">Tentang</a><a href="<?= bekuku_url('login.php') ?>">Login</a></div><small>Â© 2026 BEKUKU. All rights reserved.</small></footer>
        </div>
    </body>
    </html>
    <?php
    exit;
}
