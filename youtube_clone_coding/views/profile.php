<?php
$pageTitle = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') . ' - YourTube';
require BASE_PATH . '/views/layouts/header.php';
?>

<main class="profile-main">

    <!-- 채널 헤더 -->
    <div class="profile-header">
        <img
            class="profile-avatar"
            src="<?= htmlspecialchars($user['gravatar'], ENT_QUOTES, 'UTF-8') ?>"
            alt="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>"
            width="80" height="80"
        >
        <div class="profile-meta">
            <h1 class="profile-username">
                <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="profile-stats">
                구독자 <?= number_format($subCount) ?>명
                &nbsp;·&nbsp;
                영상 <?= count($videos) ?>개
                &nbsp;·&nbsp;
                가입일 <?= htmlspecialchars(date('Y년 m월', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <?php if ($isOwner): ?>
            <a href="/?route=profile_edit" class="btn btn-outline btn-sm profile-edit-btn">
                프로필 편집
            </a>
        <?php endif; ?>
    </div>

    <!-- 업로드 영상 목록 -->
    <section class="profile-videos">
        <h2 class="section-title">업로드한 영상</h2>

        <?php if (empty($videos)): ?>
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <path d="M8 21h8M12 17v4"/>
                </svg>
                <p>아직 업로드된 영상이 없습니다.</p>
                <?php if ($isOwner): ?>
                    <a href="/upload" class="btn btn-primary btn-sm">첫 영상 업로드하기</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="video-grid">
                <?php foreach ($videos as $v): ?>
                    <div class="video-card-wrap">
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
                                <div class="video-text">
                                    <p class="video-title">
                                        <?= htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <p class="video-meta">
                                        조회수 <?= number_format((int)$v['views']) ?>회
                                        &nbsp;·&nbsp;
                                        <?= time_ago($v['created_at']) ?>
                                    </p>
                                </div>
                            </div>
                        </a>

                        <?php if ($isOwner): ?>
                            <form
                                class="delete-form"
                                method="POST"
                                action="/video_delete"
                                onsubmit="return confirm('정말 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')"
                            >
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_generate(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="video_id" value="<?= (int)$v['id'] ?>">
                                <button type="submit" class="btn-delete" aria-label="영상 삭제">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M9 3h6l1 1h4v2H4V4h4L9 3zm-3 5h12l-1 13H7L6 8zm4 2v9h1v-9H10zm3 0v9h1v-9h-1z"/>
                                    </svg>
                                    삭제
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
