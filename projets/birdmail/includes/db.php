<?php
declare(strict_types=1);

function get_db(): PDO {
    static $db = null;

    if ($db === null) {
        $dataDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }

        $dbPath = $dataDir . DIRECTORY_SEPARATOR . 'birdmail.sqlite';
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        init_db($db);
        migrate_db($db);
        seed_demo_data($db);
    }

    return $db;
}

function init_db(PDO $db): void {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            bird_style TEXT NOT NULL DEFAULT 'bluebird',
            cage_style TEXT NOT NULL DEFAULT 'gold',
            theme_style TEXT NOT NULL DEFAULT 'basic',
            music_style TEXT NOT NULL DEFAULT 'breeze',
            role TEXT NOT NULL DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS twofa_codes (
            user_id INTEGER PRIMARY KEY,
            code TEXT NOT NULL,
            expires_at INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id)
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS letters (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender_id INTEGER NOT NULL,
            receiver_id INTEGER NOT NULL,
            subject TEXT NOT NULL,
            encrypted_text TEXT NOT NULL,
            drawing_data TEXT DEFAULT NULL,
            media_type TEXT NOT NULL DEFAULT 'none',
            media_path TEXT DEFAULT NULL,
            external_url TEXT DEFAULT NULL,
            stamp_style TEXT NOT NULL DEFAULT 'heart',
            theme_style TEXT NOT NULL DEFAULT 'basic',
            bird_style TEXT NOT NULL DEFAULT 'bluebird',
            cage_style TEXT NOT NULL DEFAULT 'gold',
            is_hidden INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(sender_id) REFERENCES users(id),
            FOREIGN KEY(receiver_id) REFERENCES users(id)
        )
    ");
}

function column_exists(PDO $db, string $table, string $column): bool {
    $stmt = $db->query("PRAGMA table_info($table)");
    $columns = $stmt->fetchAll();
    foreach ($columns as $info) {
        if (($info['name'] ?? '') === $column) return true;
    }
    return false;
}

function migrate_db(PDO $db): void {
    $userColumns = [
        'theme_style' => "ALTER TABLE users ADD COLUMN theme_style TEXT NOT NULL DEFAULT 'basic'",
        'music_style' => "ALTER TABLE users ADD COLUMN music_style TEXT NOT NULL DEFAULT 'breeze'",
        'role' => "ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT 'user'",
        'bird_style' => "ALTER TABLE users ADD COLUMN bird_style TEXT NOT NULL DEFAULT 'bluebird'",
        'cage_style' => "ALTER TABLE users ADD COLUMN cage_style TEXT NOT NULL DEFAULT 'gold'",
    ];
    foreach ($userColumns as $col => $sql) {
        if (!column_exists($db, 'users', $col)) $db->exec($sql);
    }

    $letterColumns = [
        'media_type' => "ALTER TABLE letters ADD COLUMN media_type TEXT NOT NULL DEFAULT 'none'",
        'media_path' => "ALTER TABLE letters ADD COLUMN media_path TEXT DEFAULT NULL",
        'external_url' => "ALTER TABLE letters ADD COLUMN external_url TEXT DEFAULT NULL",
        'is_hidden' => "ALTER TABLE letters ADD COLUMN is_hidden INTEGER NOT NULL DEFAULT 0",
        'theme_style' => "ALTER TABLE letters ADD COLUMN theme_style TEXT NOT NULL DEFAULT 'basic'",
        'bird_style' => "ALTER TABLE letters ADD COLUMN bird_style TEXT NOT NULL DEFAULT 'bluebird'",
        'cage_style' => "ALTER TABLE letters ADD COLUMN cage_style TEXT NOT NULL DEFAULT 'gold'",
        'drawing_data' => "ALTER TABLE letters ADD COLUMN drawing_data TEXT DEFAULT NULL",
        'stamp_style' => "ALTER TABLE letters ADD COLUMN stamp_style TEXT NOT NULL DEFAULT 'heart'",
    ];
    foreach ($letterColumns as $col => $sql) {
        if (!column_exists($db, 'letters', $col)) $db->exec($sql);
    }
}

function seed_demo_data(PDO $db): void {
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['demo@birdmail.local']);
    if (!$stmt->fetch()) {
        $insert = $db->prepare('
            INSERT INTO users (username, email, password_hash, bird_style, cage_style, theme_style, music_style, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $insert->execute([
            'MeloBird',
            'demo@birdmail.local',
            password_hash('demo1234', PASSWORD_DEFAULT),
            'yellow',
            'cloud',
            'rainbow',
            'dream',
            'user'
        ]);
    }

    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['admin@birdmail.local']);
    if (!$stmt->fetch()) {
        $insert = $db->prepare('
            INSERT INTO users (username, email, password_hash, bird_style, cage_style, theme_style, music_style, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $insert->execute([
            'AdminBird',
            'admin@birdmail.local',
            password_hash('admin1234', PASSWORD_DEFAULT),
            'bluebird',
            'gold',
            'music',
            'garden',
            'admin'
        ]);
    }
}
