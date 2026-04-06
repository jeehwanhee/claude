<?php $pageTitle = '동영상 업로드 - YourTube'; require BASE_PATH . '/views/layouts/header.php'; ?>

<main class="upload-wrapper">
    <div class="upload-card card">
        <h1>동영상 업로드</h1>

        <!-- 에러 배너 (JS에서 동적으로도 사용) -->
        <div id="upload-error-banner" class="form-alert-error" style="display:none;"></div>

        <?php if (!empty($errors)): ?>
            <div class="form-alert-error">
                <?php foreach ($errors as $msg): ?>
                    <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form id="upload-form" method="POST" action="/upload" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>

            <!-- 제목 -->
            <div class="form-group">
                <label for="title">제목 <span class="required">*</span></label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= htmlspecialchars($old['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    class="<?= isset($errors['title']) ? 'is-error' : '' ?>"
                    maxlength="200"
                    required
                >
                <?php if (isset($errors['title'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <!-- 설명 -->
            <div class="form-group">
                <label for="description">설명 <span class="optional">(선택)</span></label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <!-- 파일 선택 -->
            <div class="form-group">
                <label for="video">동영상 파일 <span class="required">*</span></label>
                <div class="file-input-wrap <?= isset($errors['video']) ? 'is-error' : '' ?>" id="file-drop-zone">
                    <input
                        type="file"
                        id="video"
                        name="video"
                        accept=".mp4,.webm,.mov"
                        required
                    >
                    <div class="file-input-ui">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <p id="file-label">파일을 선택하거나 이 영역에 끌어다 놓으세요</p>
                        <small>mp4, webm, mov · 최대 500MB</small>
                    </div>
                </div>
                <?php if (isset($errors['video'])): ?>
                    <span class="field-error" id="video-field-error"><?= htmlspecialchars($errors['video'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <span class="field-error" id="video-field-error" style="display:none;"></span>
                <?php endif; ?>
            </div>

            <!-- 프로그레스 바 -->
            <div id="progress-wrap" style="display:none;">
                <div class="progress-bar-track">
                    <div class="progress-bar-fill" id="progress-fill"></div>
                </div>
                <p class="progress-text" id="progress-text">0%</p>
            </div>

            <!-- 제출 -->
            <div class="form-submit">
                <button type="submit" id="submit-btn" class="btn btn-primary">업로드</button>
            </div>
        </form>
    </div>
</main>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>

<script>
(function () {
    const form       = document.getElementById('upload-form');
    const fileInput  = document.getElementById('video');
    const fileLabel  = document.getElementById('file-label');
    const dropZone   = document.getElementById('file-drop-zone');
    const submitBtn  = document.getElementById('submit-btn');
    const progressWrap  = document.getElementById('progress-wrap');
    const progressFill  = document.getElementById('progress-fill');
    const progressText  = document.getElementById('progress-text');
    const errorBanner   = document.getElementById('upload-error-banner');
    const videoFieldErr = document.getElementById('video-field-error');

    // ── 파일 선택 미리보기 ──────────────────────────────────────────
    fileInput.addEventListener('change', () => updateFileLabel(fileInput.files[0]));

    function updateFileLabel(file) {
        if (!file) return;
        const mb = (file.size / 1024 / 1024).toFixed(1);
        fileLabel.textContent = `${file.name}  (${mb} MB)`;
        dropZone.classList.add('has-file');
    }

    // ── 드래그 앤 드롭 ────────────────────────────────────────────
    ['dragenter', 'dragover'].forEach(evt =>
        dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.add('drag-over'); })
    );
    ['dragleave', 'drop'].forEach(evt =>
        dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.remove('drag-over'); })
    );
    dropZone.addEventListener('drop', e => {
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            updateFileLabel(file);
        }
    });

    // ── XHR 업로드 ────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // 에러 초기화
        errorBanner.style.display = 'none';
        errorBanner.textContent   = '';
        videoFieldErr.style.display = 'none';
        videoFieldErr.textContent   = '';

        const formData = new FormData(form);

        // 버튼 비활성화
        submitBtn.disabled    = true;
        submitBtn.textContent = '업로드 중...';
        progressWrap.style.display = 'block';
        setProgress(0);

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', e => {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                setProgress(pct);
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.redirect) {
                        setProgress(100);
                        progressText.textContent = '완료! 이동 중...';
                        window.location.href = res.redirect;
                        return;
                    }
                    if (res.errors) {
                        showErrors(res.errors);
                    }
                } catch (_) {
                    showBanner('응답 처리 중 오류가 발생했습니다.');
                }
            } else {
                showBanner('서버 오류가 발생했습니다. 다시 시도해주세요.');
            }
            resetButton();
        });

        xhr.addEventListener('error', () => {
            showBanner('네트워크 오류가 발생했습니다. 연결을 확인해주세요.');
            resetButton();
        });

        xhr.open('POST', '/upload');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    });

    function setProgress(pct) {
        progressFill.style.width = pct + '%';
        progressText.textContent = pct + '%';
    }

    function showErrors(errors) {
        progressWrap.style.display = 'none';
        if (errors.video) {
            videoFieldErr.textContent   = errors.video;
            videoFieldErr.style.display = 'block';
            dropZone.classList.add('is-error');
        }
        if (errors.title) {
            const titleErr = document.querySelector('[name="title"]');
            if (titleErr) {
                titleErr.classList.add('is-error');
                let span = titleErr.nextElementSibling;
                if (!span || !span.classList.contains('field-error')) {
                    span = document.createElement('span');
                    span.className = 'field-error';
                    titleErr.after(span);
                }
                span.textContent = errors.title;
            }
        }
        const first = Object.values(errors)[0];
        if (first) showBanner(first);
    }

    function showBanner(msg) {
        errorBanner.textContent   = msg;
        errorBanner.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetButton() {
        submitBtn.disabled    = false;
        submitBtn.textContent = '업로드';
    }
})();
</script>
