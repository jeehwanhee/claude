<?php
require_once __DIR__ . '/../../config.php';

requireAuth();

$body         = getBody();
$movieId      = $body['movieId']      ?? null;
$movieTitle   = $body['movieTitle']   ?? '';
$moviePoster  = $body['moviePoster']  ?? '';
$movieRuntime = $body['movieRuntime'] ?? '';

if ($movieId === null || $movieId === '') {
    jsonResponse(['success' => false, 'message' => 'movieId가 필요합니다.'], 400);
}

// userId는 반드시 세션에서만 가져옴
$userId = $_SESSION['userId'];

$db = getDB();

$stmt = $db->prepare(
    'INSERT IGNORE INTO favorites (user_id, movie_id, movie_title, movie_poster, movie_runtime)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->bind_param('issss', $userId, $movieId, $movieTitle, $moviePoster, $movieRuntime);

if (!$stmt->execute()) {
    $stmt->close();
    $db->close();
    jsonResponse(['success' => false, 'message' => '즐겨찾기 추가 중 오류가 발생했습니다.'], 500);
}

$stmt->close();
$db->close();

jsonResponse(['success' => true]);
