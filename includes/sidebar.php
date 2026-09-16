<?php
require_once __DIR__ . "/../config/app.php";

$currentRequestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$currentRequestPath = preg_replace('#^' . preg_quote(bekuku_base_path(), '#') . '#', '', $currentRequestPath);
$currentRequestPath = '/' . ltrim($currentRequestPath ?: '/', '/');

function bekuku_nav_active(string $path): bool
{
    global $currentRequestPath;

    $expected = '/' . ltrim($path, '/');
    $expected = $expected === '' ? '/' : $expected;

    if ($expected === '/') {
        return $currentRequestPath === '/';
    }

    if ($path === 'transactions/create.php') {
        return $currentRequestPath === '/transactions/create.php';
    }

    if ($path === 'products/movements.php') {
        return $currentRequestPath === '/products/movements.php';
    }

    if ($path === 'products/') {
        return $currentRequestPath === '/products/'
            || $currentRequestPath === '/products'
            || $currentRequestPath === '/products/index.php'
            || (
                str_starts_with($currentRequestPath, '/products/')
                && !str_starts_with($currentRequestPath, '/products/movements.php')
                && !str_starts_with($currentRequestPath, '/products/stock.php')
            );
    }

    if ($path === 'purchases/batches.php') {
        return $currentRequestPath === '/purchases/batches.php';
    }

    if ($path === 'purchases/') {
        return $currentRequestPath === '/purchases/'
            || $currentRequestPath === '/purchases'
            || $currentRequestPath === '/purchases/index.php'
            || (
                str_starts_with($currentRequestPath, '/purchases/')
                && !str_starts_with($currentRequestPath, '/purchases/batches.php')
            );
    }

    if ($path === 'transactions/') {
        return $currentRequestPath === '/transactions/'
            || $currentRequestPath === '/transactions'
            || $currentRequestPath === '/transactions/index.php'
            || str_starts_with($currentRequestPath, '/transactions/detail.php');
    }

    return $currentRequestPath === $expected
        || str_starts_with($currentRequestPath, $expected);
}
?>

<!-- Sidebar -->

<aside
    class="app-sidebar bg-body-secondary shadow role-<?= htmlspecialchars(bekuku_user()['role'] ?? 'guest', ENT_QUOTES, 'UTF-8') ?>"
    data-bs-theme="dark"
>


    <!-- Brand -->

    <div class="sidebar-brand">

        <a
            href="<?= bekuku_url() ?>"
            class="brand-link"
        >

            <span class="brand-text fw-light">

                BEKUKU

            </span>

        </a>

    </div>


    <!-- Sidebar Wrapper -->

    <div class="sidebar-wrapper">

        <nav class="mt-2">

            <ul
                class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                role="menu"
                data-accordion="false"
            >


                <!-- DASHBOARD -->

                <li class="nav-item">

                    <a
                        href="<?= bekuku_url() ?>"
                        class="nav-link <?= bekuku_nav_active('') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-speedometer2"></i>

                        <p>
                            Dashboard
                        </p>

                    </a>

                </li>


                <!-- TRANSAKSI -->

                <li class="nav-header">
                    TRANSAKSI
                </li>


                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('transactions/create.php') ?>"
                        class="nav-link <?= bekuku_nav_active('transactions/create.php') ? 'active' : '' ?>"
                        data-no-modal
                    >

                        <i class="nav-icon bi bi-cart-plus"></i>

                        <p>
                            Transaksi Baru
                        </p>

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('transactions/') ?>"
                        class="nav-link <?= bekuku_nav_active('transactions/') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-receipt"></i>

                        <p>
                            Riwayat Transaksi
                        </p>

                    </a>

                </li>


                <!-- MASTER DATA -->

                <li class="nav-header">
                    MASTER DATA
                </li>


                <!-- KATEGORI -->

                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('categories/') ?>"
                        class="nav-link <?= bekuku_nav_active('categories/') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-tags"></i>

                        <p>
                            Kategori
                        </p>

                    </a>

                </li>


                <!-- SUPPLIER -->

                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('suppliers/') ?>"
                        class="nav-link <?= bekuku_nav_active('suppliers/') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-truck"></i>

                        <p>
                            Supplier
                        </p>

                    </a>

                </li>


                <!-- CUSTOMER -->

                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('customers/') ?>"
                        class="nav-link <?= bekuku_nav_active('customers/') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-people"></i>

                        <p>
                            Customer
                        </p>

                    </a>

                </li>


                <!-- PRODUK -->

                <li class="nav-header">
                    PRODUK
                </li>


                <!-- DATA PRODUK -->

                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('products/') ?>"
                        class="nav-link <?= bekuku_nav_active('products/') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-box-seam"></i>

                        <p>
                            Data Produk
                        </p>

                    </a>

                </li>


                <!-- RIWAYAT STOK -->

                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('products/movements.php') ?>"
                        class="nav-link <?= bekuku_nav_active('products/movements.php') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-arrow-left-right"></i>

                        <p>
                            Riwayat Stok
                        </p>

                    </a>

                </li>


                <!-- PERSEDIAAN -->

                <li class="nav-header">
                    PERSEDIAAN
                </li>


                <!-- PEMBELIAN -->

                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('purchases/') ?>"
                        class="nav-link <?= bekuku_nav_active('purchases/') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-cart-check"></i>

                        <p>
                            Pembelian
                        </p>

                    </a>

                </li>


                <!-- BATCH -->

                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('purchases/batches.php') ?>"
                        class="nav-link <?= bekuku_nav_active('purchases/batches.php') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-calendar-x"></i>

                        <p>
                            Batch & Expired
                        </p>

                    </a>

                </li>


                <!-- LAPORAN -->

                <li class="nav-header">
                    LAPORAN
                </li>


                <!-- LAPORAN PENJUALAN -->

                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('reports/sales.php') ?>"
                        class="nav-link <?= bekuku_nav_active('reports/sales.php') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-bar-chart"></i>

                        <p>
                            Laporan Penjualan
                        </p>

                    </a>

                </li>


                <!-- LAPORAN STOK -->

                <li class="nav-item">

                    <a
                        href="<?= bekuku_url('reports/stock.php') ?>"
                        class="nav-link <?= bekuku_nav_active('reports/stock.php') ? 'active' : '' ?>"
                    >

                        <i class="nav-icon bi bi-boxes"></i>

                        <p>
                            Laporan Stok
                        </p>

                    </a>

                </li>

                <?php if (bekuku_can('users.manage')): ?>
                <li class="nav-header">ADMINISTRASI</li>
                <li class="nav-item">
                    <a href="<?= bekuku_url('users/') ?>" class="nav-link <?= bekuku_nav_active('users/') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-person-gear"></i>
                        <p>Pengguna</p>
                    </a>
                </li>
                <?php endif; ?>

            </ul>

        </nav>

    </div>

</aside>