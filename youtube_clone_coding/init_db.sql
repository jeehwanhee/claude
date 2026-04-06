CREATE DATABASE IF NOT EXISTS yourtube
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE yourtube;

-- ── users ────────────────────────────────────────────────────────────────────
CREATE TABLE users (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  UNIQUE NOT NULL,
    email      VARCHAR(100) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── videos ───────────────────────────────────────────────────────────────────
CREATE TABLE videos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    filename    VARCHAR(255) NOT NULL,
    thumbnail   VARCHAR(255) NOT NULL,
    views       INT UNSIGNED DEFAULT 0,          -- UNSIGNED: 음수 방지
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,                       -- 유저 삭제 시 영상도 삭제

    INDEX idx_videos_user_id    (user_id),
    INDEX idx_videos_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── comments ─────────────────────────────────────────────────────────────────
CREATE TABLE comments (
    id         INT      AUTO_INCREMENT PRIMARY KEY,
    video_id   INT UNSIGNED NOT NULL,
    user_id    INT          NOT NULL,
    parent_id  INT          DEFAULT NULL,
    content    TEXT         NOT NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,

    -- SELF REFERENCING FK: parent_id → comments(id)
    -- SET NULL 처리로 부모 삭제 시 대댓글이 최상위로 승격
    FOREIGN KEY (parent_id) REFERENCES comments(id)
        ON DELETE SET NULL,

    FOREIGN KEY (video_id) REFERENCES videos(id)
        ON DELETE CASCADE,                       -- 영상 삭제 시 댓글도 삭제

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,                       -- 유저 삭제 시 댓글도 삭제

    INDEX idx_comments_video_id  (video_id),
    INDEX idx_comments_parent_id (parent_id),
    INDEX idx_comments_user_id   (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── likes ────────────────────────────────────────────────────────────────────
CREATE TABLE likes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    video_id   INT UNSIGNED NOT NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_like (user_id, video_id), -- 중복 좋아요 방지

    FOREIGN KEY (user_id)  REFERENCES users(id)
        ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id)
        ON DELETE CASCADE,

    INDEX idx_likes_video_id (video_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── subscriptions ────────────────────────────────────────────────────────────
CREATE TABLE subscriptions (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscriber_id INT          NOT NULL,
    channel_id    INT          NOT NULL,
    created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_sub (subscriber_id, channel_id), -- 중복 구독 방지

    FOREIGN KEY (subscriber_id) REFERENCES users(id)
        ON DELETE CASCADE,
    FOREIGN KEY (channel_id)    REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_subscriptions_channel_id (channel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
