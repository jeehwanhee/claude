<?php

class Subscription {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function isSubscribed(int $subscriberId, int $channelId): bool {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM subscriptions WHERE subscriber_id = ? AND channel_id = ? LIMIT 1'
        );
        $stmt->execute([$subscriberId, $channelId]);
        return (bool) $stmt->fetchColumn();
    }

    public function getCount(int $channelId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM subscriptions WHERE channel_id = ?'
        );
        $stmt->execute([$channelId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * 구독 토글. 새 상태(true=구독됨)를 반환
     */
    public function toggle(int $subscriberId, int $channelId): bool {
        if ($this->isSubscribed($subscriberId, $channelId)) {
            $this->db->prepare(
                'DELETE FROM subscriptions WHERE subscriber_id = ? AND channel_id = ?'
            )->execute([$subscriberId, $channelId]);
            return false;
        }

        $this->db->prepare(
            'INSERT INTO subscriptions (subscriber_id, channel_id) VALUES (?, ?)'
        )->execute([$subscriberId, $channelId]);
        return true;
    }
}
