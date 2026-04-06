<?php $pageTitle = 'YourTube'; require BASE_PATH . '/views/layouts/header.php'; ?>

<main class="home-main">
    <h2 class="section-title">추천 영상</h2>

    <?php if (empty($videos)): ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <path d="M8 21h8M12 17v4"/>
            </svg>
            <p>아직 업로드된 영상이 없습니다.</p>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="/upload" class="btn btn-primary">첫 영상 업로드하기</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="video-grid">
            <?php foreach ($videos as $v): ?>
                <a href="/watch?v=<?= (int)$v['id'] ?>" class="video-card">

                    <div class="thumbnail-wrap">
                        <img
                            src="/uploads/thumbnails/<?= htmlspecialchars($v['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8') ?>"
                            loading="lazy"
                            onerror="this.src='/public/img/default_thumbnail.jpg'"
                        >
                    </div>

                    <div class="video-info">
                        <img
                            class="avatar"
                            src="<?= htmlspecialchars(gravatar_url($v['email'] ?? '', 36), ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($v['username'], ENT_QUOTES, 'UTF-8') ?>"
                            width="36"
                            height="36"
                            loading="lazy"
                        >
                        <div class="video-text">
                            <p class="video-title"><?= htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="video-meta"><?= htmlspecialchars($v['username'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="video-meta">
                                조회수 <?= number_format((int)$v['views']) ?>회
                                &nbsp;·&nbsp;
                                <?= time_ago($v['created_at']) ?>
                            </p>
                        </div>
                    </div>

                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
