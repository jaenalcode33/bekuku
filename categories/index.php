<?php

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/database.php";


$stmt = $conn->query("
    SELECT
        c.category_id,
        c.name,
        COUNT(p.product_id) AS total_products
    FROM categories c
    LEFT JOIN products p
        ON c.category_id = p.category_id
    GROUP BY c.category_id, c.name
    ORDER BY c.category_id DESC
");

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);


$totalCategoryProducts = 0;
$categoriesWithProducts = 0;


foreach ($categories as $category) {

    $productCount = (int) $category['total_products'];

    $totalCategoryProducts += $productCount;

    if ($productCount > 0) {
        $categoriesWithProducts++;
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Kategori - BEKUKU POS</title>


    <!-- AdminLTE -->

    "
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <!-- CSS UTAMA -->
    >


    <!-- CSS KHUSUS POPUP KATEGORI -->
    >

    <link rel="stylesheet" href="<?= bekuku_url('assets/css/categories.css') ?>?v=202609221642">
</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">


<div class="app-wrapper">


    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <main class="app-main">


        <div class="container-fluid dashboard-wrapper">


            <!-- =====================================================
                 HEADER
                 ===================================================== -->

            <section class="dashboard-hero">


                <div class="dashboard-hero-content">


                    <div>


                        <div class="dashboard-hero-title">

                            <i class="bi bi-tags me-2"></i>

                            Kategori

                        </div>


                        <p class="dashboard-hero-subtitle">

                            Kelola kategori produk BEKUKU dengan lebih rapi.

                        </p>


                    </div>


                </div>


            </section>



            <!-- =====================================================
                 STATISTIK
                 ===================================================== -->

            <div class="row">


                <div class="col-xl-4 col-md-6">


                    <div class="dashboard-stat stat-blue">


                        <div class="stat-icon">

                            <i class="bi bi-tags"></i>

                        </div>


                        <div class="stat-label">

                            Total Kategori

                        </div>


                        <div class="stat-value">

                            <?= number_format(count($categories)) ?>

                        </div>


                        <div class="stat-description">

                            Kategori terdaftar

                        </div>


                    </div>


                </div>



                <div class="col-xl-4 col-md-6">


                    <div class="dashboard-stat stat-green">


                        <div class="stat-icon">

                            <i class="bi bi-box-seam"></i>

                        </div>


                        <div class="stat-label">

                            Produk Berkategori

                        </div>


                        <div class="stat-value">

                            <?= number_format($totalCategoryProducts) ?>

                        </div>


                        <div class="stat-description">

                            Produk dalam kategori

                        </div>


                    </div>


                </div>



                <div class="col-xl-4 col-md-6">


                    <div class="dashboard-stat stat-yellow">


                        <div class="stat-icon">

                            <i class="bi bi-check-circle"></i>

                        </div>


                        <div class="stat-label">

                            Kategori Terisi

                        </div>


                        <div class="stat-value">

                            <?= number_format($categoriesWithProducts) ?>

                        </div>


                        <div class="stat-description">

                            Memiliki produk aktif

                        </div>


                    </div>


                </div>


            </div>



            <!-- =====================================================
                 DAFTAR KATEGORI
                 ===================================================== -->

            <section class="card products-list-card categories-list-card data-table-card">


                <div class="card-header d-flex align-items-center justify-content-between">


                    <h3 class="card-title">

                        <i class="bi bi-list-ul me-2"></i>

                        Daftar Kategori

                    </h3>


                    <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-light data-table-filter-toggle" aria-expanded="false">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <span class="products-count">

                        <?= number_format(count($categories)) ?>

                        kategori

                    </span>
                    <button type="button" id="btnTambahKategori" class="btn dashboard-product-add-button">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Kategori
                    </button>
                    </div>


                </div>



                <div class="card-body p-0">


                    <?php if (count($categories) > 0): ?>


                        <div class="table-responsive">


                            <table class="table table-hover align-middle mb-0">


                                <thead>


                                    <tr>


                                        <th width="80">

                                            No

                                        </th>


                                        <th>

                                            Nama Kategori

                                        </th>


                                        <th width="180">

                                            Jumlah Produk

                                        </th>


                                        <th width="180">

                                            Aksi

                                        </th>


                                    </tr>


                                </thead>


                                <tbody>


                                    <?php

                                    $no = 1;

                                    foreach ($categories as $category):

                                    ?>


                                        <tr>


                                            <td>

                                                <?= $no++; ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $category["name"]
                                                    ); ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <span class="category-product-count">

                                                    <?= number_format(
                                                        (int) $category["total_products"]
                                                    ); ?>

                                                    produk

                                                </span>

                                            </td>


                                            <td>


                                                <a
                                                    href="edit.php?id=<?= $category["category_id"]; ?>&popup=1"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="Edit kategori"
                                                    aria-label="Edit kategori"
                                                    data-popup-edit-category
                                                    data-popup-url="edit.php?id=<?= $category["category_id"]; ?>&popup=1"
                                                    data-modal-title="Edit Kategori"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>



                                                <form
                                                    method="post"
                                                    action="delete.php"
                                                    class="d-inline"
                                                    data-delete-confirm
                                                    data-delete-label="kategori"
                                                >


                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int) $category["category_id"]; ?>"
                                                    >


                                                    <?= bekuku_csrf_field() ?>


                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Hapus kategori"
                                                        aria-label="Hapus kategori"
                                                    >

                                                        <i class="bi bi-trash"></i>

                                                    </button>


                                                </form>


                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                </tbody>


                            </table>


                        </div>


                    <?php else: ?>


                        <div class="alert alert-info">

                            <i class="bi bi-info-circle"></i>

                            Belum ada kategori.

                        </div>


                    <?php endif; ?>


                </div>


            </section>


        </div>


    </main>


    <?php require_once __DIR__ . "/../includes/footer.php"; ?>


</div>



<!-- =========================================================
     JAVASCRIPT LAMA WEBSITE
     TETAP DIPAKAI
     ========================================================= -->






<!-- =========================================================
     JAVASCRIPT KHUSUS POPUP KATEGORI
     ========================================================= -->



    <script src="<?= bekuku_url('assets/js/categories.js') ?>?v=202609221642"></script>
</body>

</html>



