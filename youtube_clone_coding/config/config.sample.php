<?php
// 이 파일을 config.php로 복사한 뒤 실제 값을 입력하세요.
// config.php는 절대 Git에 커밋하지 마세요 (.gitignore에 포함됨)

return [
    // DB
    'db_host'    => 'localhost',
    'db_name'    => 'yourtube',
    'db_user'    => 'root',
    'db_pass'    => '',
    'db_charset' => 'utf8mb4',

    // FFmpeg 실행 파일 경로
    // Windows 예: 'C:/ffmpeg/bin/ffmpeg.exe'
    // Linux/Mac 예: '/usr/bin/ffmpeg'
    'ffmpeg_path' => 'C:/ffmpeg/bin/ffmpeg.exe',
];
