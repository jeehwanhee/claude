<?php

require_once BASE_PATH . '/models/Comment.php';

class CommentController {
    private Comment $commentModel;

    public function __construct() {
        $this->commentModel = new Comment(getDB());
    }

    /**
     * 영상의 계층형 댓글 배열 반환.
     * watch.php에서 include 후 바로 호출 가능:
     *
     *   require_once BASE_PATH . '/controllers/CommentController.php';
     *   $comments = (new CommentController())->getComments($videoId);
     */
    public function getComments(int $videoId): array {
        return $this->commentModel->getByVideoId($videoId);
    }
}
