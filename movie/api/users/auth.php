<?php
require_once __DIR__ . '/../../config.php';

session_start();

if (empty($_SESSION['userId'])) {
    jsonResponse(['isAuth' => false]);
}

$userId = $_SESSION['userId'];
$db     = getDB();

$stmt = $db->prepare(
    'SELECT id, name, lastname, email, image, role FROM users WHERE id = ? LIMIT 1'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();
$db->close();

if (!$user) {
    jsonResponse(['isAuth' => false]);
}

jsonResponse([
    'isAuth'   => true,
    'userId'   => $user['id'],
    'name'     => $user['name'],
    'lastname' => $user['lastname'],
    'email'    => $user['email'],
    'image'    => $user['image'],
    'role'     => $user['role'],
]);
