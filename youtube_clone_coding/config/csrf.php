<?php

/**
 * CSRF 토큰 생성 및 검증 헬퍼
 */

function csrf_generate(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $token = csrf_generate();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';

    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('403 Forbidden: CSRF 토큰이 유효하지 않습니다.');
    }

    // 1회용 토큰 재발급
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * AJAX 전용 검증 (토큰 재발급 없음)
 * POST body의 csrf_token 또는 X-CSRF-Token 헤더를 수락
 */
function csrf_check(): void {
    $token = $_POST['csrf_token']
          ?? $_SERVER['HTTP_X_CSRF_TOKEN']
          ?? '';

    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'CSRF 토큰이 유효하지 않습니다.']);
        exit;
    }
}
