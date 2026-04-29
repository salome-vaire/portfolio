<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/crypto.php';

$user = require_login();
$db = get_db();

$user['username'] = (string)($user['username'] ?? '');
$user['email'] = (string)($user['email'] ?? '');
$user['bird_style'] = (string)($user['bird_style'] ?? 'bluebird');
$user['cage_style'] = (string)($user['cage_style'] ?? 'gold');
$user['music_style'] = (string)($user['music_style'] ?? 'breeze');
$user['role'] = (string)($user['role'] ?? 'user');

function e(?string $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$usersStmt = $db->prepare('SELECT id, username FROM users WHERE id != ? ORDER BY username');
$usersStmt->execute([$user['id']]);
$otherUsers = $usersStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BirdMail - Application</title>
  <meta name="theme-color" content="#8ed6ff">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body class="theme-app">
  <nav class="navbar navbar-expand-lg glass-nav sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">BirdMail</a>
      <div class="ms-auto d-flex gap-2 align-items-center">
        <?php if ($user['role'] === 'admin'): ?>
          <a class="btn btn-sm btn-outline-dark" href="admin/index.php">Panel admin</a>
        <?php endif; ?>
        <span class="small text-muted d-none d-md-inline">Connectée : <?= e($user['username']) ?></span>
        <a class="btn btn-sm btn-outline-dark" href="logout.php">Déconnexion</a>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div id="statusMessage" class="alert alert-info d-none" role="alert"></div>

    <section class="glass-card hero-card mb-4">
      <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div>
          <p class="eyebrow mb-1">Messager central</p>
          <h1 class="h3 mb-0">Ton oiseau BirdMail</h1>
        </div>
        <button class="btn btn-outline-dark btn-sm" type="button" id="toggleSettingsBtn">Afficher / masquer les paramètres</button>
      </div>

      <div class="bird-stage mt-3">
        <div class="speech-bubble" id="birdSpeech">
          Bonjour <?= e($user['username']) ?>. Je peux t’aider à envoyer des lettres, lire tes médias et te prévenir lorsqu’un nouveau message arrive.
        </div>

        <div class="bird-stage__center">
          <img class="bird-stage__cage" id="previewCage" src="assets/cages/<?= e($user['cage_style']) ?>.png" alt="Cage">
          <img class="bird-stage__bird hop" id="previewBird" src="assets/birds/<?= e($user['bird_style']) ?>.png" alt="Oiseau">
        </div>
      </div>

      <div id="settingsPanel" class="d-none mt-4">
        <div class="row g-3">
          <div class="col-lg-6">
            <form method="post" class="vstack gap-3" id="profileForm">
              <div>
                <label class="form-label">Oiseau</label>
                <select class="form-select" name="bird_style" id="bird_style">
                  <option value="bluebird" <?= $user['bird_style']==='bluebird' ? 'selected' : '' ?>>Bleu</option>
                  <option value="peach" <?= $user['bird_style']==='peach' ? 'selected' : '' ?>>Pêche</option>
                  <option value="mint" <?= $user['bird_style']==='mint' ? 'selected' : '' ?>>Menthe</option>
                  <option value="lilac" <?= $user['bird_style']==='lilac' ? 'selected' : '' ?>>Lilas</option>
                  <option value="yellow" <?= $user['bird_style']==='yellow' ? 'selected' : '' ?>>Jaune</option>
                </select>
              </div>

              <div>
                <label class="form-label">Cage</label>
                <select class="form-select" name="cage_style" id="cage_style">
                  <option value="gold" <?= $user['cage_style']==='gold' ? 'selected' : '' ?>>Dorée</option>
                  <option value="pink" <?= $user['cage_style']==='pink' ? 'selected' : '' ?>>Rose</option>
                  <option value="cloud" <?= $user['cage_style']==='cloud' ? 'selected' : '' ?>>Nuage</option>
                </select>
              </div>

              <div>
                <label class="form-label">Musique</label>
                <select class="form-select" name="music_style" id="music_style">
                  <option value="breeze" <?= $user['music_style']==='breeze' ? 'selected' : '' ?>>Breeze</option>
                  <option value="dream" <?= $user['music_style']==='dream' ? 'selected' : '' ?>>Dream</option>
                  <option value="garden" <?= $user['music_style']==='garden' ? 'selected' : '' ?>>Garden</option>
                </select>
              </div>

              <div>
                <label class="form-label d-flex justify-content-between">
                  <span>Volume</span>
                  <span id="musicVolumeValue">35%</span>
                </label>
                <input type="range" class="form-range" id="music_volume" min="0" max="100" step="1" value="35">
              </div>

              <button class="btn btn-primary">Enregistrer</button>
            </form>
          </div>

          <div class="col-lg-6">
            <div class="mini-help-card">
              <h2 class="h5">Fonctionnalités</h2>
              <ul class="mb-0">
                <li>Envoi de lettres avec texte, dessin et média</li>
                <li>Support image, vidéo, audio, lien externe et YouTube</li>
                <li>Pagination des lettres</li>
                <li>Téléchargement des pièces jointes et dessins</li>
                <li>Panel admin pour modération</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row g-4 align-items-start">
      <div class="col-lg-5">
        <div class="glass-card h-100">
          <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div>
              <p class="eyebrow mb-1">Nouvelle lettre</p>
              <h2 class="h4 mb-0">Envoyer un message</h2>
            </div>
            <button class="btn btn-outline-dark btn-sm" id="clearCanvasBtn" type="button">Effacer le dessin</button>
          </div>

          <form class="mt-3" id="sendLetterForm" enctype="multipart/form-data">
            <input type="hidden" name="drawing_data" id="drawing_data">

            <div class="mb-3">
              <label class="form-label">Destinataire</label>
              <select class="form-select" name="receiver_id" required>
                <option value="">Choisir...</option>
                <?php foreach ($otherUsers as $otherUser): ?>
                  <option value="<?= (int)$otherUser['id'] ?>"><?= e((string)$otherUser['username']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Sujet</label>
              <input class="form-control" type="text" name="subject" maxlength="80" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Message écrit</label>
              <textarea class="form-control" rows="5" name="text_content" required></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Papier</label>
              <select class="form-select" name="theme_style">
                <option value="basic">Basique</option>
                <option value="grid">Grille</option>
                <option value="love">Love</option>
                <option value="music">Musique</option>
                <option value="rainbow">Rainbow</option>
                <option value="snowy">Snowy</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Dessin</label>
              <canvas id="letterCanvas" class="letter-canvas" width="700" height="280"></canvas>
            </div>

            <div class="mb-3">
              <label class="form-label">Type de média</label>
              <select class="form-select" id="media_type" name="media_type">
                <option value="file">Fichier local</option>
                <option value="link">Lien externe</option>
                <option value="youtube">Lien YouTube</option>
              </select>
            </div>

            <div class="mb-3" id="localMediaGroup">
              <label class="form-label">Fichier média</label>
              <input class="form-control" type="file" name="media_file" accept=".png,.jpg,.jpeg,.webp,.mp4,.webm,.mp3,.ogg,.wav,.m4a">
            </div>

            <div class="mb-3 d-none" id="externalUrlGroup">
              <label class="form-label">Lien externe</label>
              <input class="form-control" type="url" name="external_url" placeholder="https://...">
            </div>

            <div class="mb-3">
              <label class="form-label">Tampon</label>
              <select class="form-select mb-3" name="stamp_style">
                <option value="heart">Cœur</option>
                <option value="star">Étoile</option>
                <option value="bird">Oiseau</option>
                <option value="cat">Chat</option>
                <option value="blackcat">Chat noir</option>
                <option value="flamingo">Flamant</option>
              </select>

              <div class="stamp-preview">
                <img src="assets/stamps/heart.png" alt="Cœur">
                <img src="assets/stamps/star.png" alt="Étoile">
                <img src="assets/stamps/bird.png" alt="Oiseau">
                <img src="assets/stamps/cat.png" alt="Chat">
                <img src="assets/stamps/blackcat.png" alt="Chat noir">
                <img src="assets/stamps/flamingo.png" alt="Flamant">
              </div>
            </div>

            <button class="btn btn-primary w-100">Envoyer la lettre</button>
          </form>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="glass-card mb-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <p class="eyebrow mb-1">Boîte de réception</p>
              <h2 class="h4 mb-0">Lettres reçues</h2>
            </div>
            <div class="pager">
              <button class="btn btn-sm btn-outline-dark" id="receivedPrevBtn">Précédent</button>
              <span id="receivedPageInfo">Page 1</span>
              <button class="btn btn-sm btn-outline-dark" id="receivedNextBtn">Suivant</button>
            </div>
          </div>
          <div id="receivedLetters" class="mt-3"></div>
        </div>

        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <p class="eyebrow mb-1">Historique</p>
              <h2 class="h4 mb-0">Lettres envoyées</h2>
            </div>
            <div class="pager">
              <button class="btn btn-sm btn-outline-dark" id="sentPrevBtn">Précédent</button>
              <span id="sentPageInfo">Page 1</span>
              <button class="btn btn-sm btn-outline-dark" id="sentNextBtn">Suivant</button>
            </div>
          </div>
          <div id="sentLetters" class="mt-3"></div>
        </div>
      </div>
    </section>
  </main>

  <audio id="themeMusic" loop preload="auto"></audio>

  <script>
    window.BIRDS_INITIAL_MUSIC = <?= json_encode($user['music_style']) ?>;
  </script>
  <script src="assets/app.js"></script>
</body>
</html>
