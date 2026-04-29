<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/crypto.php';

$user = require_login();
$db = get_db();

$stmt = $db->prepare('
    SELECT l.id, l.subject, l.created_at, u.username AS sender_name, l.theme_style
    FROM letters l
    JOIN users u ON u.id = l.sender_id
    WHERE l.receiver_id = ?
    ORDER BY l.id DESC
');
$stmt->execute([$user['id']]);

echo json_encode($stmt->fetchAll());
?>