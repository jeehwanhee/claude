<?php

require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/config/csrf.php';

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User(getDB());
    }

    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $username        = trim($_POST['username'] ?? '');
            $email           = trim($_POST['email'] ?? '');
            $password        = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $errors          = [];

            // 필드별 유효성 검사
            if (mb_strlen($username) < 2 || mb_strlen($username) > 50) {
                $errors['username'] = '사용자명은 2~50자 사이여야 합니다.';
            } elseif ($this->userModel->findByUsername($username)) {
                $errors['username'] = '이미 사용 중인 사용자명입니다.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = '유효한 이메일 주소를 입력해주세요.';
            } elseif ($this->userModel->findByEmail($email)) {
                $errors['email'] = '이미 사용 중인 이메일입니다.';
            }

            if (strlen($password) < 8) {
                $errors['password'] = '비밀번호는 8자 이상이어야 합니다.';
            } elseif ($password !== $passwordConfirm) {
                $errors['password_confirm'] = '비밀번호가 일치하지 않습니다.';
            }

            if (empty($errors)) {
                $userId = $this->userModel->create($username, $email, $password);
                session_regenerate_id(true);
                $_SESSION['user_id']  = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['email']    = $email;
                header('Location: /');
                exit;
            }

            $this->renderRegister($errors, compact('username', 'email'));
            return;
        }

        $this->renderRegister();
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $errors   = [];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = '유효한 이메일 주소를 입력해주세요.';
            }

            if (empty($errors)) {
                $user = $this->userModel->findByEmail($email);

                if (!$user || !password_verify($password, $user['password'])) {
                    // 어느 필드 문제인지 노출하지 않음 (보안)
                    $errors['form'] = '이메일 또는 비밀번호가 올바르지 않습니다.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email']    = $user['email'];
                    header('Location: /');
                    exit;
                }
            }

            $this->renderLogin($errors, ['email' => $email]);
            return;
        }

        $this->renderLogin();
    }

    public function logout(): void {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        header('Location: /');
        exit;
    }

    // ── private helpers ──────────────────────────────────────────────────────

    private function renderRegister(array $errors = [], array $old = []): void {
        require BASE_PATH . '/views/register.php';
    }

    private function renderLogin(array $errors = [], array $old = []): void {
        require BASE_PATH . '/views/login.php';
    }
}
