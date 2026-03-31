/**
 * @fileoverview 앱 진입점.
 * 라우터를 초기화하고 전역 네비게이션 링크를 클릭할 수 있도록 설정합니다.
 * TMDB API 키가 기본값인 경우 콘솔에 경고를 출력합니다.
 */

import { router } from './router.js';
import { TMDB_API_KEY } from './config.js';

// API 키 미설정 경고
if (TMDB_API_KEY === 'YOUR_KEY_HERE') {
  console.warn(
    '[Movie App] config.js의 TMDB_API_KEY를 실제 API 키로 교체하세요.\n' +
    '발급: https://www.themoviedb.org/settings/api'
  );
}

// 라우터 초기화 (popstate 리스너 등록 + 현재 경로 렌더링)
router.init();

// 개발 편의: 콘솔에서 router 전역 접근 가능
window.router = router;
