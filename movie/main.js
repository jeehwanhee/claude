/**
 * @fileoverview 앱 진입점.
 * PHP Session으로 인증 상태를 확인한 뒤 라우터를 초기화합니다.
 */

import { router } from './router.js';
import { TMDB_API_KEY } from './config.js';
import { phpGetAuth } from './api.js';

// API 키 미설정 경고
if (TMDB_API_KEY === 'YOUR_KEY_HERE') {
  console.warn(
    '[Movie App] config.js의 TMDB_API_KEY를 실제 API 키로 교체하세요.\n' +
    '발급: https://www.themoviedb.org/settings/api'
  );
}

// 앱 시작 시 PHP Session으로 인증 상태 확인
const auth = await phpGetAuth();
window.__user = auth?.isAuth ? auth : null;

// 라우터 초기화 (popstate 리스너 등록 + 현재 경로 렌더링)
router.init();

// 개발 편의: 콘솔에서 router 전역 접근 가능
window.router = router;
