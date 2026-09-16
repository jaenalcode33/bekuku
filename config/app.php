<?php

const BEKUKU_SESSION_TIMEOUT = 1800;

function bekuku_log(string $message, array $context = []): void
{
    $directory = __DIR__ . '/../storage/logs';

    if (!is_dir($directory)) {
        @mkdir($directory, 0750, true);
    }

    $suffix = $context === [] ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    error_log('[' . date('c') . '] ' . $message . $suffix . PHP_EOL, 3, $directory . '/app.log');
}

function bekuku_error_handler(int $severity, string $message, string $file, int $line): bool
{
    if (!(error_reporting() & $severity)) {
        return false;
    }

    bekuku_log('PHP error: ' . $message, ['file' => $file, 'line' => $line, 'severity' => $severity]);
    return true;
}

if (!function_exists('bekuku_session_start')) {
    function bekuku_session_start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '1' : '0');
        session_start();
    }
}

set_error_handler('bekuku_error_handler');
bekuku_session_start();

if (isset($_SESSION['bekuku_last_activity'])
    && time() - (int) $_SESSION['bekuku_last_activity'] > BEKUKU_SESSION_TIMEOUT
) {
    $expiredUser = $_SESSION['bekuku_user']['username'] ?? null;
    $_SESSION = [];
    session_regenerate_id(true);
    if ($expiredUser !== null) {
        bekuku_log('Session expired', ['username' => $expiredUser]);
    }
}
$_SESSION['bekuku_last_activity'] = time();

function bekuku_csrf_token(): string
{
    if (empty($_SESSION['bekuku_csrf'])) {
        $_SESSION['bekuku_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['bekuku_csrf'];
}

function bekuku_csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(bekuku_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function bekuku_verify_csrf(): void
{
    $provided = (string) ($_POST['csrf_token'] ?? '');
    $expected = (string) ($_SESSION['bekuku_csrf'] ?? '');

    if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
        http_response_code(419);
        exit('Permintaan tidak valid atau sesi formulir telah kedaluwarsa.');
    }
}

function bekuku_validate_string(mixed $value, string $label, int $maxLength = 255, bool $required = true): string
{
    $value = trim((string) $value);
    if ($required && $value === '') {
        throw new InvalidArgumentException($label . ' wajib diisi.');
    }
    if (mb_strlen($value) > $maxLength) {
        throw new InvalidArgumentException($label . ' terlalu panjang.');
    }
    return $value;
}

function bekuku_validate_int(mixed $value, string $label, int $min = 0, ?int $max = null): int
{
    $result = filter_var($value, FILTER_VALIDATE_INT);
    if ($result === false || $result < $min || ($max !== null && $result > $max)) {
        throw new InvalidArgumentException($label . ' tidak valid.');
    }
    return (int) $result;
}

function bekuku_validate_money(mixed $value, string $label, float $min = 0): float
{
    if (!is_numeric($value) || (float) $value < $min || !is_finite((float) $value)) {
        throw new InvalidArgumentException($label . ' tidak valid.');
    }
    return round((float) $value, 2);
}

function bekuku_validate_enum(mixed $value, string $label, array $allowed): string
{
    $value = (string) $value;
    if (!in_array($value, $allowed, true)) {
        throw new InvalidArgumentException($label . ' tidak valid.');
    }
    return $value;
}

function bekuku_audit(string $action, string $entity, ?int $entityId = null, array $details = []): void
{
    global $conn;
    if (!isset($conn) || !($conn instanceof PDO)) {
        return;
    }

    try {
        $user = bekuku_user();
        $stmt = $conn->prepare(
            'INSERT INTO audit_logs (user_id, username, action, entity, entity_id, details, ip_address)
             VALUES (:user_id, :username, :action, :entity, :entity_id, :details, :ip)'
        );
        $stmt->execute([
            ':user_id' => isset($user['id']) ? (int) $user['id'] : null,
            ':username' => $user['username'] ?? null,
            ':action' => $action,
            ':entity' => $entity,
            ':entity_id' => $entityId,
            ':details' => $details === [] ? null : json_encode($details, JSON_UNESCAPED_UNICODE),
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        bekuku_log('Audit log failed', ['error' => $e->getMessage(), 'action' => $action, 'entity' => $entity]);
    }
}

function bekuku_can(string $permission): bool
{
    $role = (string) (bekuku_user()['role'] ?? '');
    $permissions = [
        'users.manage' => ['admin'],
        'master.write' => ['admin', 'gudang'],
        'sales.write' => ['admin', 'kasir'],
        'purchases.write' => ['admin', 'gudang'],
    ];
    return in_array($role, $permissions[$permission] ?? [], true);
}

function bekuku_require_permission(string $permission): void
{
    if (!bekuku_can($permission)) {
        http_response_code(403);
        exit('Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}

function bekuku_is_authenticated(): bool
{
    return !empty($_SESSION['bekuku_user']);
}

function bekuku_user(): array
{
    return $_SESSION['bekuku_user'] ?? [];
}

function bekuku_role_label(string $role): string
{
    return [
        'admin' => 'Administrator',
        'kasir' => 'Kasir',
        'gudang' => 'Gudang',
    ][$role] ?? ucfirst($role);
}

function bekuku_base_path(): string
{
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $segments = array_values(array_filter(explode('/', $requestPath), 'strlen'));

    if ($segments !== []) {
        $candidate = '/' . $segments[0];

        $documentRoot = (string) ($_SERVER['DOCUMENT_ROOT'] ?? '');
        $projectName = basename(dirname(__DIR__));
        if (($documentRoot !== '' && is_dir($documentRoot . $candidate . '/assets/css'))
            || (strcasecmp(ltrim($candidate, '/'), $projectName) === 0
                && is_dir(__DIR__ . '/../assets/css'))
        ) {
            return $candidate;
        }
    }

    return '';
}

function bekuku_url(string $path = ''): string
{
    $base = bekuku_base_path();

    if ($path === '') {
        return $base === '' ? '/' : $base;
    }

    $normalized = '/' . ltrim($path, '/');

    return $base === '' ? $normalized : $base . $normalized;
}

function bekuku_request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = bekuku_base_path();

    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base)) ?: '/';
    }

    return '/' . ltrim($path, '/');
}

function bekuku_role_can_access(string $role, string $path): bool
{
    if ($role === 'admin' || $path === '/') {
        return true;
    }

    $allowedPrefixes = [
        'kasir' => ['/transactions', '/customers', '/reports/sales.php'],
        'gudang' => ['/products', '/purchases', '/categories', '/suppliers', '/reports/stock.php'],
    ];

    foreach ($allowedPrefixes[$role] ?? [] as $prefix) {
        if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
            return true;
        }
    }

    return false;
}

$publicScripts = ['/', '/index.php', '/login.php', '/logout.php'];
$requestPath = bekuku_request_path();

if (!in_array($requestPath, $publicScripts, true) && !bekuku_is_authenticated()) {
    header('Location: ' . bekuku_url('login.php'));
    exit;
}

if (!in_array($requestPath, $publicScripts, true) && bekuku_is_authenticated()) {
    $role = (string) (bekuku_user()['role'] ?? '');

    if (!bekuku_role_can_access($role, $requestPath)) {
        header('Location: ' . bekuku_url('') . '?akses=ditolak');
        exit;
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    bekuku_verify_csrf();
}
