<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$user = require_login();
$db = get_db();

$stmt = $db->prepare('SELECT id, username, bird_style, cage_style, theme_style FROM users WHERE id != ? ORDER BY username');
$stmt->execute([$user['id']]);

echo json_encode($stmt->fetchAll());
?>