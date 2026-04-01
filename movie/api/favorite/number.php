<?php
require_once __DIR__ . '/../../config.php';

$body    = getBody();
$movieId = $body['movieId'] ?? null;

if ($movieId === null || $movieId === '') {
    jsonResponse(['success' => false, 'message' => 'movieId가 필요합니다.'], 400);
}

$db = getDB();

$stmt = $db->prepare('SELECT COUNT(*) AS cnt FROM favorites WHERE movie_id = ?');
$stmt->bind_param('s', $movieId);
$stmt->execute();
$result = $stmt->get_result();
$row    = $result->fetch_assoc();
$stmt->close();
$db->close();

jsonResponse(['success' => true, 'favoriteNumber' => (int)$row['cnt']]);
