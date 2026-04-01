/**
 * @fileoverview 즐겨찾기 스토어.
 * PHP 백엔드 API를 통해 즐겨찾기 데이터를 관리합니다.
 */

import { phpFavNumber, phpFavorited, phpAddFav, phpRemoveFav } from '../api.js';

/**
 * 특정 영화의 즐겨찾기 수를 반환합니다.
 * @param {string|number} movieId
 * @returns {Promise<number>}
 */
export async function getFavoriteCount(movieId) {
  const res = await phpFavNumber(movieId);
  return res?.favoriteNumber ?? 0;
}

/**
 * 특정 사용자가 해당 영화를 즐겨찾기 했는지 확인합니다.
 * @param {string|number} userId
 * @param {string|number} movieId
 * @returns {Promise<boolean>}
 */
export async function isFavorited(userId, movieId) {
  const res = await phpFavorited(movieId, userId);
  return res?.favorited ?? false;
}

/**
 * 즐겨찾기에 영화를 추가합니다.
 * moviePost 필드명은 API 전송 시 moviePoster로 매핑됩니다.
 * @param {string|number} userId
 * @param {{ movieId: string|number, movieTitle: string, moviePost: string, movieRuntime: string }} movieData
 * @returns {Promise<object|null>}
 */
export async function addFavorite(userId, { movieId, movieTitle, moviePost, movieRuntime }) {
  return phpAddFav(String(movieId), movieTitle, moviePost ?? '', movieRuntime ?? '');
}

/**
 * 즐겨찾기에서 영화를 제거합니다.
 * @param {string|number} userId
 * @param {string|number} movieId
 * @returns {Promise<object|null>}
 */
export async function removeFavorite(userId, movieId) {
  return phpRemoveFav(String(movieId));
}
