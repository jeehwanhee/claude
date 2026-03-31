/**
 * @fileoverview Web Crypto API 기반 암호화 유틸리티.
 * SubtleCrypto를 사용하므로 HTTPS 또는 localhost 환경에서만 동작합니다.
 */

/**
 * 평문 문자열을 SHA-256으로 해싱해 소문자 hex 문자열로 반환합니다.
 * @param {string} plainText - 해싱할 원본 문자열
 * @returns {Promise<string>} 64자리 소문자 hex 문자열
 * @example
 * const hash = await hashPassword('mySecret');
 * // '7c222fb2927d828af22f592134e8932480637c0d...'
 */
export async function hashPassword(plainText) {
  const encoder = new TextEncoder();
  const data = encoder.encode(plainText);
  const hashBuffer = await crypto.subtle.digest('SHA-256', data);
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  return hashArray.map((b) => b.toString(16).padStart(2, '0')).join('');
}
