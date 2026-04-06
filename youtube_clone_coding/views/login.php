<?php $pageTitle = '로그인 - YourTube'; require BASE_PATH . '/views/layouts/header.php'; ?>

<main class="auth-wrapper">
    <div class="auth-box">

        <h1>로그인</h1>

        <?php if (isset($errors['form'])): ?>
            <div class="form-alert-error">
                <?= htmlspecialchars($errors['form'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/?route=login" novalidate>
            <?= csrf_field() ?>

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
                <label for="password">비밀번호</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="<?= isset($errors['password']) ? 'is-error' : '' ?>"
                    autocomplete="current-password"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn btn-primary">로그인</button>
            </div>
        </form>

        <p class="auth-footer-text">
            계정이 없으신가요? <a href="/?route=register">회원가입</a>
        </p>

    </div>
</main>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
