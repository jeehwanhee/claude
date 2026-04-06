<?php

require_once __DIR__ . '/_bootstrap.php';
require_once BASE_PATH . '/models/Video.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('POST 요청만 허용됩니다.', 405);
}

$body    = json_decode(file_get_contents('php://input'), true);
$videoId = filter_var($body['video_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$videoId) {
    json_error('유효하지 않은 video_id');
}

// ── 세션 중복 조회수 방지 ─────────────────────────────────────────────
// 같은 세션에서 동일 영상을 이미 조회한 경우 DB 업데이트 없이 현재 값만 반환
$sessionKey = 'viewed_' . $videoId;
$videoModel = new Video(getDB());

if (!empty($_SESSION[$sessionKey])) {
    // 현재 조회수만 조회해서 반환 (증가 없음)
    $video = $videoModel->getById($videoId);
    json_ok(['views' => (int)($video['views'] ?? 0)]);
}

$_SESSION[$sessionKey] = true;
$views = $videoModel->incrementViews($videoId);

json_ok(['views' => $views]);
