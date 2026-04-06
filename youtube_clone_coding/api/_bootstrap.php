<?php

define('BASE_PATH', dirname(__DIR__));

session_start();

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/config/csrf.php';
require_once BASE_PATH . '/config/helpers.php';

header('Content-Type: application/json; charset=utf-8');

function json_ok(array $data = []): never {
    echo json_encode($data);
    exit;
}

function json_error(string $message, int $status = 400): never {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        json_error('로그인이 필요합니다.', 401);
    }
}
