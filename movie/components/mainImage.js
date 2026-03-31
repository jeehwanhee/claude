/**
 * @fileoverview 히어로 배너(MainImage) 컴포넌트.
 * TMDB backdrop 이미지와 다크 그라데이션 오버레이를 렌더링합니다.
 */

import { TMDB_IMAGE_BASE_URL } from '../config.js';

/**
 * 히어로 배너 요소를 생성해 반환합니다.
 * @param {{ backdropPath: string|null, title: string, overview: string }} options
 * @returns {HTMLElement}
 */
export function renderMainImage({ backdropPath, title, overview }) {
  const hero = document.createElement('div');
  hero.className = 'hero';

  const bgStyle = backdropPath
    ? `background-image: url('${TMDB_IMAGE_BASE_URL}/w1280${backdropPath}');`
    : 'background-color: #0a0a0a;';

  hero.innerHTML = `
    <div class="hero__bg" style="${bgStyle}"></div>
    <div class="hero__gradient"></div>
    <div class="hero__content">
      <h2 class="hero__title">${escapeHtml(title)}</h2>
      ${overview ? `<p class="hero__overview">${escapeHtml(overview)}</p>` : ''}
    </div>
  `;

  return hero;
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
