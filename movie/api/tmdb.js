/**
 * @fileoverview TMDB REST API 래퍼
 * 모든 함수는 실패 시 null을 반환하며 콘솔에 에러를 출력합니다.
 */

import { TMDB_API_KEY, TMDB_BASE_URL } from '../config.js';

/**
 * TMDB API 공통 fetch 헬퍼.
 * @param {string} endpoint - 베이스 URL 이후의 경로 (예: '/movie/popular')
 * @param {Object} [params={}] - 추가 쿼리 파라미터
 * @returns {Promise<object|null>} 파싱된 JSON 또는 실패 시 null
 */
async function fetchTMDB(endpoint, params = {}) {
  const url = new URL(`${TMDB_BASE_URL}${endpoint}`);
  url.searchParams.set('api_key', TMDB_API_KEY);
  Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

  try {
    const res = await fetch(url.toString());
    if (!res.ok) {
      throw new Error(`TMDB API 오류: ${res.status} ${res.statusText}`);
    }
    return await res.json();
  } catch (err) {
    console.error('[TMDB]', err);
    return null;
  }
}

/**
 * 인기 영화 목록을 가져옵니다.
 * @param {number} [page=1] - 페이지 번호 (1-based)
 * @returns {Promise<{results: object[], total_pages: number, page: number}|null>}
 */
export async function getPopularMovies(page = 1) {
  return fetchTMDB('/movie/popular', { page, language: 'ko-KR' });
}

/**
 * 특정 영화의 상세 정보를 가져옵니다.
 * @param {string|number} movieId - TMDB 영화 ID
 * @returns {Promise<object|null>} 영화 상세 객체 또는 null
 */
export async function getMovieDetail(movieId) {
  return fetchTMDB(`/movie/${movieId}`, { language: 'ko-KR' });
}

/**
 * 특정 영화의 출연/제작진 정보를 가져옵니다.
 * @param {string|number} movieId - TMDB 영화 ID
 * @returns {Promise<{cast: object[], crew: object[]}|null>}
 */
export async function getMovieCredits(movieId) {
  return fetchTMDB(`/movie/${movieId}/credits`, { language: 'ko-KR' });
}
