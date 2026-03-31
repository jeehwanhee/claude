/**
 * @fileoverview 네비게이션 바 컴포넌트.
 * 인증 상태에 따라 메뉴 항목을 동적으로 전환합니다.
 * 로그아웃 시 기존 nav 요소를 교체하는 방식으로 즉시 업데이트합니다.
 */

import { isLoggedIn, logout } from '../store/auth.js';
import { router } from '../router.js';

/**
 * 로그인 상태에 따른 메뉴 HTML 문자열을 반환합니다.
 * @param {'desktop'|'drawer'} context
 * @returns {string}
 */
function menuItems(context) {
  const itemClass = context === 'desktop' ? 'navbar__item' : 'navbar__drawer-item';

  if (isLoggedIn()) {
    return `<button class="${itemClass} navbar__logout" type="button">로그아웃</button>`;
  }
  return `
    <a href="/login"    data-link class="${itemClass}">로그인</a>
    <a href="/register" data-link class="${itemClass}${context === 'desktop' ? ' navbar__item--highlight' : ''}">회원가입</a>
  `;
}

/**
 * nav DOM 요소를 생성하고 이벤트 리스너를 연결한 뒤 반환합니다.
 * @param {HTMLElement} appEl - 상위 컨테이너 (#app), 로그아웃 시 nav 교체에 사용
 * @returns {HTMLElement}
 */
function buildNav(appEl) {
  const nav = document.createElement('nav');
  nav.className = 'navbar';
  nav.innerHTML = `
    <div class="navbar__inner">
      <a class="navbar__logo" href="/" data-link>🎬 Movie</a>
      <div class="navbar__menu">
        <a href="/" data-link class="navbar__item">홈</a>
        ${menuItems('desktop')}
      </div>
      <button class="navbar__hamburger" id="nav-hamburger" aria-label="메뉴 열기" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>

    <aside class="navbar__drawer" id="nav-drawer" aria-hidden="true">
      <button class="navbar__drawer-close" id="nav-drawer-close" aria-label="메뉴 닫기">✕</button>
      <a href="/" data-link class="navbar__drawer-item">홈</a>
      ${menuItems('drawer')}
    </aside>

    <div class="navbar__overlay" id="nav-overlay"></div>
  `;

  // ── 드로어 ──
  const hamburger = nav.querySelector('#nav-hamburger');
  const drawer    = nav.querySelector('#nav-drawer');
  const overlay   = nav.querySelector('#nav-overlay');
  const closeBtn  = nav.querySelector('#nav-drawer-close');

  const openDrawer = () => {
    drawer.classList.add('open');
    overlay.classList.add('open');
    hamburger.setAttribute('aria-expanded', 'true');
    drawer.setAttribute('aria-hidden', 'false');
  };

  const closeDrawer = () => {
    drawer.classList.remove('open');
    overlay.classList.remove('open');
    hamburger.setAttribute('aria-expanded', 'false');
    drawer.setAttribute('aria-hidden', 'true');
  };

  hamburger.addEventListener('click', openDrawer);
  closeBtn.addEventListener('click', closeDrawer);
  overlay.addEventListener('click', closeDrawer);

  drawer.querySelectorAll('[data-link]').forEach((el) =>
    el.addEventListener('click', closeDrawer)
  );

  // ── 로그아웃 ──
  nav.querySelectorAll('.navbar__logout').forEach((btn) => {
    btn.addEventListener('click', () => {
      logout();
      closeDrawer();

      // 기존 nav 요소를 새로 빌드한 nav(로그인 메뉴)로 즉시 교체
      const existingNav = appEl.querySelector('nav.navbar');
      const freshNav = buildNav(appEl);
      if (existingNav) {
        appEl.replaceChild(freshNav, existingNav);
      }

      router.push('/');
    });
  });

  return nav;
}

/**
 * #app 최상단에 NavBar를 삽입합니다.
 * 이미 nav가 있으면 교체하고, 없으면 prepend합니다.
 * @param {HTMLElement} appEl - #app 요소
 */
export function renderNavBar(appEl) {
  const nav = buildNav(appEl);
  const existing = appEl.querySelector('nav.navbar');

  if (existing) {
    appEl.replaceChild(nav, existing);
  } else {
    appEl.prepend(nav);
  }
}
