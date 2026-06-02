<?php
require_once __DIR__ . '/../includes/app.php';

cv_redirect_if_logged_in();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($full_name !== '' && $email !== '' && $password !== '') {
        $checkStmt = $pdo->prepare('SELECT Id_Utilisateur FROM utilisateur WHERE email = :email LIMIT 1');
        $checkStmt->execute([':email' => $email]);

        if ($checkStmt->fetch()) {
            $error = 'Cet email existe déjà.';
        } else {
            $insertStmt = $pdo->prepare('INSERT INTO utilisateur (nom, email, mot_de_passe, role ,statut) VALUES (:nom, :email, :mot_de_passe, :role, :statut)');
            $insertStmt->execute([
                ':nom' => $full_name,
                ':email' => $email,
                ':mot_de_passe' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => 'user',
                ':statut'=> 'actif',
            ]);

            cv_flash_set('success', 'Compte créé avec succès. Vous pouvez vous connecter.');
            header('Location: login.php');
            exit();
        }
    } else {
        $error = 'Tous les champs sont obligatoires.';
    }
}
?>

<!doctype html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inscription — ChronoVault</title>
      <link rel="icon" type="image/x-icon" href="https://chronovault.infinityfree.me/favicon.svg">

    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet" />

    <link
      href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&family=Manrope:wght@400;500;600&display=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="../css/style.css" />
  </head>

  <body class="auth-page">

    

    <main class="auth-main">
      <div class="auth-card text-center">

        <a href="../index.php" class="brand">ChronoVault</a>
        <p class="auth-subtitle mb-4">Créer votre compte</p>

        <form method="POST" id="signup-form">
          <div class="mb-3 text-start">
            <label class="form-label small text-muted">Nom</label>
       <input type="text" name="name" class="form-control" placeholder="Votre nom complet" required />
          </div>

          <div class="mb-3 text-start">
            <label class="form-label small text-muted">E-mail</label>
      <input type="email" name="email" class="form-control" placeholder="votre@email.com" required />
          </div>

          <div class="mb-3 text-start">
            <label class="form-label small text-muted">Mot de passe</label>
         <input type="password" name="password" id="signup-password" class="form-control" placeholder="••••••••" required aria-describedby="pwHelp" />

            <div id="password-strength" class="mt-2" style="display:none;">
              <div class="progress" style="height:8px;">
                <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width:0%"></div>
              </div>
              <div id="password-strength-text" class="small text-muted mt-1">Force du mot de passe</div>
              <ul id="password-requirements" class="small mt-2 mb-0 text-start" style="list-style:none; padding-left:0;">
                <li id="pr-length">• 8 caractères minimum</li>
                <li id="pr-lower">• Lettre minuscule</li>
                <li id="pr-upper">• Lettre majuscule</li>
                <li id="pr-digit">• Chiffre</li>
                <li id="pr-special">• Caractère spécial (ex: !@#$%)</li>
              </ul>
            </div>
          </div>

          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-chrono-gold">
              S'inscrire →
            </button>
          </div>

        <?php if ($error !== '') : ?>
          <p class="text-danger mt-3 small">
            <?= h($error) ?>
          </p>
        <?php endif; ?>
        </form>

        <p class="mt-4 small">
          Déjà un compte ?
          <a href="login.php" class="fw-bold" style="color: var(--primary)"
            >Se connecter</a
          >
        </p>

        <div class="auth-footer-links mt-4">
          <p class="small text-muted mb-1">© 2026 ChronoVault</p>
          <a href="#">Confidentialité</a> &nbsp;|&nbsp;
          <a href="#">Aide</a>
        </div>
      </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/main.js"></script>
    <script>
      (function () {
        const pw = document.getElementById('signup-password');
        const bar = document.getElementById('password-strength-bar');
        const text = document.getElementById('password-strength-text');
        const box = document.getElementById('password-strength');
        const reqs = {
          length: document.getElementById('pr-length'),
          lower: document.getElementById('pr-lower'),
          upper: document.getElementById('pr-upper'),
          digit: document.getElementById('pr-digit'),
          special: document.getElementById('pr-special')
        };

        function scorePassword(s) {
          let score = 0;
          const checks = {
            length: s.length >= 8,
            lower: /[a-z]/.test(s),
            upper: /[A-Z]/.test(s),
            digit: /[0-9]/.test(s),
            special: /[^A-Za-z0-9]/.test(s)
          };
          for (const k in checks) if (checks[k]) score++;
          return { score, checks };
        }

        function updateUI() {
          const v = pw.value || '';
          if (v === '') { box.style.display = 'none'; bar.style.width = '0%'; text.textContent = 'Force du mot de passe'; return; }
          box.style.display = 'block';
          const { score, checks } = scorePassword(v);
          const pct = (score / 5) * 100;
          bar.style.width = pct + '%';
          if (score <= 2) { bar.className = 'progress-bar bg-danger'; text.textContent = 'Faible'; }
          else if (score === 3) { bar.className = 'progress-bar bg-warning'; text.textContent = 'Moyen'; }
          else { bar.className = 'progress-bar bg-success'; text.textContent = 'Fort'; }

          reqs.length.style.color = checks.length ? 'green' : '';
          reqs.lower.style.color = checks.lower ? 'green' : '';
          reqs.upper.style.color = checks.upper ? 'green' : '';
          reqs.digit.style.color = checks.digit ? 'green' : '';
          reqs.special.style.color = checks.special ? 'green' : '';
        }

        if (pw) {
          pw.addEventListener('input', updateUI);
          const form = document.getElementById('signup-form');
          form.addEventListener('submit', function (ev) {
            const { score, checks } = scorePassword(pw.value || '');
            // require at least 4/5 checks (length + 3 others) to allow
            if (score < 4) {
              ev.preventDefault();
              updateUI();
              alert('Votre mot de passe est trop faible. Respectez au moins 4 des critères affichés.');
              pw.focus();
            }
          });
        }
      })();
    </script>

  </body>
</html>