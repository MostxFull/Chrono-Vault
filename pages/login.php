<?php
require_once __DIR__ . '/../includes/app.php';

cv_redirect_if_logged_in('dashboard.php');

if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $stmt = $pdo->prepare('
        SELECT u.Id_Utilisateur, u.nom, u.email, u.mot_de_passe, u.role, u.statut
        FROM remember_tokens rt
        JOIN utilisateur u ON u.Id_Utilisateur = rt.Id_Utilisateur
        WHERE rt.token = :token
          AND rt.expires_at > NOW()
        LIMIT 1
    ');
    $stmt->execute([':token' => hash('sha256', $token)]);
    $user = $stmt->fetch();

    if ($user && strtolower((string)($user['statut'] ?? 'actif')) === 'actif') {
        session_regenerate_id(true);
        cv_store_user_session($user);

        $newRaw  = bin2hex(random_bytes(32));
        $newHash = hash('sha256', $newRaw);
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

        $pdo->prepare('UPDATE remember_tokens SET token = :token, expires_at = :expires WHERE token = :old')
            ->execute([':token' => $newHash, ':expires' => $expires, ':old' => hash('sha256', $token)]);

        setcookie('remember_token', $newRaw, [
            'expires'  => strtotime('+30 days'),
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        header('Location: dashboard.php');
        exit();
    }

    setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
}

$error = '';
$flash = cv_flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email']    ?? '');
    $password   = trim($_POST['password'] ?? '');
    $rememberMe = !empty($_POST['remember_me']);

    if ($email !== '' && $password !== '') {
        $stmt = $pdo->prepare('SELECT Id_Utilisateur, nom, email, mot_de_passe, role, statut FROM utilisateur WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && cv_password_matches($password, (string)$user['mot_de_passe'])) {
            if (strtolower((string)($user['statut'] ?? 'actif')) !== 'actif') {
                $error = 'Ce compte est désactivé.';
            } else {
                session_regenerate_id(true);
                cv_store_user_session($user);

                if ($rememberMe) {
                    $rawToken    = bin2hex(random_bytes(32));
                    $hashedToken = hash('sha256', $rawToken);
                    $expiresAt   = date('Y-m-d H:i:s', strtotime('+30 days'));

                    // One active token per user
                    $pdo->prepare('DELETE FROM remember_tokens WHERE Id_Utilisateur = :uid')
                        ->execute([':uid' => $user['Id_Utilisateur']]);

                    $pdo->prepare('INSERT INTO remember_tokens (Id_Utilisateur, token, expires_at) VALUES (:uid, :token, :expires)')
                        ->execute([
                            ':uid'     => $user['Id_Utilisateur'],
                            ':token'   => $hashedToken,
                            ':expires' => $expiresAt,
                        ]);

                    setcookie('remember_token', $rawToken, [
                        'expires'  => strtotime('+30 days'),
                        'path'     => '/',
                        'secure'   => true,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                }

                header('Location: dashboard.php');
                exit();
            }
        } else {
            $error = 'Email ou mot de passe invalide';
        }
    } else {
        $error = 'Tous les champs sont obligatoires';
    }
}
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Connexion — ChronoVault</title>
<link rel="icon" type="image/x-icon" href="https://chronovault.infinityfree.me/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
  </head>

  <body class="auth-page">

   

    <main class="auth-main">
      <div class="auth-card text-center">

        <a href="../index.php" class="brand">ChronoVault</a>
        <p class="auth-subtitle mb-4">Accédez à votre sanctuaire numérique</p>

        <?php if ($flash && ($flash['type'] ?? '') === 'success') : ?>
          <p class="text-success mt-3 text-center small"><?= h((string)$flash['message']) ?></p>
        <?php endif; ?>

        <form method="POST">

          <div class="mb-4 text-start">
            <label class="form-label small text-muted">E-mail</label>
            <input
              type="email" name="email" class="form-control"
              placeholder="votre@email.com"
              value="<?= h($_POST['email'] ?? '') ?>"
              required />
          </div>

          <div class="mb-3 text-start">
            <div class="d-flex justify-content-between">
              <label class="form-label small text-muted">Mot de passe</label>
              <a href="#" style="font-size:10px;color:var(--muted)">Mot de passe oublié ?</a>
            </div>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required />
          </div>

          <div class="mb-3 text-start">
            <div class="form-check">
              <input
                class="form-check-input" type="checkbox"
                name="remember_me" id="remember_me" value="1"
                <?= !empty($_POST['remember_me']) ? 'checked' : '' ?> />
              <label class="form-check-label small text-muted" for="remember_me">
                Se souvenir de moi <span style="font-size:0.75rem">(30 jours)</span>
              </label>
            </div>
          </div>

          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-chrono-gold">Se connecter →</button>
          </div>

          <?php if ($error !== '') : ?>
            <p class="text-danger mt-3 text-center small"><?= h($error) ?></p>
          <?php endif; ?>

        </form>

        <p class="mt-4 small">
          Pas encore de compte ?
          <a href="signup.php" class="fw-bold" style="color:var(--primary)">S'inscrire</a>
        </p>

        <div class="auth-footer-links mt-4">
          <p class="small text-muted mb-1">© 2026 ChronoVault</p>
          <a href="#">Confidentialité</a> &nbsp;|&nbsp; <a href="#">Aide</a>
        </div>

      </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>