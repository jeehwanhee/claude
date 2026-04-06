(function () {
    const btn = document.getElementById('subscribe-btn');
    if (!btn) return;

    const csrf    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const countEl = document.getElementById('sub-count');

    btn.addEventListener('click', function () {
        if (btn.disabled) return;

        btn.disabled = true;

        const fd = new FormData();
        fd.append('channel_id', btn.dataset.channelId);
        fd.append('csrf_token', csrf);

        fetch('/api/subscribe.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) { console.error(data.error); return; }

            const subscribed = data.subscribed;
            btn.dataset.subscribed = subscribed ? '1' : '0';
            btn.textContent = subscribed ? '구독중' : '구독';
            btn.classList.toggle('btn-outline', subscribed);
            btn.classList.toggle('btn-red',     !subscribed);

            if (countEl) {
                countEl.textContent = '구독자 ' + data.count.toLocaleString('ko-KR') + '명';
            }
        })
        .catch(err => console.error('Subscribe error:', err))
        .finally(() => { btn.disabled = false; });
    });
})();
