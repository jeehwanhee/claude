<?php

require_once __DIR__ . '/_bootstrap.php';
require_once BASE_PATH . '/models/Comment.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('POST 요청만 허용됩니다.', 405);
}

require_login();
csrf_check();

$videoId  = filter_var($_POST['video_id'] ?? 0, FILTER_VALIDATE_INT);
$content  = trim($_POST['content'] ?? '');
$parentId = filter_var($_POST['parent_id'] ?? null, FILTER_VALIDATE_INT);

if (!$videoId) {
    json_error('유효하지 않은 video_id');
}
if ($content === '') {
    json_error('댓글 내용을 입력해주세요.');
}
if (mb_strlen($content) > 1000) {
    json_error('댓글은 1000자 이내로 입력해주세요.');
}

$commentModel = new Comment(getDB());

$newId = $commentModel->create(
    $videoId,
    (int) $_SESSION['user_id'],
    $content,
    $parentId ?: null
);

$comment = $commentModel->getById($newId);

json_ok([
    'comment' => [
        'id'         => $comment['id'],
        'parent_id'  => $comment['parent_id'],
        'username'   => $comment['username'],
        'avatar'     => gravatar_url($comment['email'], 40),
        'content'    => $comment['content'],
        'created_at' => '방금 전',
    ],
]);
