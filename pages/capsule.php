<?php
require_once __DIR__ . '/../includes/app.php';
cv_require_login();

$user = cv_current_user();
$userId = (int)($user['id'] ?? 0);
$userEmail = strtolower(trim((string)($user['email'] ?? '')));
$errors = [];

$capsuleId = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $capsuleId = (int)($_POST['capsule_id'] ?? $capsuleId);

    if ($action === 'add_souvenir') {
        $type = ($_POST['souvenir_type'] ?? 'message') === 'photo' ? 'photo' : 'message';
        $message = trim($_POST['souvenir_message'] ?? '');

        if ($type === 'message' && $message === '') {
          $errors[] = 'Le message est obligatoire.';
        } elseif ($type === 'photo' && empty($_FILES['souvenir_photo']['name'])) {
          $errors[] = 'Veuillez sélectionner une photo.';
        } else {
          // Permission check: only owner or invited users can post
          $allowed = cv_capsule_user_has_access($capsuleId, $userId);

          if (! $allowed) {
            $errors[] = 'Vous n\'avez pas la permission de poster dans cette capsule.';
          } else {
            try {
              $donnee = $message;

              if ($type === 'photo') {
                $donnee = cv_store_upload_file($_FILES['souvenir_photo'], 'images/uploads', 'souvenir');
              }

              cv_souvenir_create($capsuleId, $userId, $type, $donnee);

              cv_flash_set('success', $type === 'photo' ? 'Photo ajoutée à la capsule.' : 'Message ajouté à la capsule.');
              header('Location: capsule.php?id=' . $capsuleId);
              exit();
            } catch (Throwable $e) {
              $errors[] = $e->getMessage();
            }
          }
        }
    }

    if ($action === 'invite_member') {
        $inviteEmail = trim($_POST['invite_email'] ?? '');
      $inviteEmailLower = strtolower($inviteEmail);

        if ($inviteEmail === '') {
            $errors[] = 'L’adresse email est obligatoire.';
        } else {
            // Only capsule owner can invite members
            if (!cv_capsule_user_is_owner($capsuleId, $userId)) {
                $errors[] = 'Seul le créateur de la capsule peut inviter des membres.';
        } elseif ($inviteEmailLower === $userEmail) {
          $errors[] = 'Vous ne pouvez pas vous inviter vous-même.';
            } else {
              $invitee = cv_user_find_by_email($inviteEmail);

                if (!$invitee) {
                    $errors[] = 'Aucun utilisateur ne correspond à cet email.';
                } else {
                if (cv_invitation_exists_for_capsule($capsuleId, (int)$invitee['Id_Utilisateur'])) {
                        $errors[] = 'Cette personne est déjà invitée.';
                    } else {
                  cv_invitation_create($capsuleId, (int)$invitee['Id_Utilisateur']);

                        cv_flash_set('success', 'Invitation envoyée à ' . $inviteEmail . '.');
                        header('Location: capsule.php?id=' . $capsuleId);
                        exit();
                    }
                }
            }
        }
    }

    if ($action === 'delete_capsule') {
        // Get the capsule ID and verify ownership
        if (cv_capsule_user_is_owner($capsuleId, $userId)) {
            $pdo->beginTransaction();
            try {
              cv_capsule_delete_owned($capsuleId, $userId);

              $pdo->commit();
                cv_flash_set('success', 'Capsule supprimée.');
                header('Location: capsules.php');
                exit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                cv_flash_set('danger', 'Impossible de supprimer la capsule.');
                header('Location: capsule.php?id=' . $capsuleId);
                exit();
            }
        }
    }
}

if ($capsuleId <= 0) {
      $capsuleId = cv_capsule_get_first_accessible_for_user($userId);
}

if ($capsuleId <= 0) {
    cv_flash_set('warning', 'Aucune capsule disponible.');
    header('Location: capsules.php');
    exit();
}

$capsule = cv_capsule_get_by_id($capsuleId);

if (!$capsule) {
    cv_flash_set('danger', 'Capsule introuvable.');
    header('Location: capsules.php');
    exit();
}

$isOwner = (int)$capsule['Id_Utilisateur'] === $userId;

$hasInvitation = cv_capsule_user_has_access($capsuleId, $userId);

if (!$isOwner && !$hasInvitation) {
    cv_flash_set('danger', 'Vous n’avez pas accès à cette capsule.');
    header('Location: capsules.php');
    exit();
}

$isOpen = !empty($capsule['date_ouverture']) && strtotime((string)$capsule['date_ouverture']) <= time();

$souvenirs = [];
$memberList = [];
$memberList[] = [
  'name' => (string)$capsule['owner_name'],
  'email' => '',
  'status' => 'owner',
];

foreach (cv_capsule_get_members($capsuleId) as $member) {
  $memberList[] = [
    'name' => (string)$member['nom'],
    'email' => (string)$member['email'],
    'status' => (string)$member['statut'],
  ];
}

if ($isOpen) {
  $souvenirs = cv_capsule_get_souvenirs($capsuleId);
}

$memberCount = count($memberList);

$messageCount = 0;
$photoCount = 0;
foreach ($souvenirs as $souvenir) {
    if (($souvenir['type'] ?? '') === 'photo') {
        $photoCount++;
    } else {
        $messageCount++;
    }
}

require_once __DIR__ . '/../includes/layout.php';
$flash = cv_flash_get();
?>

<header class="page-header">
  <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
      <div class="page-title-bar">
        <div class="title-accent"></div>
        <h2 class="page-title"><?php echo h((string)$capsule['titre']); ?></h2>
      </div>
      <p class="page-subtitle mb-0"><?php echo h((string)($capsule['description'] ?: 'Aucune description')); ?></p>
    </div>
    <div class="d-flex gap-2">
      <a class="btn-filter text-decoration-none" href="capsules.php">Retour aux capsules</a>
      <?php if ($isOwner): ?>
      <form method="post" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette capsule ? Cette action ne peut pas être annulée.');">
        <input type="hidden" name="action" value="delete_capsule" />
        <input type="hidden" name="capsule_id" value="<?php echo (int)$capsuleId; ?>" />
        <button type="submit" class="btn-delete" aria-label="Supprimer la capsule" style="background: #dc3545; color: white; border: none; padding: 1.4rem 1.4rem; border-radius: 0.5rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.9rem;">
          <span class="material-symbols-outlined" style="font-size:16px">delete</span>
        </button>
      </form>
      <?php endif; ?>
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
  <div class="row g-4">
    <div class="col-12">
      <div class="capsule-card" style="height:auto; min-height: 180px;">
        <div class="card-img-wrapper">
          <img src="<?php echo h(!empty($capsule['photo']) ? '../' . ltrim((string)$capsule['photo'], '/') : '../images/default-capsule.jpg'); ?>" alt="<?php echo h((string)$capsule['titre']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div class="card-body-cv">
          <p class="card-category">Confidentiel jusqu'à l'ouverture</p>
          <h3 class="card-title-cv mb-2"><?php echo h((string)$capsule['titre']); ?></h3>
          <p class="card-desc" style="-webkit-line-clamp:unset; line-clamp:unset; margin-bottom:1rem;">
            Ouverture prévue le <?php echo h(!empty($capsule['date_ouverture']) ? date('d/m/Y', strtotime((string)$capsule['date_ouverture'])) : 'non définie'); ?>.
          </p>

          <div class="row g-3">
            <div class="col-12 col-md-4"><div class="capsule-card" style="height:auto; min-height: 120px;"><div class="card-body-cv"><p class="card-category">Date d'ouverture</p><h3 class="card-title-cv"><?php echo h(!empty($capsule['date_ouverture']) ? date('d/m/Y', strtotime((string)$capsule['date_ouverture'])) : 'Non définie'); ?></h3></div></div></div>
            <div class="col-12 col-md-4"><div class="capsule-card" style="height:auto; min-height: 120px;"><div class="card-body-cv"><p class="card-category">Membres</p><h3 class="card-title-cv"><?php echo $memberCount; ?></h3></div></div></div>
            <div class="col-12 col-md-4"><div class="capsule-card" style="height:auto; min-height: 120px;"><div class="card-body-cv"><p class="card-category">Contenu</p><h3 class="card-title-cv"><?php echo $messageCount; ?> message(s) · <?php echo $photoCount; ?> photo(s)</h3></div></div></div>
          </div>
        </div>
      </div>
    </div>

    <?php /* When capsule is open we show souvenirs in the left column (col-lg-7) so members appear on the right (col-lg-5) */ ?>

    <div class="col-12 col-lg-8">
      <?php if ($isOpen): ?>
      <div class="capsule-card" style="height:auto;">
        <div class="card-body-cv">
          <div class="d-flex justify-content-between align-items-center gap-3 mb-4 flex-wrap">
            <div>
              <h3 class="card-title-cv mb-1">Souvenirs de la capsule</h3>
              <p class="card-desc mb-0">Messages et photos disponibles depuis l'ouverture.</p>
            </div>
          </div>

          <?php if (!$souvenirs): ?>
            <p class="card-desc mb-0">Aucun souvenir n’a encore été ajouté.</p>
          <?php else: ?>
            <div class="row g-3">
              <?php foreach ($souvenirs as $souvenir): ?>
                <div class="col-12 col-md-6">
                  <div class="capsule-card souvenir-item-card" style="height:auto; min-height: 220px;">
                    <div class="card-body-cv d-flex flex-column">
                      <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <span class="souvenir-type-pill"><?php echo ($souvenir['type'] ?? '') === 'photo' ? 'Photo' : 'Message'; ?></span>
                      </div>

                      <?php if (($souvenir['type'] ?? '') === 'photo'): ?>
                        <div class="card-img-wrapper mb-3" style="height: 190px;">
                          <img src="<?php echo h('../' . ltrim((string)$souvenir['donnee'], '/')); ?>" alt="Souvenir photo" style="width:100%; height:100%; object-fit:cover;">
                        </div>
                      <?php else: ?>
                        <div class="souvenir-message-box">
                          <p class="souvenir-message-text"><?php echo h((string)$souvenir['donnee']); ?></p>
                        </div>
                      <?php endif; ?>

                      <div class="mt-auto">
                        <div class="open-label">Posté le</div>
                        <div class="open-value"><?php echo h(!empty($souvenir['date_poste']) ? date('d/m/Y H:i', strtotime((string)$souvenir['date_poste'])) : 'Non définie'); ?></div>
                        <div class="card-desc mb-0" style="-webkit-line-clamp: unset; line-clamp: unset; margin-top: 0.35rem;">
                          par <strong><?php echo h((string)($souvenir['auteur_nom'] ?? 'Utilisateur')); ?></strong>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php else: ?>
      <div class="capsule-card" style="height:auto;">
        <div class="card-body-cv">
          <h3 class="card-title-cv">Ajouter du contenu</h3>

          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_souvenir" />
            <input type="hidden" name="capsule_id" value="<?php echo (int)$capsuleId; ?>" />

            <div class="mb-3">
              <label class="form-label-cv" for="souvenir_type">Type de contenu</label>
              <select id="souvenir_type" name="souvenir_type" class="form-input-cv">
                <option value="message">Message</option>
                <option value="photo">Photo</option>
              </select>
            </div>

            <div class="mb-3" id="messageBlock">
              <label class="form-label-cv" for="souvenir_message">Message pour le futur</label>
              <textarea id="souvenir_message" name="souvenir_message" class="form-input-cv" rows="5" placeholder="Écrire un message pour le futur..."></textarea>
            </div>

            <div class="mb-3 d-none" id="photoBlock">
              <label class="form-label-cv" for="souvenir_photo">Photo</label>
              <input id="souvenir_photo" name="souvenir_photo" type="file" class="form-input-cv" accept=".jpg,.jpeg,.png,.gif" />
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn-primary-cv" id="souvenirSubmit">Ajouter le message</button>
            </div>
          </form>
        </div>
      </div>

      <div class="capsule-card mt-4" style="height:auto;">
        <div class="card-body-cv">
          <h3 class="card-title-cv">Inviter un membre</h3>
          <p class="card-desc">Invitez un utilisateur déjà inscrit à rejoindre cette capsule.</p>

          <?php if ($isOwner): ?>
          <form method="post">
            <input type="hidden" name="action" value="invite_member" />
            <input type="hidden" name="capsule_id" value="<?php echo (int)$capsuleId; ?>" />

            <div class="mb-3">
              <label class="form-label-cv" for="invite_email">Adresse email</label>
              <input id="invite_email" name="invite_email" type="email" class="form-input-cv" placeholder="ami@email.com" />
              <div class="form-text mt-2">Vous ne pouvez pas vous inviter vous-même.</div>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn-filter">Inviter</button>
            </div>
          </form>
          <?php else: ?>
            <p class="card-desc" style='color:red;'>Seul le créateur de la capsule peut inviter des membres.</p>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="col-12 col-lg-4">
      <div class="capsule-card" style="height:auto;">
        <div class="card-body-cv">
          <h3 class="card-title-cv">Membres de la capsule</h3>
          <p class="card-desc">Le propriétaire et les membres acceptés apparaissent ici.</p>

          <div class="d-flex flex-column gap-2 mb-4">
            <?php foreach ($memberList as $member): ?>
              <div class="d-flex justify-content-between align-items-center gap-3 pb-2 border-bottom">
                <div>
                  <strong><?php echo h((string)$member['name']); ?></strong>
                </div>
                <span class="badge-cv <?php echo ($member['status'] ?? '') === 'owner' ? 'badge-sealed' : 'badge-soon'; ?>">
                  <?php echo ($member['status'] ?? '') === 'owner' ? 'Propriétaire' : 'Membre'; ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<script>
  const typeSelect = document.getElementById('souvenir_type');
  const messageBlock = document.getElementById('messageBlock');
  const photoBlock = document.getElementById('photoBlock');
  const submitButton = document.getElementById('souvenirSubmit');

  typeSelect?.addEventListener('change', function () {
    const isPhoto = this.value === 'photo';
    messageBlock.classList.toggle('d-none', isPhoto);
    photoBlock.classList.toggle('d-none', !isPhoto);
    submitButton.textContent = isPhoto ? 'Ajouter la photo' : 'Ajouter le message';
  });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>