/**
 * @fileoverview 폼 유효성 검사 유틸리티.
 * 각 함수는 { valid: boolean, message: string } 객체를 반환합니다.
 */

/**
 * 이메일 형식을 검사합니다.
 * @param {string} value - 검사할 이메일 문자열
 * @returns {{ valid: boolean, message: string }}
 */
export function validateEmail(value) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!re.test(String(value).trim())) {
    return { valid: false, message: '올바른 이메일 형식이 아닙니다.' };
  }
  return { valid: true, message: '' };
}

/**
 * 비밀번호 최소 길이(6자)를 검사합니다.
 * @param {string} value - 검사할 비밀번호 문자열
 * @returns {{ valid: boolean, message: string }}
 */
export function validatePassword(value) {
  if (!value || value.length < 6) {
    return { valid: false, message: '비밀번호는 최소 6자 이상이어야 합니다.' };
  }
  return { valid: true, message: '' };
}

/**
 * 두 비밀번호가 일치하는지 검사합니다.
 * @param {string} a - 비밀번호
 * @param {string} b - 비밀번호 확인
 * @returns {{ valid: boolean, message: string }}
 */
export function validatePasswordMatch(a, b) {
  if (a !== b) {
    return { valid: false, message: '비밀번호가 일치하지 않습니다.' };
  }
  return { valid: true, message: '' };
}

/**
 * 필수 입력값(공백 제거 후 빈 문자열 여부)을 검사합니다.
 * @param {string} value - 검사할 문자열
 * @returns {{ valid: boolean, message: string }}
 */
export function validateRequired(value) {
  if (!value || String(value).trim() === '') {
    return { valid: false, message: '필수 입력 항목입니다.' };
  }
  return { valid: true, message: '' };
}
