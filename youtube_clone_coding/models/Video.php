<?php

class Video {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        $stmt = $this->db->query(
            'SELECT v.*, u.username, u.email
             FROM videos v
             JOIN users u ON v.user_id = u.id
             ORDER BY v.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT v.*, u.username, u.email
             FROM videos v
             JOIN users u ON v.user_id = u.id
             WHERE v.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO videos (user_id, title, description, filename, thumbnail)
             VALUES (:user_id, :title, :description, :filename, :thumbnail)'
        );
        $stmt->execute([
            ':user_id'     => $data['user_id'],
            ':title'       => $data['title'],
            ':description' => $data['description'],
            ':filename'    => $data['filename'],
            ':thumbnail'   => $data['thumbnail'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function incrementViews(int $id): int {
        $this->db->prepare('UPDATE videos SET views = views + 1 WHERE id = ?')->execute([$id]);
        $stmt = $this->db->prepare('SELECT views FROM videos WHERE id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    public function getByUserId(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT v.*, u.username, u.email
             FROM videos v
             JOIN users u ON v.user_id = u.id
             WHERE v.user_id = ?
             ORDER BY v.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getRecent(int $limit = 5, int $excludeId = 0): array {
        $stmt = $this->db->prepare(
            'SELECT v.*, u.username, u.email
             FROM videos v
             JOIN users u ON v.user_id = u.id
             WHERE v.id != ?
             ORDER BY v.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$excludeId, $limit]);
        return $stmt->fetchAll();
    }
}
