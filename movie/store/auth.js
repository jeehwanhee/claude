/**
 * @fileoverview 사용자 인증 스토어.
 * 사용자 목록은 localStorage 'movie_users'에,
 * 현재 세션은 sessionStorage 'movie_session'에 저장합니다.
 */

import { hashPassword } from '../utils/crypto.js';

/** localStorage / sessionStorage 키 상수 */
const USERS_KEY = 'movie_users';
const SESSION_KEY = 'movie_session';

/** 세션 유효 시간: 1시간(ms) */
const SESSION_TTL = 3_600_000;

/**
 * localStorage에서 전체 사용자 배열을 읽어 반환합니다.
 * @returns {object[]}
 */
function loadUsers() {
  try {
    return JSON.parse(localStorage.getItem(USERS_KEY) ?? '[]');
  } catch {
    return [];
  }
}

/**
 * 전체 사용자 배열을 localStorage에 저장합니다.
 * @param {object[]} users
 */
function saveUsers(users) {
  localStorage.setItem(USERS_KEY, JSON.stringify(users));
}

/**
 * 신규 사용자를 등록합니다.
 * @param {{ name: string, lastname: string, email: string, password: string }} userData
 * @returns {Promise<{ success: boolean, message: string }>}
 */
export async function register(userData) {
  const users = loadUsers();
  const duplicate = users.find((u) => u.email === userData.email);
  if (duplicate) {
    return { success: false, message: '이미 사용 중인 이메일입니다.' };
  }

  const hashedPassword = await hashPassword(userData.password);
  const newUser = {
    userId: crypto.randomUUID(),
    name: userData.name,
    lastname: userData.lastname,
    email: userData.email,
    password: hashedPassword,
    role: 0,
    createdAt: Date.now(),
  };

  saveUsers([...users, newUser]);
  return { success: true, message: '회원가입이 완료되었습니다.' };
}

/**
 * 이메일과 비밀번호로 로그인합니다.
 * 성공 시 sessionStorage에 세션을 저장합니다.
 * @param {string} email
 * @param {string} password
 * @returns {Promise<{ success: boolean, message: string }>}
 */
export async function login(email, password) {
  const users = loadUsers();
  const user = users.find((u) => u.email === email);
  if (!user) {
    return { success: false, message: '이메일 또는 비밀번호가 올바르지 않습니다.' };
  }

  const hashedInput = await hashPassword(password);
  if (hashedInput !== user.password) {
    return { success: false, message: '이메일 또는 비밀번호가 올바르지 않습니다.' };
  }

  const session = {
    userId: user.userId,
    email: user.email,
    name: user.name,
    lastname: user.lastname,
    role: user.role,
    expiresAt: Date.now() + SESSION_TTL,
  };
  sessionStorage.setItem(SESSION_KEY, JSON.stringify(session));
  return { success: true, message: '로그인되었습니다.' };
}

/**
 * 현재 세션을 삭제(로그아웃)합니다.
 */
export function logout() {
  sessionStorage.removeItem(SESSION_KEY);
}

/**
 * 유효한 세션이 존재하는지 확인합니다.
 * @returns {boolean}
 */
export function isLoggedIn() {
  const session = currentUser();
  return session !== null;
}

/**
 * 현재 로그인된 사용자 세션 객체를 반환합니다.
 * 세션이 없거나 만료된 경우 null을 반환하고 세션을 삭제합니다.
 * @returns {{ userId: string, email: string, name: string, lastname: string, role: number, expiresAt: number } | null}
 */
export function currentUser() {
  try {
    const raw = sessionStorage.getItem(SESSION_KEY);
    if (!raw) return null;

    const session = JSON.parse(raw);
    if (Date.now() > session.expiresAt) {
      sessionStorage.removeItem(SESSION_KEY);
      return null;
    }
    return session;
  } catch {
    return null;
  }
}
