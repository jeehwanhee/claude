<?php

$_cfg = require __DIR__ . '/config.php';

define('DB_HOST',     $_cfg['db_host']);
define('DB_PORT',     $_cfg['db_port'] ?? 3306);
define('DB_NAME',     $_cfg['db_name']);
define('DB_USER',     $_cfg['db_user']);
define('DB_PASS',     $_cfg['db_pass']);
define('DB_CHARSET',  $_cfg['db_charset']);
define('FFMPEG_PATH', $_cfg['ffmpeg_path']);

unset($_cfg);

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('DB Connection failed: ' . $e->getMessage());
            throw new RuntimeException('데이터베이스 연결에 실패했습니다.', 0, $e);
        }
    }

    return $pdo;
}
