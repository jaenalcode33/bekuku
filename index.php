<?php

require_once __DIR__ . '/config/app.php';

if (bekuku_is_authenticated()) {
    require __DIR__ . '/dashboard/index.php';
    exit;
}

require __DIR__ . '/landing/index.php';
