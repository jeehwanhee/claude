<?php

class Like {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function isLiked(int $userId, int $videoId): bool {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM likes WHERE user_id = ? AND video_id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $videoId]);
        return (bool) $stmt->fetchColumn();
    }

    public function getCount(int $videoId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE video_id = ?');
        $stmt->execute([$videoId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * 좋아요 토글. 새 상태(true=좋아요됨)를 반환
     */
    public function toggle(int $userId, int $videoId): bool {
        if ($this->isLiked($userId, $videoId)) {
            $this->db->prepare('DELETE FROM likes WHERE user_id = ? AND video_id = ?')
                     ->execute([$userId, $videoId]);
            return false;
        }

        $this->db->prepare('INSERT INTO likes (user_id, video_id) VALUES (?, ?)')
                 ->execute([$userId, $videoId]);
        return true;
    }
}
