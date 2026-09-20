<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

$statsStmt = $conn->query("
    SELECT
        COUNT(*) AS total_products,
        COALESCE(SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END), 0) AS active_products,
        COALESCE(SUM(CASE WHEN status = 'aktif' THEN stock ELSE 0 END), 0) AS total_stock,
        COALESCE(SUM(CASE WHEN status = 'aktif' AND stock <= min_stock THEN 1 ELSE 0 END), 0) AS low_stock_products
    FROM products
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
$productsPerPage = 10;
$totalProductCount = (int) ($stats['total_products'] ?? 0);
$totalPages = max(1, (int) ceil($totalProductCount / $productsPerPage));
$currentPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
$currentPage = $currentPage !== false ? max(1, min($currentPage, $totalPages)) : 1;
$offset = ($currentPage - 1) * $productsPerPage;
$stmt = $conn->prepare("
    SELECT
        p.product_id,
        p.sku,
        p.product_name,
        p.stock,
        p.min_stock,
        p.unit,
        p.selling_price,
        p.status,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.category_id = p.category_id
    ORDER BY p.product_name ASC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $productsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$activeProducts = (int) ($stats['active_products'] ?? 0);
$lowStockProducts = (int) ($stats['low_stock_products'] ?? 0);
$totalStock = (int) ($stats['total_stock'] ?? 0);

function product_rupiah(float $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk - BEKUKU POS</title>
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/adminlte.min.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/style.css') ?>?v=202609200917">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="app-main">
        <div class="container-fluid dashboard-wrapper">
            <section class="dashboard-hero">
                <div class="dashboard-hero-content">
                    <div>
                        <div class="dashboard-hero-title">
                            <i class="bi bi-box-seam me-2"></i>Data Produk
                        </div>
                        <p class="dashboard-hero-subtitle">
                            Kelola katalog produk, harga, dan ketersediaan stok BEKUKU.
                        </p>
                    </div>
                </div>
            </section>

            <div class="row">
                <div class="col-xl-4 col-md-6">
                    <div class="dashboard-stat stat-blue">
                        <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                        <div class="stat-label">Produk Aktif</div>
                        <div class="stat-value"><?= number_format($activeProducts) ?></div>
                        <div class="stat-description">Produk siap dijual</div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="dashboard-stat stat-green">
                        <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                        <div class="stat-label">Total Stok</div>
                        <div class="stat-value"><?= number_format($totalStock) ?></div>
                        <div class="stat-description">Unit dari produk aktif</div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="dashboard-stat stat-yellow">
                        <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                        <div class="stat-label">Stok Menipis</div>
                        <div class="stat-value"><?= number_format($lowStockProducts) ?></div>
                        <div class="stat-description">Perlu segera diperiksa</div>
                    </div>
                </div>
            </div>

            <section class="card products-list-card data-table-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title"><i class="bi bi-list-ul me-2"></i>Daftar Produk</h3>
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn btn-outline-light data-table-filter-toggle" aria-expanded="false">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                        <span class="products-count"><?= number_format($totalProductCount) ?> produk</span>
                        <a href="<?= bekuku_url('products/create.php') ?>" class="btn dashboard-product-add-button">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Produk
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if ($products !== []): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Kategori</th>
                                        <th>SKU</th>
                                        <th>Stok</th>
                                        <th>Harga Jual</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    $stock = (int) $product['stock'];
                                    $isLowStock = $stock <= (int) $product['min_stock'];
                                    $isActive = $product['status'] === 'aktif';
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small class="d-block text-muted"><?= htmlspecialchars($product['unit'], ENT_QUOTES, 'UTF-8') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($product['category_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($product['sku'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <span class="<?= $isLowStock ? 'product-stock-low' : 'product-stock' ?>">
                                                <?= number_format($stock) ?>
                                            </span>
                                        </td>
                                        <td><strong><?= product_rupiah((float) $product['selling_price']) ?></strong></td>
                                        <td>
                                            <span class="product-status <?= $isActive ? 'is-active' : 'is-inactive' ?>">
                                                <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= bekuku_url('products/edit.php?id=' . (int) $product['product_id']) ?>" class="btn btn-sm btn-outline-info" title="Edit produk">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="post" action="<?= bekuku_url('products/delete.php') ?>" class="d-inline" data-delete-confirm data-delete-label="produk">
                                                <input type="hidden" name="id" value="<?= (int) $product['product_id'] ?>">
                                                <?= bekuku_csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus produk">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="products-empty">
                            <i class="bi bi-box-seam"></i>
                            <h4>Belum ada produk</h4>
                            <p>Tambahkan produk pertama untuk mulai mengelola persediaan.</p>
                            <a href="<?= bekuku_url('products/create.php') ?>" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Produk
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            Menampilkan <?= (($currentPage - 1) * $productsPerPage) + 1 ?>-<?= min($currentPage * $productsPerPage, $totalProductCount) ?> dari <?= number_format($totalProductCount) ?> produk
                        </small>
                        <nav aria-label="Navigasi halaman produk">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= max(1, $currentPage - 1) ?>" aria-label="Sebelumnya">&laquo;</a>
                                </li>
                                <?php
                                $pageStart = (int) (floor(($currentPage - 1) / 10) * 10) + 1;
                                $pageEnd = min($totalPages, $pageStart + 9);
                                for ($page = $pageStart; $page <= $pageEnd; $page++):
                                ?>
                                    <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page ?>"><?= $page ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= min($totalPages, $currentPage + 1) ?>" aria-label="Berikutnya">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</div>
<script src="<?= bekuku_url('assets/js/ui.js') ?>?v=2026091619"></script>
<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>
<script src="<?= bekuku_url('assets/js/data-table-filter.js') ?>?v=202609200748"></script>
</body>
</html>
