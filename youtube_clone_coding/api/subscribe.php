<?php

require_once __DIR__ . '/_bootstrap.php';
require_once BASE_PATH . '/models/Subscription.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('POST 요청만 허용됩니다.', 405);
}

require_login();
csrf_check();

$channelId = filter_var($_POST['channel_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$channelId) {
    json_error('유효하지 않은 channel_id');
}

$subscriberId = (int) $_SESSION['user_id'];

if ($subscriberId === $channelId) {
    json_error('본인 채널은 구독할 수 없습니다.');
}

$subModel     = new Subscription(getDB());
$isSubscribed = $subModel->toggle($subscriberId, $channelId);
$count        = $subModel->getCount($channelId);

json_ok(['subscribed' => $isSubscribed, 'count' => $count]);
