/**
 * @fileoverview 사용자 인증 스토어.
 * PHP Session 기반 인증을 사용합니다.
 * 현재 사용자 정보는 window.__user에 저장됩니다.
 */

import { phpRegister, phpLogin, phpLogout, phpGetAuth } from '../api.js';

/**
 * 신규 사용자를 등록합니다.
 * @param {{ name: string, lastname: string, email: string, password: string, image: string }} userData
 * @returns {Promise<{ success: boolean, message?: string }>}
 */
export async function register(userData) {
  const result = await phpRegister(userData);
  return result ?? { success: false, message: '서버 오류가 발생했습니다.' };
}

/**
 * 이메일과 비밀번호로 로그인합니다.
 * 성공 시 phpGetAuth로 window.__user를 갱신합니다.
 * @param {string} email
 * @param {string} password
 * @returns {Promise<{ success: boolean, message?: string }>}
 */
export async function login(email, password) {
  const result = await phpLogin(email, password);
  if (result?.success) {
    const auth = await phpGetAuth();
    window.__user = auth?.isAuth ? auth : null;
  }
  return result ?? { success: false, message: '서버 오류가 발생했습니다.' };
}

/**
 * 로그아웃합니다. window.__user를 null로 초기화합니다.
 */
export async function logout() {
  await phpLogout();
  window.__user = null;
}

/**
 * 현재 로그인 여부를 반환합니다.
 * @returns {boolean}
 */
export function isLoggedIn() {
  return !!window.__user;
}

/**
 * 현재 로그인된 사용자 객체를 반환합니다.
 * @returns {object|null}
 */
export function currentUser() {
  return window.__user ?? null;
}
