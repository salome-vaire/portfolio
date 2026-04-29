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

$stmt = $db->query("
    SELECT l.id, l.subject, l.created_at, l.is_hidden, s.username AS sender_name, r.username AS receiver_name
    FROM letters l
    JOIN users s ON s.id = l.sender_id
    JOIN users r ON r.id = l.receiver_id
    ORDER BY l.id DESC
    LIMIT 100
");
$letters = $stmt->fetchAll();

function e(?string $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pannel admin - BirdMail</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">
</head>
<body class="theme-app">
  <main class="container py-4">
    <div class="glass-card">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <p class="eyebrow mb-1">Modération</p>
          <h1 class="h3 mb-0">Panel admin</h1>
        </div>
        <a class="btn btn-outline-dark" href="../app.php">Retour</a>
      </div>

      <div class="table-responsive mt-4">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Sujet</th>
              <th>De</th>
              <th>Vers</th>
              <th>Date</th>
              <th>État</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($letters as $letter): ?>
              <tr>
                <td><?= (int)$letter['id'] ?></td>
                <td><?= e((string)$letter['subject']) ?></td>
                <td><?= e((string)$letter['sender_name']) ?></td>
                <td><?= e((string)$letter['receiver_name']) ?></td>
                <td><?= e((string)$letter['created_at']) ?></td>
                <td><?= (int)$letter['is_hidden'] === 1 ? 'Masquée' : 'Visible' ?></td>
                <td>
                  <form method="post" action="moderate.php" class="d-inline">
                    <input type="hidden" name="letter_id" value="<?= (int)$letter['id'] ?>">
                    <input type="hidden" name="action_type" value="<?= (int)$letter['is_hidden'] === 1 ? 'show' : 'hide' ?>">
                    <button class="btn btn-sm btn-outline-dark"><?= (int)$letter['is_hidden'] === 1 ? 'Réafficher' : 'Masquer' ?></button>
                  </form>
                  <form method="post" action="moderate.php" class="d-inline ms-1">
                    <input type="hidden" name="letter_id" value="<?= (int)$letter['id'] ?>">
                    <input type="hidden" name="action_type" value="delete">
                    <button class="btn btn-sm btn-danger">Supprimer</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
