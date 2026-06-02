<?php
require_once __DIR__ . '/../includes/app.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
}
if (isset($_COOKIE['remember_token'])) {
    $pdo->prepare('DELETE FROM remember_tokens WHERE token = :token')
        ->execute([':token' => hash('sha256', $_COOKIE['remember_token'])]);

    setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
}
session_destroy();

header('Location: login.php');
exit();
