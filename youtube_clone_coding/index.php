<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

// ── 전역 예외 핸들러 (흰 화면 방지) ──────────────────────────────────────
set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    error_log('[YourTube] Uncaught: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    // DB 연결 실패 메시지는 별도 처리
    $isDatabaseError = $e instanceof RuntimeException
                    && str_contains($e->getMessage(), '데이터베이스');

    echo '<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8">'
       . '<title>오류 - YourTube</title>'
       . '<style>body{font-family:sans-serif;display:flex;justify-content:center;'
       . 'align-items:center;min-height:100vh;margin:0;background:#0f0f0f;color:#f1f1f1;}'
       . '.box{text-align:center;padding:40px;} h1{color:#ff0000;font-size:2rem;margin-bottom:12px;}'
       . 'p{color:#aaa;margin-bottom:20px;} a{color:#ff0000;}</style></head><body>'
       . '<div class="box">'
       . '<h1>500</h1>'
       . '<p>' . ($isDatabaseError ? '데이터베이스에 연결할 수 없습니다.<br>잠시 후 다시 시도해주세요.' : '서버 오류가 발생했습니다.') . '</p>'
       . '<a href="/">홈으로 돌아가기</a>'
       . '</div></body></html>';
    exit;
});

// 세션 쿠키 보안 강화
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,          // JS에서 쿠키 접근 차단
    'samesite' => 'Lax',         // CSRF 방어
]);
session_start();

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/config/csrf.php';
require_once BASE_PATH . '/config/helpers.php';

$route = trim($_GET['route'] ?? '', '/');

switch ($route) {
    case '':
    case 'home':
        require_once BASE_PATH . '/controllers/VideoController.php';
        (new VideoController())->home();
        break;

    case 'register':
        require_once BASE_PATH . '/controllers/AuthController.php';
        (new AuthController())->register();
        break;

    case 'login':
        require_once BASE_PATH . '/controllers/AuthController.php';
        (new AuthController())->login();
        break;

    case 'logout':
        require_once BASE_PATH . '/controllers/AuthController.php';
        (new AuthController())->logout();
        break;

    case 'upload':
        require_once BASE_PATH . '/controllers/VideoController.php';
        (new VideoController())->upload();
        break;

    case 'watch':
        require_once BASE_PATH . '/controllers/VideoController.php';
        (new VideoController())->watch();
        break;

    case 'profile':
        require_once BASE_PATH . '/controllers/UserController.php';
        (new UserController())->profile();
        break;

    case 'video_delete':
        require_once BASE_PATH . '/controllers/VideoController.php';
        (new VideoController())->delete();
        break;

    default:
        http_response_code(404);
        // 간단한 404 페이지
        echo '<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8">'
           . '<title>404 - YourTube</title>'
           . '<style>body{font-family:sans-serif;display:flex;justify-content:center;'
           . 'align-items:center;min-height:100vh;margin:0;background:#0f0f0f;color:#f1f1f1;}'
           . '.box{text-align:center;padding:40px;} h1{color:#ff0000;font-size:3rem;margin-bottom:12px;}'
           . 'p{color:#aaa;margin-bottom:20px;} a{color:#ff0000;}</style></head><body>'
           . '<div class="box"><h1>404</h1><p>페이지를 찾을 수 없습니다.</p>'
           . '<a href="/">홈으로 돌아가기</a></div></body></html>';
        break;
}
