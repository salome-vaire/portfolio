<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$user = require_login();
if (($user['role'] ?? 'user') !== 'admin') {
    header('Location: ../app.php');
    exit;
}

$db = get_db();
$letterId = (int)($_POST['letter_id'] ?? 0);
$action = (string)($_POST['action_type'] ?? '');

if ($letterId > 0) {
    if ($action === 'hide') {
        $stmt = $db->prepare('UPDATE letters SET is_hidden = 1 WHERE id = ?');
        $stmt->execute([$letterId]);
    } elseif ($action === 'show') {
        $stmt = $db->prepare('UPDATE letters SET is_hidden = 0 WHERE id = ?');
        $stmt->execute([$letterId]);
    } elseif ($action === 'delete') {
        $stmt = $db->prepare('DELETE FROM letters WHERE id = ?');
        $stmt->execute([$letterId]);
    }
}

header('Location: index.php');
