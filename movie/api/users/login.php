<?php
require_once __DIR__ . '/../../config.php';

$body     = getBody();
$email    = trim($body['email']    ?? '');
$password = $body['password']      ?? '';

if ($email === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => '이메일과 비밀번호를 입력해 주세요.'], 400);
}

$db = getDB();

$stmt = $db->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();
$db->close();

// 사용자 없거나 비밀번호 불일치 — 메시지는 구분하지 않음
if (!$user || !password_verify($password, $user['password'])) {
    jsonResponse(['success' => false, 'message' => '이메일 또는 비밀번호가 올바르지 않습니다.'], 401);
}

session_start();
$_SESSION['userId'] = $user['id'];
$_SESSION['name']   = $user['name'];
$_SESSION['email']  = $user['email'];
$_SESSION['role']   = $user['role'];

jsonResponse([
    'success' => true,
    'userId'  => $user['id'],
    'name'    => $user['name'],
]);
