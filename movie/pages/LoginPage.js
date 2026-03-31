/**
 * @fileoverview 로그인 페이지.
 * blur 이벤트 기반 실시간 유효성 검사, 이메일 기억하기 기능을 포함합니다.
 */

import { renderNavBar } from '../components/navbar.js';
import { renderFooter } from '../components/footer.js';
import { login, isLoggedIn } from '../store/auth.js';
import { validateEmail, validatePassword } from '../utils/validator.js';
import { router } from '../router.js';

const REMEMBER_KEY = 'movie_remember';

/**
 * 로그인 페이지를 렌더링합니다.
 * @param {HTMLElement} container - #app 요소
 * @param {Object} _params - 라우트 파라미터 (사용 안 함)
 */
export default async function LoginPage(container, _params) {
  // 이미 로그인 상태이면 즉시 홈으로
  if (isLoggedIn()) {
    router.replace('/');
    return;
  }

  renderNavBar(container);

  const main = document.createElement('main');
  main.className = 'main-content auth-page';
  container.appendChild(main);

  // ── 카드 ──
  const card = document.createElement('div');
  card.className = 'auth-card';
  card.innerHTML = `
    <a class="auth-card__logo" href="/" data-link>🎬 Movie</a>
    <h1 class="auth-card__title">로그인</h1>
    <p class="auth-card__sub">계속하려면 로그인하세요.</p>

    <form class="auth-form" id="login-form" novalidate>
      <div class="auth-form__field">
        <label class="auth-form__label" for="email">이메일</label>
        <input class="auth-input" type="email" id="email" name="email"
          autocomplete="email" placeholder="example@email.com" />
        <span class="error" role="alert" id="email-error"></span>
      </div>

      <div class="auth-form__field">
        <label class="auth-form__label" for="password">비밀번호</label>
        <input class="auth-input" type="password" id="password" name="password"
          autocomplete="current-password" placeholder="비밀번호를 입력하세요" />
        <span class="error" role="alert" id="password-error"></span>
      </div>

      <label class="auth-form__check">
        <input type="checkbox" id="remember" />
        이메일 기억하기
      </label>

      <span class="auth-form__error" id="form-error" role="alert"></span>

      <button type="submit" class="auth-submit" id="submit-btn">로그인</button>
    </form>

    <p class="auth-card__footer">
      계정이 없으신가요? <a href="/register" data-link>회원가입</a>
    </p>
  `;
  main.appendChild(card);

  renderFooter(container);

  // ── DOM 참조 ──
  const form        = card.querySelector('#login-form');
  const emailInput  = card.querySelector('#email');
  const pwInput     = card.querySelector('#password');
  const rememberChk = card.querySelector('#remember');
  const emailErr    = card.querySelector('#email-error');
  const pwErr       = card.querySelector('#password-error');
  const formErr     = card.querySelector('#form-error');
  const submitBtn   = card.querySelector('#submit-btn');

  // ── 이메일 기억하기 복원 ──
  const saved = localStorage.getItem(REMEMBER_KEY);
  if (saved) {
    emailInput.value = saved;
    rememberChk.checked = true;
  }

  // ── 헬퍼: 에러 표시 / 초기화 ──
  function showFieldError(input, errorEl, message) {
    errorEl.textContent = message;
    input.classList.toggle('auth-input--error', !!message);
  }

  function showFormError(message) {
    formErr.textContent = message;
    formErr.classList.toggle('visible', !!message);
  }

  // ── blur 유효성 검사 ──
  emailInput.addEventListener('blur', () => {
    const { valid, message } = validateEmail(emailInput.value);
    showFieldError(emailInput, emailErr, valid ? '' : message);
  });

  pwInput.addEventListener('blur', () => {
    const { valid, message } = validatePassword(pwInput.value);
    showFieldError(pwInput, pwErr, valid ? '' : message);
  });

  // 입력 시 폼 에러 숨기기
  [emailInput, pwInput].forEach((el) =>
    el.addEventListener('input', () => showFormError(''))
  );

  // ── 폼 제출 ──
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email    = emailInput.value.trim();
    const password = pwInput.value;

    // 제출 시 전체 유효성 재검사
    const emailResult = validateEmail(email);
    const pwResult    = validatePassword(password);

    showFieldError(emailInput, emailErr, emailResult.valid ? '' : emailResult.message);
    showFieldError(pwInput,    pwErr,    pwResult.valid    ? '' : pwResult.message);

    if (!emailResult.valid || !pwResult.valid) return;

    // 이메일 기억하기 처리
    if (rememberChk.checked) {
      localStorage.setItem(REMEMBER_KEY, email);
    } else {
      localStorage.removeItem(REMEMBER_KEY);
    }

    submitBtn.disabled = true;
    submitBtn.textContent = '로그인 중...';

    const result = await login(email, password);

    if (result.success) {
      router.push('/');
    } else {
      showFormError(result.message);
      submitBtn.disabled = false;
      submitBtn.textContent = '로그인';
    }
  });
}
