/**
 * @fileoverview 회원가입 페이지.
 * blur 이벤트 기반 실시간 유효성 검사, 모든 필드 유효 시 제출 버튼 활성화.
 */

import { renderNavBar } from '../components/navbar.js';
import { renderFooter } from '../components/footer.js';
import { register, isLoggedIn } from '../store/auth.js';
import {
  validateRequired,
  validateEmail,
  validatePassword,
  validatePasswordMatch,
} from '../utils/validator.js';
import { router } from '../router.js';

/**
 * 회원가입 페이지를 렌더링합니다.
 * @param {HTMLElement} container - #app 요소
 * @param {Object} _params - 라우트 파라미터 (사용 안 함)
 */
export default async function RegisterPage(container, _params) {
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
    <h1 class="auth-card__title">회원가입</h1>
    <p class="auth-card__sub">무료 계정을 만들어 시작하세요.</p>

    <form class="auth-form" id="register-form" novalidate>
      <div class="auth-form__row">
        <div class="auth-form__field">
          <label class="auth-form__label" for="name">이름</label>
          <input class="auth-input" type="text" id="name" name="name"
            autocomplete="given-name" placeholder="이름" />
          <span class="error" role="alert" id="name-error"></span>
        </div>
        <div class="auth-form__field">
          <label class="auth-form__label" for="lastname">성</label>
          <input class="auth-input" type="text" id="lastname" name="lastname"
            autocomplete="family-name" placeholder="성" />
          <span class="error" role="alert" id="lastname-error"></span>
        </div>
      </div>

      <div class="auth-form__field">
        <label class="auth-form__label" for="email">이메일</label>
        <input class="auth-input" type="email" id="email" name="email"
          autocomplete="email" placeholder="example@email.com" />
        <span class="error" role="alert" id="email-error"></span>
      </div>

      <div class="auth-form__field">
        <label class="auth-form__label" for="password">비밀번호</label>
        <input class="auth-input" type="password" id="password" name="password"
          autocomplete="new-password" placeholder="6자 이상 입력" />
        <span class="error" role="alert" id="password-error"></span>
      </div>

      <div class="auth-form__field">
        <label class="auth-form__label" for="confirm-password">비밀번호 확인</label>
        <input class="auth-input" type="password" id="confirm-password" name="confirmPassword"
          autocomplete="new-password" placeholder="비밀번호 재입력" />
        <span class="error" role="alert" id="confirm-error"></span>
      </div>

      <span class="auth-form__error" id="form-error" role="alert"></span>

      <button type="submit" class="auth-submit" id="submit-btn" disabled>
        회원가입
      </button>
    </form>

    <p class="auth-card__footer">
      이미 계정이 있으신가요? <a href="/login" data-link>로그인</a>
    </p>
  `;
  main.appendChild(card);

  renderFooter(container);

  // ── DOM 참조 ──
  const form        = card.querySelector('#register-form');
  const nameInput   = card.querySelector('#name');
  const lastInput   = card.querySelector('#lastname');
  const emailInput  = card.querySelector('#email');
  const pwInput     = card.querySelector('#password');
  const cpwInput    = card.querySelector('#confirm-password');
  const nameErr     = card.querySelector('#name-error');
  const lastErr     = card.querySelector('#lastname-error');
  const emailErr    = card.querySelector('#email-error');
  const pwErr       = card.querySelector('#password-error');
  const cpwErr      = card.querySelector('#confirm-error');
  const formErr     = card.querySelector('#form-error');
  const submitBtn   = card.querySelector('#submit-btn');

  // ── 필드별 유효 상태 추적 ──
  const validity = { name: false, lastname: false, email: false, password: false, confirmPassword: false };

  function updateSubmitBtn() {
    submitBtn.disabled = !Object.values(validity).every(Boolean);
  }

  // ── 헬퍼 ──
  function applyField(input, errorEl, key, result) {
    errorEl.textContent = result.valid ? '' : result.message;
    input.classList.toggle('auth-input--error', !result.valid);
    validity[key] = result.valid;
    updateSubmitBtn();
  }

  function showFormError(message) {
    formErr.textContent = message;
    formErr.classList.toggle('visible', !!message);
  }

  // ── blur 유효성 검사 ──
  nameInput.addEventListener('blur', () =>
    applyField(nameInput, nameErr, 'name', validateRequired(nameInput.value))
  );

  lastInput.addEventListener('blur', () =>
    applyField(lastInput, lastErr, 'lastname', validateRequired(lastInput.value))
  );

  emailInput.addEventListener('blur', () =>
    applyField(emailInput, emailErr, 'email', validateEmail(emailInput.value))
  );

  pwInput.addEventListener('blur', () => {
    applyField(pwInput, pwErr, 'password', validatePassword(pwInput.value));
    // 비밀번호 변경 시 확인 필드도 재검사
    if (cpwInput.value) {
      applyField(cpwInput, cpwErr, 'confirmPassword',
        validatePasswordMatch(pwInput.value, cpwInput.value));
    }
  });

  cpwInput.addEventListener('blur', () =>
    applyField(cpwInput, cpwErr, 'confirmPassword',
      validatePasswordMatch(pwInput.value, cpwInput.value))
  );

  // 입력 시 폼 에러 숨기기
  [nameInput, lastInput, emailInput, pwInput, cpwInput].forEach((el) =>
    el.addEventListener('input', () => showFormError(''))
  );

  // ── 폼 제출 ──
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    submitBtn.disabled = true;
    submitBtn.textContent = '처리 중...';

    const image = `https://www.gravatar.com/avatar/${Date.now()}?d=identicon`;

    const result = await register({
      name:     nameInput.value.trim(),
      lastname: lastInput.value.trim(),
      email:    emailInput.value.trim(),
      password: pwInput.value,
      image,
    });

    if (result.success) {
      router.push('/login');
    } else {
      showFormError(result.message);
      submitBtn.disabled = false;
      submitBtn.textContent = '회원가입';
    }
  });
}
