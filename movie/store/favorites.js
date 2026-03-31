/**
 * @fileoverview 즐겨찾기 스토어.
 * 사용자별 즐겨찾기 목록을 localStorage에 저장합니다.
 * 키 패턴: 'movie_fav_{userId}'
 */

/**
 * 특정 사용자의 즐겨찾기 localStorage 키를 반환합니다.
 * @param {string} userId
 * @returns {string}
 */
function favKey(userId) {
  return `movie_fav_${userId}`;
}

/**
 * 특정 사용자의 즐겨찾기 배열을 localStorage에서 읽어옵니다.
 * @param {string} userId
 * @returns {object[]}
 */
function loadFavorites(userId) {
  try {
    return JSON.parse(localStorage.getItem(favKey(userId)) ?? '[]');
  } catch {
    return [];
  }
}

/**
 * 특정 사용자의 즐겨찾기 배열을 localStorage에 저장합니다.
 * @param {string} userId
 * @param {object[]} favorites
 */
function saveFavorites(userId, favorites) {
  localStorage.setItem(favKey(userId), JSON.stringify(favorites));
}

/**
 * 즐겨찾기에 영화를 추가합니다. 이미 추가된 경우 아무 동작도 하지 않습니다.
 * @param {string} userId - 현재 사용자 ID
 * @param {{ movieId: string|number, movieTitle: string, moviePost: string, movieRuntime: string }} movie
 */
export function addFavorite(userId, { movieId, movieTitle, moviePost, movieRuntime }) {
  const favorites = loadFavorites(userId);
  const alreadyAdded = favorites.some((f) => String(f.movieId) === String(movieId));
  if (alreadyAdded) return;

  favorites.push({
    movieId: String(movieId),
    movieTitle,
    moviePost,
    movieRuntime,
    addedAt: Date.now(),
  });
  saveFavorites(userId, favorites);
}

/**
 * 즐겨찾기에서 영화를 제거합니다.
 * @param {string} userId - 현재 사용자 ID
 * @param {string|number} movieId - 제거할 영화 ID
 */
export function removeFavorite(userId, movieId) {
  const favorites = loadFavorites(userId);
  const updated = favorites.filter((f) => String(f.movieId) !== String(movieId));
  saveFavorites(userId, updated);
}

/**
 * 특정 사용자가 해당 영화를 즐겨찾기 했는지 확인합니다.
 * @param {string} userId
 * @param {string|number} movieId
 * @returns {boolean}
 */
export function isFavorited(userId, movieId) {
  const favorites = loadFavorites(userId);
  return favorites.some((f) => String(f.movieId) === String(movieId));
}

/**
 * 모든 사용자를 기준으로 특정 영화의 즐겨찾기 수를 반환합니다.
 * localStorage에서 'movie_fav_' 접두사를 가진 모든 키를 탐색합니다.
 * @param {string|number} movieId
 * @returns {number}
 */
export function getFavoriteCount(movieId) {
  let count = 0;
  for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);
    if (!key || !key.startsWith('movie_fav_')) continue;

    try {
      const favorites = JSON.parse(localStorage.getItem(key) ?? '[]');
      if (favorites.some((f) => String(f.movieId) === String(movieId))) {
        count++;
      }
    } catch {
      // 파싱 실패한 키는 무시
    }
  }
  return count;
}
