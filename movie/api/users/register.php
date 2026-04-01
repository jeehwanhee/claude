<?php
require_once __DIR__ . '/../../config.php';

$body = getBody();

$name     = trim($body['name']     ?? '');
$lastname = trim($body['lastname'] ?? '');
$email    = trim($body['email']    ?? '');
$password = $body['password']      ?? '';
$image    = trim($body['image']    ?? '');

// 필수 필드 확인
if ($name === '' || $lastname === '' || $email === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => '필수 항목을 모두 입력해 주세요.'], 400);
}

// 이메일 형식 확인
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => '유효하지 않은 이메일 형식입니다.'], 400);
}

// 비밀번호 최소 길이
if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'message' => '비밀번호는 최소 6자 이상이어야 합니다.'], 400);
}

$db = getDB();

// 이메일 중복 확인
$stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $db->close();
    jsonResponse(['success' => false, 'message' => '이미 사용 중인 이메일입니다.'], 409);
}
$stmt->close();

// 비밀번호 해싱
$hashed = password_hash($password, PASSWORD_BCRYPT);

// 사용자 삽입
$stmt = $db->prepare(
    'INSERT INTO users (name, lastname, email, password, image, role) VALUES (?, ?, ?, ?, ?, 0)'
);
$stmt->bind_param('sssss', $name, $lastname, $email, $hashed, $image);

if (!$stmt->execute()) {
    $stmt->close();
    $db->close();
    jsonResponse(['success' => false, 'message' => '회원가입 처리 중 오류가 발생했습니다.'], 500);
}

$stmt->close();
$db->close();

jsonResponse(['success' => true]);
