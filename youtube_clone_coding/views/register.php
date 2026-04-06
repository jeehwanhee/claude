<?php $pageTitle = '회원가입 - YourTube'; require BASE_PATH . '/views/layouts/header.php'; ?>

<main class="auth-wrapper">
    <div class="auth-box">

        <h1>회원가입</h1>

        <form method="POST" action="/?route=register" novalidate>
            <?= csrf_field() ?>

            <!-- 사용자명 -->
            <div class="form-group">
                <label for="username">사용자명</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    class="<?= isset($errors['username']) ? 'is-error' : '' ?>"
                    maxlength="50"
                    autocomplete="username"
                    required
                >
                <?php if (isset($errors['username'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <!-- 이메일 -->
            <div class="form-group">
                <label for="email">이메일</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    class="<?= isset($errors['email']) ? 'is-error' : '' ?>"
                    autocomplete="email"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <!-- 비밀번호 -->
            <div class="form-group">
                <label for="password">비밀번호 <small>(8자 이상)</small></label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="<?= isset($errors['password']) ? 'is-error' : '' ?>"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <!-- 비밀번호 확인 -->
            <div class="form-group">
                <label for="password_confirm">비밀번호 확인</label>
                <input
                    type="password"
                    id="password_confirm"
                    name="password_confirm"
                    class="<?= isset($errors['password_confirm']) ? 'is-error' : '' ?>"
                    autocomplete="new-password"
                    required
                >
                <?php if (isset($errors['password_confirm'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password_confirm'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn btn-primary">회원가입</button>
            </div>
        </form>

        <p class="auth-footer-text">
            이미 계정이 있으신가요? <a href="/?route=login">로그인</a>
        </p>

    </div>
</main>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
