/**
 * @fileoverview 즐겨찾기 버튼 컴포넌트.
 * 로그인 상태에 따라 즐겨찾기 추가/해제 또는 /login 리다이렉트 동작을 수행합니다.
 * API 실패 시 버튼 상태를 호출 전으로 원복합니다.
 */

import { isLoggedIn, currentUser } from '../store/auth.js';
import {
  isFavorited,
  addFavorite,
  removeFavorite,
  getFavoriteCount,
} from '../store/favorites.js';
import { router } from '../router.js';

/**
 * 즐겨찾기 버튼을 컨테이너에 삽입합니다.
 * @param {HTMLElement} containerEl - 버튼을 삽입할 부모 요소
 * @param {{ movieId: string|number, movieTitle: string, moviePost: string, movieRuntime: string }} movieData
 * @returns {Promise<void>}
 */
export async function renderFavorite(containerEl, movieData) {
  const { movieId, movieTitle, moviePost, movieRuntime } = movieData;

  const loggedIn = isLoggedIn();
  const userId   = loggedIn ? currentUser().userId : null;

  const btn = document.createElement('button');
  btn.className = 'btn-favorite';
  btn.id = 'btn-favorite';
  btn.setAttribute('aria-label', '즐겨찾기');
  btn.title = loggedIn ? '' : '로그인 후 이용 가능';

  // 초기 로딩 표시 (클릭 방지용 disabled)
  btn.innerHTML = `♡ 즐겨찾기 <span class="favorite-count">...</span>`;
  btn.disabled = true;
  containerEl.appendChild(btn);

  /**
   * API에서 현재 즐겨찾기 상태를 읽어 버튼 UI를 갱신합니다.
   */
  async function refresh() {
    const [fav, count] = await Promise.all([
      loggedIn ? isFavorited(userId, String(movieId)) : Promise.resolve(false),
      getFavoriteCount(movieId),
    ]);

    btn.innerHTML = `
      ${fav ? '★' : '☆'} ${fav ? '즐겨찾기 해제' : '즐겨찾기 추가'}
      <span class="favorite-count">${count}</span>
    `;
    btn.setAttribute('aria-label', fav ? '즐겨찾기 해제' : '즐겨찾기 추가');
    btn.classList.toggle('active', loggedIn && fav);
    btn.classList.toggle('ready',  loggedIn && !fav);
    btn.dataset.fav = fav ? '1' : '0';
  }

  // 초기 상태 로드 후 버튼 활성화
  // ※ 비로그인이어도 disabled=false: 클릭하면 /login 리다이렉트 처리
  await refresh();
  btn.disabled = false;

  btn.addEventListener('click', async () => {
    // 비로그인 → 로그인 페이지로
    if (!loggedIn) {
      router.push('/login');
      return;
    }

    const wasFav = btn.dataset.fav === '1';
    btn.disabled = true;

    try {
      const result = wasFav
        ? await removeFavorite(userId, String(movieId))
        : await addFavorite(userId, { movieId: String(movieId), movieTitle, moviePost, movieRuntime });

      if (result?.success) {
        await refresh();
      } else {
        const msg = result?.message ?? 'API 오류가 발생했습니다.';
        console.error('[Favorite]', msg);
        showToast(msg);
      }
    } catch (err) {
      console.error('[Favorite] 예외 발생:', err);
    } finally {
      // 항상 버튼 재활성화
      btn.disabled = false;
    }
  });
}

/**
 * 화면 하단에 잠깐 메시지를 표시합니다.
 * @param {string} message
 */
function showToast(message) {
  const existing = document.getElementById('fav-toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.id = 'fav-toast';
  toast.textContent = message;
  toast.style.cssText = `
    position:fixed; bottom:1.5rem; left:50%; transform:translateX(-50%);
    background:rgba(30,30,30,0.95); color:#f5f5f1; padding:0.6rem 1.2rem;
    border-radius:6px; font-size:0.875rem; z-index:9999;
    border:1px solid #444; pointer-events:none;
  `;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 2500);
}
