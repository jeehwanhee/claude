(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── 댓글 등록 ───────────────────────────────────────────────────────
    const submitBtn   = document.getElementById('comment-submit');
    const commentInput = document.getElementById('comment-input');

    if (submitBtn && commentInput) {
        submitBtn.addEventListener('click', function () {
            const content = commentInput.value.trim();
            if (!content) return;

            submitBtn.disabled = true;

            const fd = new FormData();
            fd.append('video_id',   submitBtn.dataset.videoId);
            fd.append('content',    content);
            fd.append('csrf_token', csrf);

            fetch('/api/comment.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            })
            .then(r => r.json())
            .then(data => {
                if (data.error) { alert(data.error); return; }
                const list = document.getElementById('comment-list');
                list.insertAdjacentHTML('afterbegin', buildCommentHTML(data.comment));
                commentInput.value = '';
                attachCommentHandlers(list.firstElementChild);
            })
            .catch(err => console.error('Comment error:', err))
            .finally(() => { submitBtn.disabled = false; });
        });

        // textarea 자동 높이 조절
        commentInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }

    // ── 답글 버튼 / 대댓글 등록 (기존 DOM) ─────────────────────────────
    document.querySelectorAll('.comment').forEach(attachCommentHandlers);

    // ── 헬퍼 ────────────────────────────────────────────────────────────

    function attachCommentHandlers(commentEl) {
        if (!commentEl) return;

        // 답글 폼 토글
        const toggleBtn = commentEl.querySelector('.reply-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const formId   = 'reply-form-' + this.dataset.commentId;
                const formEl   = document.getElementById(formId);
                if (!formEl) return;
                const visible = formEl.style.display !== 'none';
                formEl.style.display = visible ? 'none' : 'block';
                if (!visible) formEl.querySelector('textarea')?.focus();
            });
        }

        // 대댓글 등록
        const replySubmit = commentEl.querySelector('.reply-submit');
        if (replySubmit) {
            replySubmit.addEventListener('click', function () {
                const parentId = this.dataset.parentId;
                const textarea = document.querySelector(`#reply-form-${parentId} textarea`);
                const content  = textarea?.value.trim();
                if (!content) return;

                this.disabled = true;

                const fd = new FormData();
                fd.append('video_id',   submitBtn?.dataset.videoId
                                     ?? document.getElementById('comment-submit')?.dataset.videoId
                                     ?? document.querySelector('[data-video-id]')?.dataset.videoId);
                fd.append('content',    content);
                fd.append('parent_id',  parentId);
                fd.append('csrf_token', csrf);

                const btn = this;
                fetch('/api/comment.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                })
                .then(r => r.json())
                .then(data => {
                    if (data.error) { alert(data.error); return; }
                    const repliesEl = document.getElementById('replies-' + parentId);
                    if (repliesEl) {
                        repliesEl.insertAdjacentHTML('beforeend', buildReplyHTML(data.comment));
                    }
                    textarea.value = '';
                    document.getElementById(`reply-form-${parentId}`).style.display = 'none';
                })
                .catch(err => console.error('Reply error:', err))
                .finally(() => { btn.disabled = false; });
            });
        }
    }

    function buildCommentHTML(c) {
        const escaped = escHtml(c.content).replace(/\n/g, '<br>');
        return `
        <div class="comment" data-id="${c.id}">
            <img class="avatar" src="${escHtml(c.avatar)}" alt="${escHtml(c.username)}" width="40" height="40" loading="lazy">
            <div class="comment-body">
                <div class="comment-header">
                    <span class="comment-author">${escHtml(c.username)}</span>
                    <span class="comment-date">${escHtml(c.created_at)}</span>
                </div>
                <p class="comment-content">${escaped}</p>
                <div class="comment-footer">
                    <button class="reply-toggle" data-comment-id="${c.id}">답글 0개</button>
                </div>
                <div class="replies" id="replies-${c.id}"></div>
                <div class="reply-form" id="reply-form-${c.id}" style="display:none;">
                    <textarea placeholder="답글을 입력하세요..." rows="2"></textarea>
                    <button class="btn btn-outline btn-sm reply-submit" data-parent-id="${c.id}">등록</button>
                </div>
            </div>
        </div>`;
    }

    function buildReplyHTML(c) {
        const escaped = escHtml(c.content).replace(/\n/g, '<br>');
        return `
        <div class="comment reply" data-id="${c.id}">
            <img class="avatar" src="${escHtml(c.avatar)}" alt="${escHtml(c.username)}" width="32" height="32" loading="lazy">
            <div class="comment-body">
                <div class="comment-header">
                    <span class="comment-author">${escHtml(c.username)}</span>
                    <span class="comment-date">${escHtml(c.created_at)}</span>
                </div>
                <p class="comment-content">${escaped}</p>
            </div>
        </div>`;
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
})();
