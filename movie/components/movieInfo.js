/**
 * @fileoverview 영화 메타정보 컴포넌트.
 * <dl><dt><dd> 구조로 영화의 주요 정보를 그리드 형태로 표시합니다.
 */

/**
 * XSS 방지를 위해 HTML 특수문자를 이스케이프합니다.
 * @param {string} str
 * @returns {string}
 */
function esc(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

/**
 * 수익 값을 달러 통화 문자열로 포맷합니다.
 * revenue === 0 또는 falsy 이면 '정보 없음'을 반환합니다.
 * @param {number} value
 * @returns {string}
 */
function formatRevenue(value) {
  if (!value) return '정보 없음';
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
  }).format(value);
}

/**
 * 날짜 문자열을 한국어 형식으로 포맷합니다.
 * @param {string} dateStr
 * @returns {string}
 */
function formatDate(dateStr) {
  if (!dateStr) return '정보 없음';
  return new Date(dateStr).toLocaleDateString('ko-KR', {
    year: 'numeric', month: 'long', day: 'numeric',
  });
}

/**
 * 영화 메타정보 <dl> 을 컨테이너에 삽입합니다.
 * @param {HTMLElement} containerEl - 삽입 대상 부모 요소
 * @param {object} detail - TMDB 영화 상세 객체
 */
export function renderMovieInfo(containerEl, detail) {
  const items = [
    {
      term: '제목',
      def: detail.title ?? '정보 없음',
    },
    {
      term: '개봉일',
      def: formatDate(detail.release_date),
    },
    {
      term: '수익',
      def: formatRevenue(detail.revenue),
    },
    {
      term: '런타임',
      def: detail.runtime ? `${detail.runtime}분` : '정보 없음',
    },
    {
      term: '평점',
      def: detail.vote_average ? `${Number(detail.vote_average).toFixed(1)} / 10` : '정보 없음',
      highlight: !!detail.vote_average,
    },
    {
      term: '투표수',
      def: detail.vote_count
        ? detail.vote_count.toLocaleString('ko-KR') + '표'
        : '정보 없음',
    },
    {
      term: '상태',
      def: detail.status ?? '정보 없음',
    },
    {
      term: '인기도',
      def: detail.popularity ? Number(detail.popularity).toFixed(1) : '정보 없음',
    },
  ];

  const dl = document.createElement('dl');
  dl.className = 'movie-info-dl';

  items.forEach(({ term, def, highlight = false }) => {
    const item = document.createElement('div');
    item.className = 'movie-info-dl__item';
    item.innerHTML = `
      <dt class="movie-info-dl__term">${esc(term)}</dt>
      <dd class="movie-info-dl__def${highlight ? ' movie-info-dl__def--highlight' : ''}">${esc(def)}</dd>
    `;
    dl.appendChild(item);
  });

  containerEl.appendChild(dl);
}
