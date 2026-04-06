(function () {
    const btn = document.getElementById('like-btn');
    if (!btn) return;

    const csrf   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const countEl = document.getElementById('like-count');

    btn.addEventListener('click', function () {
        if (btn.disabled) return;

        btn.disabled = true;

        const fd = new FormData();
        fd.append('video_id',   btn.dataset.videoId);
        fd.append('csrf_token', csrf);

        fetch('/api/like.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) { console.error(data.error); return; }

            const liked = data.liked;
            btn.dataset.liked = liked ? '1' : '0';
            btn.classList.toggle('liked', liked);

            // 아이콘 색상 전환
            const svg = btn.querySelector('svg');
            if (svg) svg.style.fill = liked ? 'var(--primary)' : 'currentColor';

            if (countEl) {
                countEl.textContent = data.count.toLocaleString('ko-KR');
            }
        })
        .catch(err => console.error('Like error:', err))
        .finally(() => { btn.disabled = false; });
    });
})();
