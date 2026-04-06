<?php

require_once BASE_PATH . '/models/Video.php';
require_once BASE_PATH . '/config/csrf.php';
require_once BASE_PATH . '/config/helpers.php';

class VideoController {
    private const ALLOWED_EXTENSIONS = ['mp4', 'webm', 'mov'];
    private const ALLOWED_MIMES      = ['video/mp4', 'video/webm', 'video/quicktime'];
    private const MAX_SIZE_BYTES      = 500 * 1024 * 1024; // 500MB
    private const DEFAULT_THUMBNAIL   = 'default_thumbnail.jpg';

    private Video $videoModel;

    public function __construct() {
        $this->videoModel = new Video(getDB());
    }

    // ── 홈 ───────────────────────────────────────────────────────────────────

    public function home(): void {
        $videos = $this->videoModel->getAll();
        require BASE_PATH . '/views/home.php';
    }

    // ── 업로드 ───────────────────────────────────────────────────────────────

    public function upload(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $title       = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $errors      = [];

            if ($title === '') {
                $errors['title'] = '제목을 입력해주세요.';
            }

            if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
                $errors['video'] = $this->uploadErrorMessage($_FILES['video']['error'] ?? UPLOAD_ERR_NO_FILE);
            } else {
                $file = $_FILES['video'];
                $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                    $errors['video'] = '허용되지 않는 확장자입니다. (mp4, webm, mov 만 가능)';
                } else {
                    $finfo    = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($file['tmp_name']);
                    if (!in_array($mimeType, self::ALLOWED_MIMES, true)) {
                        $errors['video'] = '허용되지 않는 파일 형식입니다.';
                    } elseif ($file['size'] > self::MAX_SIZE_BYTES) {
                        $errors['video'] = '파일 크기는 500MB 이하여야 합니다.';
                    }
                }
            }

            if (!empty($errors)) {
                $this->respondUploadError($errors, compact('title', 'description'));
                return;
            }

            // 파일 저장
            $uid       = uniqid('', true);
            $filename  = $uid . '.' . $ext;
            $videoPath = BASE_PATH . '/uploads/videos/' . $filename;

            if (!move_uploaded_file($_FILES['video']['tmp_name'], $videoPath)) {
                $errors['video'] = '파일 저장 중 오류가 발생했습니다.';
                $this->respondUploadError($errors, compact('title', 'description'));
                return;
            }

            $thumbnail = $this->extractThumbnail($videoPath, $uid);

            $videoId = $this->videoModel->create([
                'user_id'     => $_SESSION['user_id'],
                'title'       => $title,
                'description' => $description,
                'filename'    => $filename,
                'thumbnail'   => $thumbnail,
            ]);

            $redirectUrl = '/watch?v=' . $videoId;

            // XHR 요청이면 JSON 응답, 아니면 일반 리다이렉트
            if ($this->isXhr()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['redirect' => $redirectUrl]);
                exit;
            }

            header('Location: ' . $redirectUrl);
            exit;
        }

        $errors = [];
        $old    = [];
        require BASE_PATH . '/views/upload.php';
    }

    // ── 시청 ─────────────────────────────────────────────────────────────────

    public function watch(): void {
        $videoId = filter_input(INPUT_GET, 'v', FILTER_VALIDATE_INT);

        if (!$videoId) {
            $this->render404();
            return;
        }

        $video = $this->videoModel->getById($videoId);

        if (!$video) {
            $this->render404();
            return;
        }

        require_once BASE_PATH . '/models/Comment.php';
        require_once BASE_PATH . '/models/Like.php';
        require_once BASE_PATH . '/models/Subscription.php';

        $db           = getDB();
        $commentModel = new Comment($db);
        $likeModel    = new Like($db);
        $subModel     = new Subscription($db);

        $comments     = $commentModel->getByVideoId($videoId);
        $likeCount    = $likeModel->getCount($videoId);
        $subCount     = $subModel->getCount((int) $video['user_id']);
        $isLiked      = false;
        $isSubscribed = false;

        if (!empty($_SESSION['user_id'])) {
            $uid          = (int) $_SESSION['user_id'];
            $isLiked      = $likeModel->isLiked($uid, $videoId);
            $isSubscribed = $subModel->isSubscribed($uid, (int) $video['user_id']);
        }

        $relatedVideos = $this->videoModel->getRecent(5, $videoId);

        // 조회수 증가는 /api/view_count.php AJAX 에서 처리
        require BASE_PATH . '/views/watch.php';
    }

    // ── private helpers ───────────────────────────────────────────────────────

    private function respondUploadError(array $errors, array $old): void {
        if ($this->isXhr()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['errors' => $errors]);
            exit;
        }
        require BASE_PATH . '/views/upload.php';
    }

    private function isXhr(): bool {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    private function extractThumbnail(string $videoPath, string $uid): string {
        $thumbFilename = $uid . '.jpg';
        $thumbPath     = BASE_PATH . '/uploads/thumbnails/' . $thumbFilename;

        $ffmpeg  = escapeshellarg(FFMPEG_PATH);
        $input   = escapeshellarg($videoPath);
        $output  = escapeshellarg($thumbPath);
        $command = "{$ffmpeg} -i {$input} -ss 00:00:10 -vframes 1 {$output} 2>&1";

        exec($command, $cmdOutput, $returnCode);

        if ($returnCode !== 0 || !file_exists($thumbPath)) {
            error_log('FFmpeg 썸네일 추출 실패: ' . implode("\n", $cmdOutput));
            return self::DEFAULT_THUMBNAIL;
        }

        return $thumbFilename;
    }

    private function uploadErrorMessage(int $code): string {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => '파일 크기가 너무 큽니다.',
            UPLOAD_ERR_PARTIAL                        => '파일이 불완전하게 업로드됐습니다.',
            UPLOAD_ERR_NO_FILE                        => '파일을 선택해주세요.',
            default                                   => '파일 업로드 중 오류가 발생했습니다.',
        };
    }

    private function render404(): void {
        http_response_code(404);
        echo '404 - 영상을 찾을 수 없습니다.';
    }
}
