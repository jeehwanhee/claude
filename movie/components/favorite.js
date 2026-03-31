/**
 * @fileoverview 즐겨찾기 버튼 컴포넌트.
 * 로그인 상태에 따라 즐겨찾기 추가/해제 또는 /login 리다이렉트 동작을 수행합니다.
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
 */
export function renderFavorite(containerEl, movieData) {
  const { movieId, movieTitle, moviePost, movieRuntime } = movieData;

  const loggedIn = isLoggedIn();
  const userId   = loggedIn ? currentUser().userId : null;

  const btn = document.createElement('button');
  btn.className = 'btn-favorite';
  btn.id = 'btn-favorite';
  if (!loggedIn) {
    btn.title = '로그인 후 이용 가능';
  }

  /**
   * localStorage 기준으로 즐겨찾기 여부를 새로 읽습니다.
   * @returns {boolean}
   */
  function isFav() {
    return loggedIn && isFavorited(userId, String(movieId));
  }

  /**
   * 현재 즐겨찾기 상태를 반영해 버튼을 즉시 업데이트합니다.
   */
  function refresh() {
    const fav   = isFav();
    const count = getFavoriteCount(movieId);

    btn.innerHTML = `
      ${fav ? '★' : '☆'} ${fav ? '즐겨찾기 해제' : '즐겨찾기 추가'}
      <span class="favorite-count">${count}</span>
    `;

    // 클래스 상태 동기화
    btn.classList.toggle('active', loggedIn && fav);
    btn.classList.toggle('ready',  loggedIn && !fav);
  }

  // 초기 렌더
  refresh();

  btn.addEventListener('click', () => {
    if (!loggedIn) {
      router.push('/login');
      return;
    }

    if (isFav()) {
      removeFavorite(userId, String(movieId));
    } else {
      addFavorite(userId, {
        movieId:      String(movieId),
        movieTitle,
        moviePost,
        movieRuntime,
      });
    }

    // localStorage 반영 후 즉시 UI 업데이트
    refresh();
  });

  containerEl.appendChild(btn);
}
