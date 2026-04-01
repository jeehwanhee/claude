/**
 * @fileoverview 랜딩 페이지 — 인기 영화 목록.
 * NavBar → 히어로 배너 → 영화 그리드 → 더보기 버튼 → Footer 순으로 렌더링합니다.
 */

import { renderNavBar } from '../components/navbar.js';
import { renderMainImage } from '../components/mainImage.js';
import { renderGridCards } from '../components/gridCards.js';
import { renderFooter } from '../components/footer.js';
import { getPopularMovies } from '../api/tmdb.js';

/** 현재 페이지 번호 (더보기 시 증가) */
let currentPage = 1;
/** 마지막 페이지 번호 */
let totalPages = 1;

/**
 * 랜딩 페이지를 렌더링합니다.
 * @param {HTMLElement} container - #app 요소
 * @param {Object} _params - 라우트 파라미터 (사용 안 함)
 */
export default async function LandingPage(container, _params) {
  // 페이지 상태 초기화
  currentPage = 1;
  totalPages = 1;

  // ── NavBar ──
  renderNavBar(container);

  // ── 메인 콘텐츠 래퍼 ──
  const main = document.createElement('main');
  main.className = 'main-content';
  container.appendChild(main);

  // ── 로딩 스피너 ──
  const spinnerWrap = document.createElement('div');
  spinnerWrap.className = 'spinner-wrap';
  spinnerWrap.setAttribute('role', 'status');
  spinnerWrap.setAttribute('aria-live', 'polite');
  spinnerWrap.setAttribute('aria-label', '영화 목록을 불러오는 중');
  spinnerWrap.innerHTML = '<div class="spinner"></div>';
  main.appendChild(spinnerWrap);

  // ── 데이터 패치 ──
  let data = null;
  try {
    data = await getPopularMovies(1);
  } finally {
    spinnerWrap.remove();
  }

  if (!data || !data.results?.length) {
    main.innerHTML = `
      <p role="alert" style="text-align:center;padding:4rem;color:var(--color-text-muted)">
        영화 목록을 불러올 수 없습니다.<br>
        <small>config.js의 TMDB_API_KEY를 확인하세요.</small>
      </p>`;
    renderFooter(container);
    return;
  }

  totalPages = data.total_pages;

  // ── 히어로 배너 (첫 번째 영화) ──
  const firstMovie = data.results[0];
  main.appendChild(
    renderMainImage({
      backdropPath: firstMovie.backdrop_path,
      title: firstMovie.title,
      overview: firstMovie.overview,
    })
  );

  // ── 섹션 헤더 ──
  const section = document.createElement('section');
  section.className = 'landing';
  section.innerHTML = `
    <div class="landing__header">
      <h2 class="landing__title">인기 영화</h2>
    </div>
  `;
  main.appendChild(section);

  // ── 영화 그리드 ──
  const grid = document.createElement('div');
  grid.className = 'grid-cards';
  grid.id = 'movie-grid';
  section.appendChild(grid);

  renderGridCards(grid, data.results, 'movie');

  // ── 더보기 버튼 ──
  const moreWrap = document.createElement('div');
  moreWrap.className = 'landing__more';

  const loadMoreBtn = document.createElement('button');
  loadMoreBtn.className = 'btn-load-more';
  loadMoreBtn.textContent = '더보기';
  if (currentPage >= totalPages) loadMoreBtn.disabled = true;

  loadMoreBtn.addEventListener('click', async () => {
    loadMoreBtn.disabled = true;
    loadMoreBtn.textContent = '불러오는 중...';

    currentPage++;
    const moreData = await getPopularMovies(currentPage);

    if (moreData?.results?.length) {
      renderGridCards(grid, moreData.results, 'movie', true);
      totalPages = moreData.total_pages;

      if (currentPage >= totalPages) {
        loadMoreBtn.textContent = '마지막 페이지입니다';
      } else {
        loadMoreBtn.textContent = '더보기';
        loadMoreBtn.disabled = false;
      }
    } else {
      // API 실패 시 페이지 번호 원복 후 버튼 재활성화
      currentPage--;
      loadMoreBtn.textContent = '더보기 (재시도)';
      loadMoreBtn.disabled = false;
    }
  });

  moreWrap.appendChild(loadMoreBtn);
  section.appendChild(moreWrap);

  // ── Footer ──
  renderFooter(container);
}
