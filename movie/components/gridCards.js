/**
 * @fileoverview 영화 / 배우 카드 그리드 컴포넌트.
 * type에 따라 포스터(영화) 또는 프로필(배우) 카드를 렌더링합니다.
 */

import { TMDB_IMAGE_BASE_URL } from '../config.js';
import { router } from '../router.js';

/**
 * 컨테이너에 카드 그리드를 렌더링합니다.
 * @param {HTMLElement} containerEl - 카드를 삽입할 grid 컨테이너
 * @param {object[]} items - TMDB 영화 또는 배우 객체 배열
 * @param {'movie'|'actor'} type - 카드 유형
 * @param {boolean} [append=false] - true면 기존 카드에 추가(더보기), false면 초기화 후 렌더
 */
export function renderGridCards(containerEl, items, type, append = false) {
  if (!append) {
    containerEl.innerHTML = '';
  }

  const fragment = document.createDocumentFragment();

  items.forEach((item) => {
    const card = type === 'movie'
      ? createMovieCard(item)
      : createActorCard(item);
    fragment.appendChild(card);
  });

  containerEl.appendChild(fragment);
}

/**
 * 영화 카드 요소를 생성합니다.
 * @param {object} movie - TMDB 영화 객체
 * @returns {HTMLElement}
 */
function createMovieCard(movie) {
  const card = document.createElement('div');
  card.className = 'card card--movie';
  card.setAttribute('role', 'button');
  card.setAttribute('tabindex', '0');
  card.setAttribute('aria-label', movie.title ?? '영화');

  const imgSrc = movie.poster_path
    ? `${TMDB_IMAGE_BASE_URL}/w342${movie.poster_path}`
    : null;

  card.innerHTML = `
    <div class="card__img-wrap">
      ${imgSrc
        ? `<img class="card__img" src="${imgSrc}" alt="${escapeHtml(movie.title ?? '')}" loading="lazy" />`
        : `<div class="card__placeholder">🎬<br>${escapeHtml(movie.title ?? '제목 없음')}</div>`
      }
    </div>
    <div class="card__body">
      <p class="card__title">${escapeHtml(movie.title ?? '제목 없음')}</p>
      ${movie.release_date
        ? `<p class="card__sub">${movie.release_date.slice(0, 4)}</p>`
        : ''
      }
    </div>
  `;

  // 클릭 / 키보드 진입
  const navigate = () => router.push(`/movie/${movie.id}`);
  card.addEventListener('click', navigate);
  card.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') navigate();
  });

  return card;
}

/**
 * 배우 카드 요소를 생성합니다.
 * @param {object} actor - TMDB 배우(cast) 객체
 * @returns {HTMLElement}
 */
function createActorCard(actor) {
  const card = document.createElement('div');
  card.className = 'card card--actor';

  const imgSrc = actor.profile_path
    ? `${TMDB_IMAGE_BASE_URL}/w185${actor.profile_path}`
    : null;

  card.innerHTML = `
    <div class="card__img-wrap">
      ${imgSrc
        ? `<img class="card__img" src="${imgSrc}" alt="${escapeHtml(actor.name ?? '')}" loading="lazy" />`
        : `<div class="card__placeholder">👤</div>`
      }
    </div>
    <div class="card__body">
      <p class="card__title">${escapeHtml(actor.name ?? '이름 없음')}</p>
      ${actor.character
        ? `<p class="card__sub">${escapeHtml(actor.character)}</p>`
        : ''
      }
    </div>
  `;

  return card;
}

/**
 * XSS 방지를 위해 HTML 특수문자를 이스케이프합니다.
 * @param {string} str
 * @returns {string}
 */
function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
