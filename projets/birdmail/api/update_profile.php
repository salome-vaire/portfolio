<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$user = require_login();
$db = get_db();

$birdStyle = (string)($_POST['bird_style'] ?? 'bluebird');
$cageStyle = (string)($_POST['cage_style'] ?? 'gold');
$musicStyle = (string)($_POST['music_style'] ?? 'breeze');

$stmt = $db->prepare('UPDATE users SET bird_style = ?, cage_style = ?, music_style = ? WHERE id = ?');
$stmt->execute([$birdStyle, $cageStyle, $musicStyle, $user['id']]);

echo json_encode([
    'ok' => true,
    'message' => 'Personnalisation mise à jour.',
    'bird_style' => $birdStyle,
    'cage_style' => $cageStyle,
    'music_style' => $musicStyle
]);
