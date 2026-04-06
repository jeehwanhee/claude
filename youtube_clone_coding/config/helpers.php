<?php

/**
 * 날짜를 "n분 전 / n시간 전 / n일 전" 형식으로 반환
 */
function time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);

    if ($diff < 60) {
        return '방금 전';
    }
    if ($diff < 3600) {
        return (int)($diff / 60) . '분 전';
    }
    if ($diff < 86400) {
        return (int)($diff / 3600) . '시간 전';
    }
    if ($diff < 2592000) {
        return (int)($diff / 86400) . '일 전';
    }
    if ($diff < 31536000) {
        return (int)($diff / 2592000) . '개월 전';
    }
    return (int)($diff / 31536000) . '년 전';
}

/**
 * Gravatar URL 반환
 */
function gravatar_url(string $email, int $size = 40): string {
    $hash = md5(strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
}
