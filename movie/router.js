/**
 * @fileoverview SPA 클라이언트 라우터.
 * History API 기반 페이지 전환 + 인증 가드를 관리합니다.
 */

import { isLoggedIn } from './store/auth.js';

/**
 * 라우트 정의.
 * - requiresAuth : true이면 비로그인 시 /login 으로 리다이렉트
 * - redirectIfAuth: true이면 로그인 상태에서 진입 시 / 로 리다이렉트
 */
const routes = [
  {
    pattern: /^\/$/,
    keys: [],
    load: () => import('./pages/LandingPage.js'),
    requiresAuth: false,
    redirectIfAuth: false,
  },
  {
    pattern: /^\/login$/,
    keys: [],
    load: () => import('./pages/LoginPage.js'),
    requiresAuth: false,
    redirectIfAuth: true,
  },
  {
    pattern: /^\/register$/,
    keys: [],
    load: () => import('./pages/RegisterPage.js'),
    requiresAuth: false,
    redirectIfAuth: true,
  },
  {
    pattern: /^\/movie\/([^/]+)$/,
    keys: ['id'],
    load: () => import('./pages/MovieDetailPage.js'),
    requiresAuth: false,
    redirectIfAuth: false,
  },
];

/**
 * 경로를 SPA가 인식할 수 있는 형태로 정규화합니다.
 * @param {string} pathname
 * @returns {string}
 */
function normalizePath(pathname) {
  if (pathname.endsWith('/index.html')) {
    return pathname.slice(0, -'index.html'.length) || '/';
  }
  if (/^\/[A-Za-z]:/.test(pathname) || pathname.includes('\\')) {
    return '/';
  }
  return pathname || '/';
}

/**
 * 경로 문자열을 파싱해 매칭된 라우트와 파라미터를 반환합니다.
 * @param {string} path
 * @returns {{ route: object, params: Object<string, string> } | null}
 */
function matchRoute(path) {
  for (const route of routes) {
    const match = path.match(route.pattern);
    if (match) {
      const params = {};
      route.keys.forEach((key, i) => {
        params[key] = decodeURIComponent(match[i + 1]);
      });
      return { route, params };
    }
  }
  return null;
}

/**
 * #app 컨테이너에 페이지를 렌더링합니다.
 * @param {string} path
 */
async function render(path) {
  const app = document.getElementById('app');

  document.getElementById('initial-loader')?.remove();

  const normalized = normalizePath(path);
  const result = matchRoute(normalized) ?? matchRoute('/');

  // ── 인증 가드 ──
  if (result.route.redirectIfAuth && isLoggedIn()) {
    // 로그인 상태에서 /login, /register 진입 → 홈으로
    router.replace('/');
    return;
  }
  if (result.route.requiresAuth && !isLoggedIn()) {
    // 인증 필요 페이지인데 비로그인 → 로그인으로
    router.replace('/login');
    return;
  }

  try {
    const mod = await result.route.load();
    app.innerHTML = '';
    await mod.default(app, result.params);
  } catch (err) {
    console.error('[Router] 페이지 로드 실패:', err);
    app.innerHTML = `
      <div style="
        display:flex;flex-direction:column;align-items:center;
        justify-content:center;min-height:100vh;
        color:#f5f5f1;font-family:system-ui,sans-serif;gap:1rem;
      ">
        <p style="font-size:1.1rem">⚠️ 페이지를 불러오지 못했습니다.</p>
        <p style="color:#aaa;font-size:.875rem">${err.message ?? err}</p>
        <button onclick="location.reload()" style="
          margin-top:.5rem;padding:.5rem 1.5rem;
          background:#e50914;color:#fff;border:none;
          border-radius:6px;cursor:pointer;font-size:1rem;
        ">새로고침</button>
      </div>
    `;
  }
}

export const router = {
  /**
   * 지정한 경로로 이동합니다. (history 스택에 추가)
   * @param {string} path
   */
  push(path) {
    try { history.pushState(null, '', path); } catch { /* file:// 무시 */ }
    render(path);
  },

  /**
   * 현재 경로를 대체합니다. (history 스택에 추가하지 않음)
   * @param {string} path
   */
  replace(path) {
    try { history.replaceState(null, '', path); } catch { /* file:// 무시 */ }
    render(path);
  },

  /**
   * 라우터를 초기화합니다. 앱 시작 시 한 번 호출합니다.
   */
  init() {
    window.addEventListener('popstate', () => {
      render(location.pathname);
    });

    document.addEventListener('click', (e) => {
      const anchor = e.target.closest('a[data-link]');
      if (!anchor) return;
      e.preventDefault();
      this.push(anchor.getAttribute('href'));
    });

    render(location.pathname);
  },
};
