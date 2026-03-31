/**
 * @fileoverview 푸터 컴포넌트.
 */

/**
 * 푸터를 컨테이너에 추가합니다.
 * @param {HTMLElement} appEl - 삽입 대상 컨테이너 (#app)
 */
export function renderFooter(appEl) {
  const footer = document.createElement('footer');
  footer.className = 'footer';
  footer.innerHTML = `
    <p>🎬 Movie App &mdash; Powered by <a href="https://www.themoviedb.org" target="_blank" rel="noopener" style="color:var(--color-primary)">TMDB</a></p>
  `;
  appEl.appendChild(footer);
}
