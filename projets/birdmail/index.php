<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

start_session_if_needed();

if (current_user()) {
    header('Location: app.php');
    exit;
}

$message = '';
$demoCode = null;
$mode = $_POST['mode'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'register') {
        $result = register_user(
            trim($_POST['username'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['password'] ?? '')
        );
        $message = $result['message'];
    } elseif ($mode === 'login') {
        $result = login_user(
            trim($_POST['email'] ?? ''),
            trim($_POST['password'] ?? '')
        );
        $message = $result['message'];
        $demoCode = $result['demo_code'] ?? null;
    } elseif ($mode === 'verify_2fa') {
        $result = verify_twofa(trim($_POST['twofa_code'] ?? ''));
        if ($result['ok']) {
            header('Location: app.php');
            exit;
        }
        $message = $result['message'];
    }
}

$pending2fa = !empty($_SESSION['pending_2fa_user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BirdMail</title>
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#8ed6ff">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body class="theme-page theme-basic">
  <main class="auth-shell container py-5">
    <div class="hero-card mx-auto">
      <div class="row g-0">
        <div class="col-lg-6 p-4 p-lg-5 left-pane">
          <p class="eyebrow">BirdMail</p>
          <h1 class="display-6 fw-bold">La messagerie mignonne où ton oiseau livre tes lettres</h1>
          <p class="lead mb-4">Messages, dessins, images, tampons et personnalisation dans une ambiance carnet, crayonnée et personnalisable.</p>
          <ul class="feature-list">
            <li>Messagerie chiffrée</li>
            <li>Dessin intégré au message</li>
            <li>Compatible mobile et PC</li>
            <li>API JSON incluse</li>
            <li>A2F de démonstration</li>
          </ul>
        </div>
        <div class="col-lg-6 p-4 p-lg-5 right-pane">
          <?php if ($message !== ''): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
          <?php endif; ?>

          <?php if ($demoCode): ?>
            <div class="alert alert-warning">
              <strong>Code A2F de démonstration :</strong> <?= htmlspecialchars($demoCode) ?><br>
              <small>Ce code est affiché pour faciliter la présentation en BTS.</small>
            </div>
          <?php endif; ?>

          <?php if ($pending2fa): ?>
            <h2 class="h4 mb-3">Validation A2F</h2>
            <form method="post" class="vstack gap-3">
              <input type="hidden" name="mode" value="verify_2fa">
              <div>
                <label for="twofa_code" class="form-label">Code à 6 chiffres</label>
                <input type="text" class="form-control" id="twofa_code" name="twofa_code" maxlength="6" required>
              </div>
              <button class="btn btn-primary">Valider</button>
            </form>
          <?php else: ?>
            <div class="row g-4">
              <div class="col-md-6">
                <h2 class="h4 mb-3">Connexion</h2>
                <form method="post" class="vstack gap-3">
                  <input type="hidden" name="mode" value="login">
                  <div>
                    <label for="login_email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="login_email" name="email" required>
                  </div>
                  <div>
                    <label for="login_password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="login_password" name="password" required>
                  </div>
                  <button class="btn btn-primary">Se connecter</button>
                </form>
              </div>

              <div class="col-md-6">
                <h2 class="h4 mb-3">Créer un compte</h2>
                <form method="post" class="vstack gap-3">
                  <input type="hidden" name="mode" value="register">
                  <div>
                    <label for="register_username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" class="form-control" id="register_username" name="username" required>
                  </div>
                  <div>
                    <label for="register_email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="register_email" name="email" required>
                  </div>
                  <div>
                    <label for="register_password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="register_password" name="password" required>
                  </div>
                  <button class="btn btn-outline-primary">Créer le compte</button>
                </form>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
  <script src="assets/app.js"></script>
</body>
</html>
