<?php
$pageTitle = htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') . ' - YourTube';
require BASE_PATH . '/views/layouts/header.php';
?>

<div class="watch-layout">

    <!-- ── 좌측 메인 ─────────────────────────────────────────── -->
    <main class="watch-main">

        <!-- 플레이어 -->
        <div class="watch-player-wrap">
            <video
                controls
                preload="metadata"
                src="/uploads/videos/<?= htmlspecialchars($video['filename'], ENT_QUOTES, 'UTF-8') ?>"
            ></video>
        </div>

        <!-- 영상 정보 -->
        <div class="watch-info">

            <h1 class="watch-title">
                <?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <!-- 조회수 + 좋아요 행 -->
            <div class="watch-meta-row">
                <div class="watch-stats">
                    <span
                        id="view-count"
                        data-video-id="<?= (int)$video['id'] ?>"
                    >조회수 <?= number_format((int)$video['views']) ?>회</span>
                    <span class="dot">&middot;</span>
                    <span><?= time_ago($video['created_at']) ?></span>
                </div>

                <button
                    id="like-btn"
                    class="btn btn-outline btn-sm<?= $isLiked ? ' liked' : '' ?>"
                    data-video-id="<?= (int)$video['id'] ?>"
                    data-liked="<?= $isLiked ? '1' : '0' ?>"
                    <?= empty($_SESSION['user_id']) ? 'disabled title="로그인이 필요합니다."' : '' ?>
                >
                    <svg width="16" height="16" viewBox="0 0 24 24"
                         fill="<?= $isLiked ? 'var(--primary)' : 'none' ?>"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/>
                        <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                    </svg>
                    <span id="like-count"><?= number_format($likeCount) ?></span>
                </button>
            </div>

            <!-- 업로더 행 -->
            <div class="uploader-row">
                <img
                    class="avatar avatar-lg"
                    src="<?= htmlspecialchars(gravatar_url($video['email'], 48), ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($video['username'], ENT_QUOTES, 'UTF-8') ?>"
                    width="48" height="48"
                >
                <div class="uploader-info">
                    <p class="uploader-name">
                        <?= htmlspecialchars($video['username'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p id="sub-count" class="sub-count">
                        구독자 <?= number_format($subCount) ?>명
                    </p>
                </div>

                <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] !== (int)$video['user_id']): ?>
                    <button
                        id="subscribe-btn"
                        class="btn btn-sm <?= $isSubscribed ? 'btn-outline' : 'btn-red' ?>"
                        data-channel-id="<?= (int)$video['user_id'] ?>"
                        data-subscribed="<?= $isSubscribed ? '1' : '0' ?>"
                    ><?= $isSubscribed ? '구독중' : '구독' ?></button>
                <?php endif; ?>
            </div>

            <!-- 설명 -->
            <?php if (!empty($video['description'])): ?>
                <details class="watch-description">
                    <summary>설명 보기</summary>
                    <div class="description-body">
                        <?= nl2br(htmlspecialchars($video['description'], ENT_QUOTES, 'UTF-8')) ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>

        <!-- 댓글 섹션 -->
        <section class="comments-section">
            <h3 class="comments-title">댓글 <span id="comment-count"><?= count($comments) ?></span>개</h3>

            <!-- 댓글 입력 (로그인 시) -->
            <?php if (!empty($_SESSION['user_id'])): ?>
                <div class="comment-input-wrap">
                    <img
                        class="avatar"
                        src="<?= htmlspecialchars(gravatar_url($_SESSION['email'], 40), ENT_QUOTES, 'UTF-8') ?>"
                        alt="내 프로필"
                        width="40" height="40"
                    >
                    <div class="comment-input-inner">
                        <textarea
                            id="comment-input"
                            placeholder="댓글 추가..."
                            rows="1"
                        ></textarea>
                        <div class="comment-input-actions">
                            <button
                                id="comment-submit"
                                class="btn btn-primary btn-sm"
                                data-video-id="<?= (int)$video['id'] ?>"
                            >등록</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 댓글 목록 -->
            <div id="comment-list">
                <?php foreach ($comments as $c): ?>
                    <div class="comment" data-id="<?= (int)$c['id'] ?>">
                        <img
                            class="avatar"
                            src="<?= htmlspecialchars(gravatar_url($c['email'], 40), ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($c['username'], ENT_QUOTES, 'UTF-8') ?>"
                            width="40" height="40" loading="lazy"
                        >
                        <div class="comment-body">
                            <div class="comment-header">
                                <span class="comment-author">
                                    <?= htmlspecialchars($c['username'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="comment-date"><?= time_ago($c['created_at']) ?></span>
                            </div>
                            <p class="comment-content">
                                <?= nl2br(htmlspecialchars($c['content'], ENT_QUOTES, 'UTF-8')) ?>
                            </p>
                            <div class="comment-footer">
                                <button class="reply-toggle" data-comment-id="<?= (int)$c['id'] ?>">
                                    답글 <?= count($c['replies']) ?>개
                                </button>
                            </div>

                            <!-- 대댓글 목록 -->
                            <div class="replies" id="replies-<?= (int)$c['id'] ?>">
                                <?php foreach ($c['replies'] as $r): ?>
                                    <div class="comment reply" data-id="<?= (int)$r['id'] ?>">
                                        <img
                                            class="avatar"
                                            src="<?= htmlspecialchars(gravatar_url($r['email'], 32), ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8') ?>"
                                            width="32" height="32" loading="lazy"
                                        >
                                        <div class="comment-body">
                                            <div class="comment-header">
                                                <span class="comment-author">
                                                    <?= htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                                <span class="comment-date"><?= time_ago($r['created_at']) ?></span>
                                            </div>
                                            <p class="comment-content">
                                                <?= nl2br(htmlspecialchars($r['content'], ENT_QUOTES, 'UTF-8')) ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- 대댓글 입력 폼 (기본 hidden) -->
                            <?php if (!empty($_SESSION['user_id'])): ?>
                                <div class="reply-form" id="reply-form-<?= (int)$c['id'] ?>" style="display:none;">
                                    <textarea placeholder="답글을 입력하세요..." rows="2"></textarea>
                                    <button
                                        class="btn btn-outline btn-sm reply-submit"
                                        data-parent-id="<?= (int)$c['id'] ?>"
                                    >등록</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($comments)): ?>
                    <p class="no-comments">첫 번째 댓글을 남겨보세요.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- ── 우측 사이드바 ────────────────────────────────────────── -->
    <aside class="watch-sidebar">
        <h3 class="sidebar-title">최근 영상</h3>

        <?php if (empty($relatedVideos)): ?>
            <p class="no-related">관련 영상이 없습니다.</p>
        <?php else: ?>
            <div class="related-list">
                <?php foreach ($relatedVideos as $rv): ?>
                    <a href="/watch?v=<?= (int)$rv['id'] ?>" class="related-item">
                        <div class="related-thumb">
                            <img
                                src="/uploads/thumbnails/<?= htmlspecialchars($rv['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($rv['title'], ENT_QUOTES, 'UTF-8') ?>"
                                loading="lazy"
                                onerror="this.src='/public/img/default_thumbnail.jpg'"
                            >
                        </div>
                        <div class="related-info">
                            <p class="related-title">
                                <?= htmlspecialchars($rv['title'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="related-meta">
                                <?= htmlspecialchars($rv['username'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="related-meta">
                                조회수 <?= number_format((int)$rv['views']) ?>회
                                &nbsp;·&nbsp;
                                <?= time_ago($rv['created_at']) ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </aside>

</div><!-- /.watch-layout -->

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>

<script src="/public/js/like.js"></script>
<script src="/public/js/subscribe.js"></script>
<script src="/public/js/comment.js"></script>
<script>
// 페이지 로드 시 조회수 증가 + 표시 갱신
document.addEventListener('DOMContentLoaded', function () {
    const vcEl    = document.getElementById('view-count');
    const videoId = parseInt(vcEl.dataset.videoId, 10);

    fetch('/api/view_count.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ video_id: videoId }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.views !== undefined) {
            vcEl.textContent = '조회수 ' + data.views.toLocaleString('ko-KR') + '회';
        }
    })
    .catch(() => {/* 실패해도 초기 렌더 값 유지 */});
});
</script>
