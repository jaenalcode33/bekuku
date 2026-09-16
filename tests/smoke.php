<?php

declare(strict_types=1);

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
require_once __DIR__ . '/../config/app.php';

$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $message);
    }
};

$token = bekuku_csrf_token();
$check(strlen($token) === 64, 'CSRF token is 32 random bytes');
$check(str_contains(bekuku_csrf_field(), 'name="csrf_token"'), 'CSRF field renders');
$check(bekuku_validate_string('  Produk  ', 'Nama') === 'Produk', 'string validation trims');
$check(bekuku_validate_int('5', 'Jumlah', 1) === 5, 'integer validation');
$check(bekuku_validate_money('12.345', 'Harga') === 12.35, 'money validation rounds');
$check(bekuku_validate_enum('kasir', 'Peran', ['admin', 'kasir', 'gudang']) === 'kasir', 'enum validation');
$check(bekuku_role_can_access('kasir', '/transactions/create.php'), 'role access helper');
$check(!bekuku_role_can_access('kasir', '/products/'), 'role denial helper');

echo "BEKUKU helper smoke test passed ($checks checks)\n";
