<?php
require_once __DIR__ . '/../includes/app.php';
cv_require_login();

$activePage = 'parametre';
$user = cv_current_user();
$userId = (int)($user['id'] ?? 0);

$stmt = $pdo->prepare('SELECT Id_Utilisateur, nom, email, mot_de_passe, role FROM utilisateur WHERE Id_Utilisateur = :user_id LIMIT 1');
$stmt->execute([':user_id' => $userId]);
$profile = $stmt->fetch();

if (!$profile) {
    cv_flash_set('danger', 'Utilisateur introuvable.');
    header('Location: ../index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($fullName === '' || $email === '') {
            $errors[] = 'Le nom et l’email sont obligatoires.';
        } else {
            $stmt = $pdo->prepare('SELECT Id_Utilisateur FROM utilisateur WHERE email = :email AND Id_Utilisateur <> :user_id LIMIT 1');
            $stmt->execute([':email' => $email, ':user_id' => $userId]);

            if ($stmt->fetch()) {
                $errors[] = 'Cet email est déjà utilisé.';
            } else {
                $stmt = $pdo->prepare('UPDATE utilisateur SET nom = :nom, email = :email WHERE Id_Utilisateur = :user_id');
                $stmt->execute([
                    ':nom' => $fullName,
                    ':email' => $email,
                    ':user_id' => $userId,
                ]);

                $_SESSION['user']['name'] = $fullName;
                $_SESSION['user']['email'] = $email;
                cv_flash_set('success', 'Profil mis à jour.');
                header('Location: parametre.php');
                exit();
            }
        }
    }

    if ($action === 'change_password') {
        $currentPassword = trim($_POST['current_password'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $errors[] = 'Tous les champs du mot de passe sont obligatoires.';
        } elseif (!cv_password_matches($currentPassword, (string)$profile['mot_de_passe'])) {
            $errors[] = 'Le mot de passe actuel est incorrect.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'La confirmation du nouveau mot de passe ne correspond pas.';
        } else {
            $stmt = $pdo->prepare('UPDATE utilisateur SET mot_de_passe = :mot_de_passe WHERE Id_Utilisateur = :user_id');
            $stmt->execute([
                ':mot_de_passe' => password_hash($newPassword, PASSWORD_DEFAULT),
                ':user_id' => $userId,
            ]);

            cv_flash_set('success', 'Mot de passe mis à jour.');
            header('Location: parametre.php');
            exit();
        }
    }

    if ($action === 'delete_account') {
        $stmt = $pdo->prepare('UPDATE utilisateur SET statut = :statut WHERE Id_Utilisateur = :user_id');
        $stmt->execute([
            ':statut' => 'inactif',
            ':user_id' => $userId,
        ]);

        session_destroy();
        cv_flash_set('success', 'Votre compte a été désactivé.');
        header('Location: ../index.php');
        exit();
    }
}

require_once __DIR__ . '/../includes/layout.php';
$flash = cv_flash_get();
?>

<header class="page-header">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
      <div class="page-title-bar">
        <div class="title-accent"></div>
        <h2 class="page-title">Paramètres</h2>
      </div>
      <p class="page-subtitle mb-0">Gérez votre compte et votre sécurité.</p>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?php echo h($flash['type'] ?? 'info'); ?>" role="alert"><?php echo h($flash['message'] ?? ''); ?></div>
  <?php endif; ?>

  <?php foreach ($errors as $error): ?>
    <div class="alert alert-danger" role="alert"><?php echo h($error); ?></div>
  <?php endforeach; ?>
</header>

<section class="cards-section">
  <div class="row g-4 justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="capsule-card" style="height:auto;">
        <div class="card-body-cv">
          <h3 class="card-title-cv">Profil</h3>
          <form method="post">
            <input type="hidden" name="action" value="update_profile" />

            <div class="mb-3">
              <label class="form-label-cv" for="full_name">Nom complet</label>
              <input id="full_name" name="full_name" class="form-input-cv" type="text" value="<?php echo h((string)$profile['nom']); ?>" />
            </div>

            <div class="mb-3">
              <label class="form-label-cv" for="email">Adresse email</label>
              <input id="email" name="email" class="form-input-cv" type="email" value="<?php echo h((string)$profile['email']); ?>" />
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn-primary-cv">Enregistrer le profil</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="capsule-card" style="height:auto;">
        <div class="card-body-cv">
          <h3 class="card-title-cv">Sécurité</h3>
          <p class="card-desc">Changez votre mot de passe en confirmant votre mot de passe actuel.</p>

          <form method="post">
            <input type="hidden" name="action" value="change_password" />

            <div class="mb-3">
              <label class="form-label-cv" for="current_password">Mot de passe actuel</label>
              <input id="current_password" name="current_password" type="password" class="form-input-cv" />
            </div>

            <div class="mb-3">
              <label class="form-label-cv" for="new_password">Nouveau mot de passe</label>
              <input id="new_password" name="new_password" type="password" class="form-input-cv" />
            </div>

            <div class="mb-3">
              <label class="form-label-cv" for="confirm_password">Confirmer le nouveau mot de passe</label>
              <input id="confirm_password" name="confirm_password" type="password" class="form-input-cv" />
            </div>

            <div class="d-flex justify-content-end gap-2">
              <button type="submit" class="btn-filter">Mettre à jour le mot de passe</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="capsule-card" style="height:auto;">
        <div class="card-body-cv">
          <h3 class="card-title-cv text-danger">Zone de danger</h3>
          <p class="card-desc">Désactiver votre compte le rendra inactif. Vous ne pourrez plus vous connecter ni accéder à vos données.</p>
          
          <form method="post" onsubmit="return confirm('Êtes-vous sûr ? Cette action rendra votre compte inactif et vous déconnectera.');">
            <input type="hidden" name="action" value="delete_account" />
            <button type="submit" class="btn btn-danger" style="background-color: #dc3545; border: none; padding: 0.7rem 1.4rem; border-radius: 0.5rem; font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif; cursor: pointer; color: white;">
              Désactiver le compte
            </button>
          </form>
        </div>
      </div>
    </div>
     <div class="col-12 col-lg-8">
  <div class="capsule-card" style="height:auto;">
    <div class="card-body-cv">
      <h3 class="card-title-cv">Apparence</h3>
      <p class="card-desc">Personnalisez le thème de l’interface.</p>

      <div class="mb-3">
        <label class="form-label-cv" for="theme_select">Thème</label>
   <select id="theme_select" class="form-input-cv">
  <option value="indigo">Indigo (par défaut)</option>
  <option value="brown">Brun</option>
  <option value="cendre">Cendre</option>
  <option value="mauve">Mauve</option>
  <option value="ambre">Ambre</option>
  <option value="nuit">Nuit (sombre)</option>
</select>
      </div>

      <div class="d-flex justify-content-end">
        <button type="button" onclick="applyTheme(document.getElementById('theme_select').value)" class="btn-primary-cv">
          Appliquer
        </button>
      </div>
    </div>
  </div>
</div> 
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>