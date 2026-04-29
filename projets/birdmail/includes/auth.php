<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function start_session_if_needed(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function current_user(): ?array {
    start_session_if_needed();
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_login(): array {
    $user = current_user();
    if (!$user) {
        header('Location: index.php');
        exit;
    }
    return $user;
}

function register_user(string $username, string $email, string $password): array {
    $db = get_db();

    if (strlen($username) < 3) {
        return ['ok' => false, 'message' => "Le nom d'utilisateur doit contenir au moins 3 caractères."];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Adresse email invalide.'];
    }

    if (strlen($password) < 6) {
        return ['ok' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères.'];
    }

    try {
        $stmt = $db->prepare(
            'INSERT INTO users(username, email, password_hash, bird_style, cage_style, theme_style, music_style)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            'bluebird',
            'gold',
            'basic',
            'breeze'
        ]);
        return ['ok' => true, 'message' => 'Compte créé. Vous pouvez maintenant vous connecter.'];
    } catch (PDOException $e) {
        return ['ok' => false, 'message' => 'Nom d’utilisateur ou email déjà utilisé.'];
    }
}

function login_user(string $email, string $password): array {
    start_session_if_needed();
    $db = get_db();

    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['ok' => false, 'message' => 'Identifiants incorrects.'];
    }

    $code = (string) random_int(100000, 999999);
    $expiresAt = time() + 300;

    $stmt = $db->prepare('INSERT INTO twofa_codes(user_id, code, expires_at) VALUES (?, ?, ?)
        ON CONFLICT(user_id) DO UPDATE SET code = excluded.code, expires_at = excluded.expires_at');
    $stmt->execute([$user['id'], $code, $expiresAt]);

    $_SESSION['pending_2fa_user_id'] = (int) $user['id'];

    return [
        'ok' => true,
        'message' => 'Code A2F généré.',
        'demo_code' => $code
    ];
}

function verify_twofa(string $code): array {
    start_session_if_needed();

    if (empty($_SESSION['pending_2fa_user_id'])) {
        return ['ok' => false, 'message' => 'Aucune connexion en attente.'];
    }

    $userId = (int) $_SESSION['pending_2fa_user_id'];
    $db = get_db();

    $stmt = $db->prepare('SELECT * FROM twofa_codes WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if (!$row) {
        return ['ok' => false, 'message' => 'Code introuvable.'];
    }

    if ((int) $row['expires_at'] < time()) {
        return ['ok' => false, 'message' => 'Code expiré.'];
    }

    if ($row['code'] !== $code) {
        return ['ok' => false, 'message' => 'Code incorrect.'];
    }

    unset($_SESSION['pending_2fa_user_id']);
    $_SESSION['user_id'] = $userId;

    $stmt = $db->prepare('DELETE FROM twofa_codes WHERE user_id = ?');
    $stmt->execute([$userId]);

    return ['ok' => true, 'message' => 'Connexion validée.'];
}

function logout_user(): void {
    start_session_if_needed();
    $_SESSION = [];
    session_destroy();
}
