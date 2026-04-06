<?php

class Comment {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * 영상의 댓글을 계층 구조로 반환 (최대 2단계)
     *
     * 반환 구조:
     * [
     *   [
     *     "id" => 1, "content" => "...", "username" => "...",
     *     "gravatar" => "...", "created_at" => "...",
     *     "replies" => [
     *       ["id" => 3, "content" => "...", "parent_id" => 1, ...],
     *       ...
     *     ]
     *   ],
     *   ...
     * ]
     */
    public function getByVideoId(int $videoId): array {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.video_id, c.user_id, c.parent_id,
                    c.content, c.created_at,
                    u.username, u.email
             FROM comments c
             JOIN users u ON c.user_id = u.id
             WHERE c.video_id = ?
             ORDER BY c.created_at ASC'
        );
        $stmt->execute([$videoId]);
        $all = $stmt->fetchAll();

        // id → row 인덱싱 + gravatar 추가
        $indexed = [];
        foreach ($all as $row) {
            $row['gravatar'] = self::gravatarUrl($row['email']);
            $row['replies']  = [];
            $indexed[$row['id']] = $row;
        }

        // parent_id가 NULL인 댓글이 최상위, 나머지는 replies 배열에 배치
        $topLevel = [];
        foreach ($indexed as $id => $row) {
            if ($row['parent_id'] === null) {
                $topLevel[$id] = $row;
            }
        }
        foreach ($indexed as $row) {
            $pid = $row['parent_id'];
            if ($pid !== null && isset($topLevel[$pid])) {
                $topLevel[$pid]['replies'][] = $row;
            }
        }

        return array_values($topLevel);
    }

    /**
     * 댓글 INSERT 후 생성된 id 반환
     */
    public function create(
        int     $videoId,
        int     $userId,
        string  $content,
        ?int    $parentId = null
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (video_id, user_id, parent_id, content)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$videoId, $userId, $parentId, $content]);
        return (int) $this->db->lastInsertId();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.video_id, c.user_id, c.parent_id,
                    c.content, c.created_at,
                    u.username, u.email
             FROM comments c
             JOIN users u ON c.user_id = u.id
             WHERE c.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $row['gravatar'] = self::gravatarUrl($row['email']);
        return $row;
    }

    // ── private ──────────────────────────────────────────────────────────────

    private static function gravatarUrl(string $email, int $size = 40): string {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
    }
}
