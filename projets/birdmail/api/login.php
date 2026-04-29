<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$payload = json_decode(file_get_contents('php://input') ?: '{}', true);
$result = login_user(
    trim((string) ($payload['email'] ?? '')),
    trim((string) ($payload['password'] ?? ''))
);

echo json_encode($result);
?>