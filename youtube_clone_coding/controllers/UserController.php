<?php

require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Video.php';
require_once BASE_PATH . '/models/Subscription.php';
require_once BASE_PATH . '/config/helpers.php';

class UserController {

    public function profile(): void {
        $targetId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        // id 없으면 로그인한 본인 프로필로 대체, 그것도 없으면 404
        if (!$targetId) {
            if (!empty($_SESSION['user_id'])) {
                $targetId = (int) $_SESSION['user_id'];
            } else {
                $this->render404();
                return;
            }
        }

        $db        = getDB();
        $userModel = new User($db);
        $user      = $userModel->findById($targetId);

        if (!$user) {
            $this->render404();
            return;
        }

        $videoModel = new Video($db);
        $videos     = $videoModel->getByUserId($targetId);

        $subModel   = new Subscription($db);
        $subCount   = $subModel->getCount($targetId);

        // 본인 프로필 여부 (편집 버튼 표시 조건)
        $isOwner = !empty($_SESSION['user_id'])
                && (int) $_SESSION['user_id'] === $targetId;

        $user['gravatar'] = gravatar_url($user['email'], 80);

        require BASE_PATH . '/views/profile.php';
    }

    // ── private ──────────────────────────────────────────────────────────────

    private function render404(): void {
        http_response_code(404);
        echo '404 - 사용자를 찾을 수 없습니다.';
    }
}
