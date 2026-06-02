<?php
// Ce fichier contient les fonctions et configurations globales de l'application.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/fonctions.php';

function cv_current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function cv_require_login(): void
{
    if (!cv_current_user()) {
        header('Location: login.php');
        exit();
    }
}

function cv_redirect_if_logged_in(string $target = 'dashboard.php'): void
{
    if (cv_current_user()) {
        header('Location: ' . $target);
        exit();
    }
}

function cv_password_matches(string $plainPassword, string $storedPassword): bool
{
    if (password_verify($plainPassword, $storedPassword)) {
        return true;
    }

    return hash_equals($storedPassword, $plainPassword);
}

function cv_store_user_session(array $user): void
{
    $_SESSION['user'] = [
        'id' => (int)($user['Id_Utilisateur'] ?? 0),
        'name' => (string)($user['nom'] ?? ''),
        'email' => (string)($user['email'] ?? ''),
        'role' => (string)($user['role'] ?? 'user'),
        'statut' => (string)($user['statut'] ?? ''),
    ];
}

/**
 * Vérifie que l'utilisateur courant est un administrateur.
 * Permet la compatibilité en vérifiant soit `statut === 'admin'` soit `role === 'admin'`.
 */
function cv_require_admin(): void
{
    if (!cv_current_user()) {
        header('Location: login.php');
        exit();
    }

    $u = cv_current_user();
    $role = strtolower(trim((string)($u['role'] ?? '')));
    $statut = strtolower(trim((string)($u['statut'] ?? '')));
    $isAdmin = ($role === 'admin') && ($statut === 'actif');

    if (! $isAdmin) {
        cv_flash_set('danger', 'Accès refusé : privilèges administrateur requis.');
        header('Location: dashboard.php');
        exit();
    }
}

function cv_now(): string
{
    return date('Y-m-d H:i:s');
}

function cv_flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function cv_flash_get(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function cv_store_upload_file(array $file, string $directory, string $prefix = 'file'): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erreur lors de l\'upload du fichier.');
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Fichier uploadé invalide.');
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0) {
        throw new RuntimeException('Fichier vide.');
    }

    $mime = (string)mime_content_type($tmpName);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Format non supporte. Utilisez JPG, PNG ou GIF.');
    }

    $baseDirectory = dirname(__DIR__) . '/' . ltrim($directory, '/');
    if (!is_dir($baseDirectory) && !mkdir($baseDirectory, 0777, true) && !is_dir($baseDirectory)) {
        throw new RuntimeException('Impossible de créer le dossier de destination.');
    }

    $filename = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $destination = $baseDirectory . '/' . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Impossible de sauvegarder le fichier.');
    }

    return trim($directory, '/') . '/' . $filename;
}


function cv_try_remember_login(PDO $pdo): bool {
    if (!isset($_COOKIE['remember_token'])) return false;

    $token = $_COOKIE['remember_token'];
    $stmt  = $pdo->prepare('
        SELECT u.Id_Utilisateur, u.nom, u.email, u.mot_de_passe, u.role, u.statut
        FROM remember_tokens rt
        JOIN utilisateur u ON u.Id_Utilisateur = rt.Id_Utilisateur
        WHERE rt.token = :token AND rt.expires_at > NOW()
        LIMIT 1
    ');
    $stmt->execute([':token' => hash('sha256', $token)]);
    $user = $stmt->fetch();

    if ($user && strtolower((string)($user['statut'] ?? 'actif')) === 'actif') {
        session_regenerate_id(true);
        cv_store_user_session($user);
        return true;
    }

    setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
    return false;
}