<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'YourTube', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_generate(), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>

<header class="site-header">

    <!-- 로고 -->
    <a href="/" class="header-logo">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--primary)" aria-hidden="true">
            <path d="M21.58 7.19a2.75 2.75 0 0 0-1.93-1.95C18.0 5 12 5 12 5s-6 0-7.65.24a2.75 2.75 0 0 0-1.93 1.95A28.5 28.5 0 0 0 2 12a28.5 28.5 0 0 0 .42 4.81 2.75 2.75 0 0 0 1.93 1.95C6.0 19 12 19 12 19s6 0 7.65-.24a2.75 2.75 0 0 0 1.93-1.95A28.5 28.5 0 0 0 22 12a28.5 28.5 0 0 0-.42-4.81zM9.75 15V9l5.25 3-5.25 3z"/>
        </svg>
        Your<span>Tube</span>
    </a>

    <!-- 검색 -->
    <div class="header-search">
        <input type="text" placeholder="검색" aria-label="검색">
        <button type="button" aria-label="검색 실행">&#128269;</button>
    </div>

    <!-- 네비게이션 -->
    <nav class="header-nav">
        <?php if (!empty($_SESSION['user_id'])): ?>
            <?php
                $gravatarHash = md5(strtolower(trim($_SESSION['email'])));
                $gravatarUrl  = 'https://www.gravatar.com/avatar/' . $gravatarHash . '?s=40&d=identicon';
            ?>

            <a href="/upload" class="btn btn-red btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M14 13v4H10v-4H7l5-5 5 5h-3zM5 20h14v2H5v-2z"/>
                </svg>
                업로드
            </a>

            <div class="header-user">
                <img
                    src="<?= htmlspecialchars($gravatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?> 프로필"
                    class="header-avatar"
                    width="40"
                    height="40"
                >
                <span class="header-username">
                    <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <a href="/logout" class="btn btn-outline btn-sm">로그아웃</a>

        <?php else: ?>

            <a href="/login" class="btn btn-outline btn-sm">로그인</a>
            <a href="/register" class="btn btn-primary btn-sm">회원가입</a>

        <?php endif; ?>
    </nav>

</header>
