<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/crypto.php';

header('Content-Type: application/json; charset=utf-8');

$user = require_login();
$db = get_db();

$limit = 10;
$receivedPage = max(1, (int)($_GET['received_page'] ?? 1));
$sentPage = max(1, (int)($_GET['sent_page'] ?? 1));

function e(?string $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function youtube_embed_url(string $url): ?string {
    if (preg_match('~(?:v=|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return null;
}

function render_media(array $letter): string {
    $type = (string)($letter['media_type'] ?? 'none');
    $path = (string)($letter['media_path'] ?? '');
    $url = (string)($letter['external_url'] ?? '');

    if ($type === 'image' && $path !== '') {
        return '<img src="' . e($path) . '" alt="Média joint" class="letter-image">'
            . '<a class="btn btn-sm btn-outline-dark mt-2" href="' . e($path) . '" download>Télécharger le média</a>';
    }

    if ($type === 'video' && $path !== '') {
        return '<video controls class="letter-video"><source src="' . e($path) . '"></video>'
            . '<a class="btn btn-sm btn-outline-dark mt-2" href="' . e($path) . '" download>Télécharger la vidéo</a>';
    }

    if ($type === 'audio' && $path !== '') {
        return '<audio controls class="letter-audio"><source src="' . e($path) . '"></audio>'
            . '<a class="btn btn-sm btn-outline-dark mt-2" href="' . e($path) . '" download>Télécharger l’audio</a>';
    }

    if ($type === 'youtube' && $url !== '') {
        $embed = youtube_embed_url($url);
        if ($embed) {
            return '<iframe class="letter-embed" src="' . e($embed) . '" allowfullscreen></iframe>'
                . '<a class="btn btn-sm btn-outline-dark mt-2" href="' . e($url) . '" target="_blank" rel="noopener">Ouvrir sur YouTube</a>';
        }
    }

    if ($type === 'link' && $url !== '') {
        return '<a class="btn btn-sm btn-outline-dark mt-2" href="' . e($url) . '" target="_blank" rel="noopener">Ouvrir le lien</a>';
    }

    return '';
}

function render_letter_card(array $letter, string $mode = 'received'): string {
    $title = e($letter['subject'] ?? '(Sans sujet)');
    $name = $mode === 'received' ? e($letter['sender_name'] ?? 'Utilisateur') : e($letter['receiver_name'] ?? 'Utilisateur');
    $prefix = $mode === 'received' ? 'De' : 'Vers';
    $created = e($letter['created_at'] ?? '');
    $theme = e($letter['theme_style'] ?? 'basic');
    $stamp = e($letter['stamp_style'] ?? 'heart');
    $text = '';

    if (!empty($letter['encrypted_text'])) {
        try {
            $text = decrypt_text((string)$letter['encrypted_text']);
        } catch (Throwable $e) {
            $text = 'Message indisponible.';
        }
    }

    ob_start();
    ?>
    <article class="letter-card" style="background-image:url('assets/letters/<?= $theme ?>.png')">
      <div class="letter-card__top">
        <div>
          <h3 class="letter-card__title"><?= $title ?></h3>
          <p class="letter-card__meta"><?= $prefix ?> : <?= $name ?></p>
        </div>
        <div class="letter-card__date"><?= $created ?></div>
      </div>

      <div class="letter-body">
        <p><?= nl2br(e($text)) ?></p>

        <?php if (!empty($letter['drawing_data'])): ?>
          <img src="<?= e((string)$letter['drawing_data']) ?>" alt="Dessin joint" class="letter-image">
          <a class="btn btn-sm btn-outline-dark mt-2" href="<?= e((string)$letter['drawing_data']) ?>" download="dessin-birdmail.png">Télécharger le dessin</a>
        <?php endif; ?>

        <?= render_media($letter) ?>

        <div class="stamp-large">
          <img src="assets/stamps/<?= $stamp ?>.png" alt="Tampon">
        </div>
      </div>
    </article>
    <?php
    return trim((string)ob_get_clean());
}

$countStmt = $db->prepare('SELECT COUNT(*) FROM letters WHERE receiver_id = ? AND is_hidden = 0');
$countStmt->execute([$user['id']]);
$receivedTotal = (int)$countStmt->fetchColumn();
$receivedPages = max(1, (int)ceil($receivedTotal / $limit));
$receivedPage = min($receivedPage, $receivedPages);
$receivedOffset = ($receivedPage - 1) * $limit;

$countStmt = $db->prepare('SELECT COUNT(*) FROM letters WHERE sender_id = ? AND is_hidden = 0');
$countStmt->execute([$user['id']]);
$sentTotal = (int)$countStmt->fetchColumn();
$sentPages = max(1, (int)ceil($sentTotal / $limit));
$sentPage = min($sentPage, $sentPages);
$sentOffset = ($sentPage - 1) * $limit;

$receivedStmt = $db->prepare("
    SELECT l.*, u.username AS sender_name
    FROM letters l
    JOIN users u ON u.id = l.sender_id
    WHERE l.receiver_id = ? AND l.is_hidden = 0
    ORDER BY l.id DESC
    LIMIT $limit OFFSET $receivedOffset
");
$receivedStmt->execute([$user['id']]);
$receivedLetters = $receivedStmt->fetchAll();

$sentStmt = $db->prepare("
    SELECT l.*, u.username AS receiver_name
    FROM letters l
    JOIN users u ON u.id = l.receiver_id
    WHERE l.sender_id = ? AND l.is_hidden = 0
    ORDER BY l.id DESC
    LIMIT $limit OFFSET $sentOffset
");
$sentStmt->execute([$user['id']]);
$sentLetters = $sentStmt->fetchAll();

$receivedHtml = !$receivedLetters
    ? '<p class="text-muted mb-0">Aucune lettre reçue pour le moment.</p>'
    : implode('', array_map(fn($letter) => render_letter_card($letter, 'received'), $receivedLetters));

$sentHtml = !$sentLetters
    ? '<p class="text-muted mb-0">Aucune lettre envoyée pour le moment.</p>'
    : implode('', array_map(fn($letter) => render_letter_card($letter, 'sent'), $sentLetters));

echo json_encode([
    'ok' => true,
    'received_html' => $receivedHtml,
    'sent_html' => $sentHtml,
    'received_page' => $receivedPage,
    'sent_page' => $sentPage,
    'received_pages' => $receivedPages,
    'sent_pages' => $sentPages,
    'received_total' => $receivedTotal
]);
