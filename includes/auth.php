<?php
function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('hrls_admin');
        session_start();
    }
}

function is_logged_in(): bool {
    start_session();
    return !empty($_SESSION['admin_logged_in']);
}

function require_auth(): void {
    if (!is_logged_in()) {
        header('Location: ' . admin_url(''));
        exit;
    }
}

function attempt_login(string $password): bool {
    require_once __DIR__ . '/db.php';
    $db   = get_db();
    $hash = $db->query("SELECT value FROM settings WHERE `key` = 'admin_password'")->fetchColumn();
    if ($hash && password_verify($password, $hash)) {
        start_session();
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['csrf']            = bin2hex(random_bytes(32));
        return true;
    }
    return false;
}

function do_logout(): void {
    start_session();
    session_destroy();
}

function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(string $token): bool {
    start_session();
    return hash_equals($_SESSION['csrf'] ?? '', $token);
}

function admin_url(string $path = ''): string {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/admin/'), '/\\');
    // Ensure we always point to /admin/
    if (!str_contains($base, 'admin')) {
        $base .= '/admin';
    }
    return $base . ($path !== '' ? '/' . ltrim($path, '/') : '/');
}
