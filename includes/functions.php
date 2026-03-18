<?php
function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function settings(): array {
    static $cache = null;
    if ($cache === null) {
        require_once __DIR__ . '/db.php';
        $rows  = get_db()->query("SELECT key, value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        $cache = $rows ?: [];
    }
    return $cache;
}

function setting(string $key, string $default = ''): string {
    return settings()[$key] ?? $default;
}

function json_response(mixed $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
