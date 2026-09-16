<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

bekuku_audit('logout', 'auth', isset(bekuku_user()['id']) ? (int) bekuku_user()['id'] : null);

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();
header('Location: ' . bekuku_url(''));
exit;
