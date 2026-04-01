/**
 * @fileoverview 영화 상세 페이지.
 * 영화 정보와 출연진을 렌더링하며, 즐겨찾기 버튼을 연결합니다.
 */

import { renderNavBar } from '../components/navbar.js';
import { renderMainImage } from '../components/mainImage.js';
import { renderGridCards } from '../components/gridCards.js';
import { renderFavorite } from '../components/favorite.js';
import { renderFooter } from '../components/footer.js';
import { getMovieDetail, getMovieCredits } from '../api/tmdb.js';
import { router } from '../router.js';

/**
 * 금액을 달러 단위로 포맷합니다.
 * @param {number} value
 * @returns {string}
 */
function formatCurrency(value) {
  if (!value) return '정보 없음';
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);
}

/**
 * 런타임(분)을 "Xh Ym" 형식으로 포맷합니다.
 * @param {number} minutes
 * @returns {string}
 */
function formatRuntime(minutes) {
  if (!minutes) return '정보 없음';
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  return h > 0 ? `${h}시간 ${m}분` : `${m}분`;
}

/**
 * 날짜 문자열을 한국어 형식으로 포맷합니다.
 * @param {string} dateStr - 'YYYY-MM-DD'
 * @returns {string}
 */
function formatDate(dateStr) {
  if (!dateStr) return '정보 없음';
  return new Date(dateStr).toLocaleDateString('ko-KR', { year: 'numeric', month: 'long', day: 'numeric' });
}

/**
 * 영화 메타 정보 그리드 HTML을 생성합니다.
 * @param {object} movie
 * @returns {string}
 */
function buildInfoGrid(movie) {
  const items = [
    { label: '개봉일', value: formatDate(movie.release_date) },
    { label: '수익', value: formatCurrency(movie.revenue) },
    { label: '상영 시간', value: formatRuntime(movie.runtime) },
    { label: '평점', value: movie.vote_average ? `⭐ ${movie.vote_average.toFixed(1)}` : '정보 없음', cssClass: movie.vote_average ? 'movie-info__value--rating' : '' },
    { label: '투표 수', value: movie.vote_count ? movie.vote_count.toLocaleString('ko-KR') + '표' : '정보 없음' },
    { label: '상태', value: movie.status ?? '정보 없음' },
    { label: '인기도', value: movie.popularity ? movie.popularity.toFixed(1) : '정보 없음' },
  ];

  return items.map(({ label, value, cssClass = '' }) => `
    <div class="movie-info__item">
      <p class="movie-info__label">${label}</p>
      <p class="movie-info__value ${cssClass}">${value}</p>
    </div>
  `).join('');
}

/**
 * 영화 상세 페이지를 렌더링합니다.
 * @param {HTMLElement} container - #app 요소
 * @param {{ id: string }} params - 라우트 파라미터
 */
export default async function MovieDetailPage(container, params) {
  const movieId = params.id;

  // ── NavBar ──
  renderNavBar(container);

  // ── 메인 콘텐츠 래퍼 ──
  const main = document.createElement('main');
  main.className = 'main-content movie-detail';
  container.appendChild(main);

  // ── 로딩 스피너 ──
  const spinnerWrap = document.createElement('div');
  spinnerWrap.className = 'spinner-wrap';
  spinnerWrap.setAttribute('role', 'status');
  spinnerWrap.setAttribute('aria-live', 'polite');
  spinnerWrap.setAttribute('aria-label', '영화 정보를 불러오는 중');
  spinnerWrap.innerHTML = '<div class="spinner"></div>';
  main.appendChild(spinnerWrap);

  let movie = null;
  let credits = null;

  try {
    // ── 병렬 데이터 패치 ──
    [movie, credits] = await Promise.all([
      getMovieDetail(movieId),
      getMovieCredits(movieId),
    ]);
  } finally {
    spinnerWrap.remove();
  }

  if (!movie) {
    main.innerHTML = `
      <div role="alert" style="text-align:center;padding:4rem;color:var(--color-text-muted)">
        <p>영화 정보를 불러올 수 없습니다.</p>
        <button onclick="" class="btn-load-more" style="margin-top:1.5rem" id="btn-home">홈으로</button>
      </div>`;
    main.querySelector('#btn-home').addEventListener('click', () => router.push('/'));
    renderFooter(container);
    return;
  }

  // ── 히어로 배너 ──
  main.appendChild(
    renderMainImage({
      backdropPath: movie.backdrop_path,
      title: movie.title,
      overview: movie.overview,
    })
  );

  // ── 영화 정보 섹션 ──
  const infoSection = document.createElement('section');
  infoSection.className = 'movie-info';
  infoSection.innerHTML = `
    <h1 class="movie-info__title">${escapeHtml(movie.title)}</h1>
    <div class="movie-info__grid">${buildInfoGrid(movie)}</div>
    <div class="movie-info__actions">
      <!-- 배우 토글 버튼 -->
      <button class="btn-cast-toggle" id="btn-cast-toggle">
        👥 배우 보기
      </button>
    </div>
  `;
  main.appendChild(infoSection);

  // ── 즐겨찾기 버튼 삽입 (actions 앞에 prepend) ──
  const actionsEl = infoSection.querySelector('.movie-info__actions');
  await renderFavorite(actionsEl, {
    movieId:      String(movie.id),
    movieTitle:   movie.title,
    moviePost:    movie.poster_path ?? '',
    movieRuntime: movie.runtime ? String(movie.runtime) : '',
  });
  // 버튼을 맨 앞으로 이동
  actionsEl.prepend(actionsEl.querySelector('#btn-favorite'));

  // ── 배우 섹션 (기본 숨김) ──
  const castSection = document.createElement('div');
  castSection.className = 'cast-section';
  castSection.hidden = true;

  const castTitle = document.createElement('p');
  castTitle.className = 'cast-section__title';
  castTitle.textContent = '출연진';
  castSection.appendChild(castTitle);

  const castGrid = document.createElement('div');
  castGrid.className = 'grid-cards';
  castSection.appendChild(castGrid);

  infoSection.appendChild(castSection);

  // 배우 데이터 렌더 (숨겨진 채로 미리 그림)
  const castList = credits?.cast?.slice(0, 20) ?? [];
  if (castList.length) {
    renderGridCards(castGrid, castList, 'actor');
  } else {
    castGrid.innerHTML = '<p style="padding:1rem;color:var(--color-text-muted)">출연진 정보가 없습니다.</p>';
  }

  // ── 배우 토글 이벤트 ──
  let castVisible = false;
  const castToggleBtn = infoSection.querySelector('#btn-cast-toggle');

  castToggleBtn.addEventListener('click', () => {
    castVisible = !castVisible;
    castSection.hidden = !castVisible;
    castToggleBtn.textContent = castVisible ? '👥 배우 숨기기' : '👥 배우 보기';
  });

  // ── Footer ──
  renderFooter(container);
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
