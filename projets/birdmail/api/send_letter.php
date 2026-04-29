<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/crypto.php';

header('Content-Type: application/json; charset=utf-8');

$user = require_login();
$db = get_db();

$receiverId = (int)($_POST['receiver_id'] ?? 0);
$subject = trim((string)($_POST['subject'] ?? ''));
$text = trim((string)($_POST['text_content'] ?? ''));
$drawingData = trim((string)($_POST['drawing_data'] ?? ''));
$stampStyle = trim((string)($_POST['stamp_style'] ?? 'heart'));
$themeStyle = trim((string)($_POST['theme_style'] ?? 'basic'));
$mediaType = trim((string)($_POST['media_type'] ?? 'file'));
$externalUrl = trim((string)($_POST['external_url'] ?? ''));

$mediaPath = null;
$storedMediaType = 'none';

if ($mediaType === 'file' && !empty($_FILES['media_file']['tmp_name']) && is_uploaded_file($_FILES['media_file']['tmp_name'])) {
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'audio/mpeg' => 'mp3',
        'audio/ogg' => 'ogg',
        'audio/wav' => 'wav',
        'audio/x-m4a' => 'm4a',
        'audio/mp4' => 'm4a'
    ];

    $mime = mime_content_type($_FILES['media_file']['tmp_name']);
    if (isset($allowed[$mime])) {
        $filename = 'media_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $target = dirname(__DIR__) . '/assets/uploads/' . $filename;
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }
        if (move_uploaded_file($_FILES['media_file']['tmp_name'], $target)) {
            $mediaPath = 'assets/uploads/' . $filename;
            if (str_starts_with($mime, 'image/')) $storedMediaType = 'image';
            elseif (str_starts_with($mime, 'video/')) $storedMediaType = 'video';
            elseif (str_starts_with($mime, 'audio/')) $storedMediaType = 'audio';
        }
    }
} elseif (($mediaType === 'link' || $mediaType === 'youtube') && $externalUrl !== '') {
    $storedMediaType = $mediaType;
}

if ($receiverId <= 0 || $subject === '' || $text === '') {
    echo json_encode(['ok' => false, 'message' => 'Merci de remplir le destinataire, le sujet et le message.']);
    exit;
}

$stmt = $db->prepare('
    INSERT INTO letters(
        sender_id, receiver_id, subject, encrypted_text, drawing_data,
        media_type, media_path, external_url, stamp_style, theme_style, bird_style, cage_style
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
');

$stmt->execute([
    $user['id'],
    $receiverId,
    $subject,
    encrypt_text($text),
    $drawingData !== '' ? $drawingData : null,
    $storedMediaType,
    $mediaPath,
    $externalUrl !== '' ? $externalUrl : null,
    $stampStyle,
    $themeStyle !== '' ? $themeStyle : 'basic',
    (string)($user['bird_style'] ?? 'bluebird'),
    (string)($user['cage_style'] ?? 'gold')
]);

echo json_encode(['ok' => true, 'message' => 'Lettre envoyée.']);
