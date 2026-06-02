<?php
require_once __DIR__ . '/../includes/app.php';
cv_require_login();

$activePage = 'new-capsule';

$user = cv_current_user();
$userId = (int)($user['id'] ?? 0);
$userEmail = strtolower(trim((string)($user['email'] ?? '')));
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $openDate = trim($_POST['open_date'] ?? '');
    $inviteEmailsRaw = trim($_POST['invite_emails'] ?? '');
  $photoPath = null;

    if ($title === '') {
        $errors[] = 'Le titre est obligatoire.';
    }

    if ($openDate === '') {
        $errors[] = 'La date d’ouverture est obligatoire.';
    } elseif (strtotime($openDate) < strtotime(date('Y-m-d'))) {
      $errors[] = 'La date d’ouverture ne peut pas être dans le passé.';
    }

    if (!$errors) {
        try {
        if (!empty($_FILES['photo']['name'])) {
          $photoPath = cv_store_upload_file($_FILES['photo'], 'images/uploads', 'capsule');
        }

        $pdo->beginTransaction();

        $capsuleId = cv_capsule_create([
          'titre' => $title,
          'description' => $description !== '' ? $description : null,
          'photo' => $photoPath,
          'date_creation' => cv_now(),
          'date_ouverture' => $openDate . ' 00:00:00',
          'user_id' => $userId,
        ]);
            $pdo->commit();

            $inviteEmails = preg_split('/[\r\n,;]+/', $inviteEmailsRaw) ?: [];
            $inviteEmails = array_values(array_unique(array_filter(array_map(static function (string $email): string {
                return strtolower(trim($email));
            }, $inviteEmails))));

            $now = cv_now();
            $invitedCount = 0;
            $invalidEmails = [];

            foreach ($inviteEmails as $email) {
                if ($email === '') {
                    continue;
                }

              if ($email === $userEmail) {
                $invalidEmails[] = $email;
                continue;
              }

                $invitee = cv_user_find_by_email($email);

                if (!$invitee || (string)($invitee['statut'] ?? 'actif') !== 'actif') {
                    $invalidEmails[] = $email;
                    continue;
                }

                if (cv_invitation_exists_for_capsule($capsuleId, (int)$invitee['Id_Utilisateur'])) {
                    $invalidEmails[] = $email;
                    continue;
                }

                cv_invitation_create($capsuleId, (int)$invitee['Id_Utilisateur'], 'pending', $now);
                $invitedCount++;
            }

            if ($invitedCount > 0 && $invalidEmails) {
                cv_flash_set('warning', $invitedCount . ' invitation(s) envoyée(s). Adresses invalides: ' . implode(', ', $invalidEmails) . '.');
            } elseif ($invitedCount > 0) {
                cv_flash_set('success', $invitedCount . ' invitation(s) envoyée(s).');
            } elseif ($invalidEmails) {
                cv_flash_set('warning', 'Aucune invitation envoyée. Adresses invalides: ' . implode(', ', $invalidEmails) . '.');
            } else {
                cv_flash_set('success', 'Capsule créée avec succès.');
            }

            header('Location: capsule.php?id=' . $capsuleId);
            exit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($photoPath !== null) {
                $photoFile = dirname(__DIR__) . '/' . ltrim($photoPath, '/');
                if (is_file($photoFile)) {
                    @unlink($photoFile);
                }
            }

            $errors[] = 'Impossible de créer la capsule.';
        }
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
        <h2 class="page-title">Nouvelle capsule</h2>
      </div>
      <p class="page-subtitle mb-0">Créez une capsule temporelle et invitez vos proches.</p>
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
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="capsule-card" style="height:auto;">
        <div class="card-body-cv">
          <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label-cv" for="title">Titre de la capsule</label>
              <input id="title" name="title" type="text" class="form-input-cv" placeholder="Ex: Vacances d'été 2026" />
            </div>

            <div class="mb-3">
              <label class="form-label-cv" for="description">Description</label>
              <textarea id="description" name="description" class="form-input-cv" rows="5" placeholder="Décrivez l'objectif de cette capsule..."></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label-cv" for="photo">Photo de la capsule</label>
              <input id="photo" name="photo" type="file" class="form-input-cv" accept=".jpg,.jpeg,.png,.gif" />
              <div class="form-hint">Cette image sera utilisée comme photo de couverture de la capsule.</div>
            </div>

            <div class="mb-3">
              <label class="form-label-cv" for="open_date">Date d'ouverture</label>
              <input id="open_date" name="open_date" type="date" class="form-input-cv" />
            </div>

            <div class="mb-3">
              <label class="form-label-cv" for="invite_emails">Inviter des participants</label>
              <textarea id="invite_emails" name="invite_emails" class="form-input-cv" rows="4" placeholder="une@email.com, deux@email.com"></textarea>
              <div class="form-hint">Séparez les adresses par une virgule, un point-virgule ou une nouvelle ligne.</div>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a class="btn-filter text-decoration-none" href="capsules.php">Annuler</a>
              <button type="submit" class="btn-primary-cv">Créer la capsule</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>