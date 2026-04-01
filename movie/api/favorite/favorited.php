<?php
require_once __DIR__ . '/../../config.php';

$body    = getBody();
$movieId = $body['movieId'] ?? null;
$userId  = $body['userId']  ?? null;

if ($movieId === null || $userId === null) {
    jsonResponse(['success' => false, 'message' => 'movieId와 userId가 필요합니다.'], 400);
}

$db = getDB();

$stmt = $db->prepare(
    'SELECT 1 FROM favorites WHERE user_id = ? AND movie_id = ? LIMIT 1'
);
$stmt->bind_param('is', $userId, $movieId);
$stmt->execute();
$stmt->store_result();
$favorited = $stmt->num_rows > 0;
$stmt->close();
$db->close();

jsonResponse(['success' => true, 'favorited' => $favorited]);
