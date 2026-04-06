<?php

require_once __DIR__ . '/_bootstrap.php';
require_once BASE_PATH . '/models/Like.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('POST 요청만 허용됩니다.', 405);
}

require_login();
csrf_check();

$videoId = filter_var($_POST['video_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$videoId) {
    json_error('유효하지 않은 video_id');
}

$likeModel = new Like(getDB());
$isLiked   = $likeModel->toggle((int) $_SESSION['user_id'], $videoId);
$count     = $likeModel->getCount($videoId);

json_ok(['liked' => $isLiked, 'count' => $count]);
