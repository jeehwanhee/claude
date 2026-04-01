/**
 * @fileoverview 통합 API 모듈.
 * PHP 백엔드 API 함수와 TMDB API 함수를 하나의 모듈로 관리합니다.
 */

// ── PHP API ──────────────────────────────────────────────

const PHP_BASE = '/movie-web/api';

/**
 * PHP API GET 헬퍼. 실패 시 null 반환.
 * @param {string} endpoint
 * @returns {Promise<object|null>}
 */
async function get(endpoint) {
  try {
    const res = await fetch(`${PHP_BASE}${endpoint}`, { credentials: 'include' });
    return await res.json();
  } catch (err) {
    console.error('[API GET]', endpoint, err);
    return null;
  }
}

/**
 * PHP API POST 헬퍼. 실패 시 null 반환.
 * @param {string} endpoint
 * @param {object} body
 * @returns {Promise<object|null>}
 */
async function post(endpoint, body) {
  try {
    const res = await fetch(`${PHP_BASE}${endpoint}`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    return await res.json();
  } catch (err) {
    console.error('[API POST]', endpoint, err);
    return null;
  }
}

/** 회원가입 */
export async function phpRegister(userData) {
  return post('/users/register.php', userData);
}

/** 로그인 */
export async function phpLogin(email, password) {
  return post('/users/login.php', { email, password });
}

/** 로그아웃 */
export async function phpLogout() {
  return post('/users/logout.php', {});
}

/** 현재 세션 인증 정보 조회 */
export async function phpGetAuth() {
  return get('/users/auth.php');
}

/** 특정 영화의 즐겨찾기 수 조회 */
export async function phpFavNumber(movieId) {
  return post('/favorite/number.php', { movieId });
}

/** 특정 유저의 즐겨찾기 여부 조회 */
export async function phpFavorited(movieId, userId) {
  return post('/favorite/favorited.php', { movieId, userId });
}

/** 즐겨찾기 추가 */
export async function phpAddFav(movieId, movieTitle, moviePoster, movieRuntime) {
  return post('/favorite/add.php', { movieId, movieTitle, moviePoster, movieRuntime });
}

/** 즐겨찾기 제거 */
export async function phpRemoveFav(movieId) {
  return post('/favorite/remove.php', { movieId });
}

// ── TMDB API ─────────────────────────────────────────────

export { getPopularMovies, getMovieDetail, getMovieCredits } from './api/tmdb.js';
