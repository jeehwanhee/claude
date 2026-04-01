<?php
require_once __DIR__ . '/../../config.php';

requireAuth();

$body    = getBody();
$movieId = $body['movieId'] ?? null;

if ($movieId === null || $movieId === '') {
    jsonResponse(['success' => false, 'message' => 'movieId가 필요합니다.'], 400);
}

// userId는 반드시 세션에서만 가져옴
$userId = $_SESSION['userId'];

$db = getDB();

$stmt = $db->prepare('DELETE FROM favorites WHERE user_id = ? AND movie_id = ?');
$stmt->bind_param('is', $userId, $movieId);

if (!$stmt->execute()) {
    $stmt->close();
    $db->close();
    jsonResponse(['success' => false, 'message' => '즐겨찾기 제거 중 오류가 발생했습니다.'], 500);
}

$stmt->close();
$db->close();

jsonResponse(['success' => true]);
