<?php
require_once __DIR__ . '/../includes/app.php';
cv_require_admin();
$activePage = 'admin';

$currentUser = cv_current_user();
$currentUserId = (int)($currentUser['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_user_role') {
      $targetUserId = (int)($_POST['user_id'] ?? 0);
      $newRole = strtolower(trim((string)($_POST['role'] ?? 'user')));

      if ($targetUserId <= 0) {
        cv_flash_set('danger', 'Utilisateur invalide.');
      } elseif ($targetUserId === $currentUserId) {
        cv_flash_set('danger', 'Vous ne pouvez pas modifier votre propre rôle depuis cette page.');
      } elseif (!in_array($newRole, ['user', 'admin'], true)) {
        cv_flash_set('danger', 'Rôle invalide.');
      } else {
        $stmt = $pdo->prepare('UPDATE utilisateur SET role = :role WHERE Id_Utilisateur = :user_id');
        $stmt->execute([
          ':role' => $newRole,
          ':user_id' => $targetUserId,
        ]);

        cv_flash_set('success', 'Rôle utilisateur mis à jour.');
      }

      header('Location: admin.php');
      exit();
    }

    if ($action === 'toggle_user_status') {
      $targetUserId = (int)($_POST['user_id'] ?? 0);
      $newStatus = strtolower(trim((string)($_POST['status'] ?? 'actif')));

      if ($targetUserId <= 0) {
        cv_flash_set('danger', 'Utilisateur invalide.');
      } elseif ($targetUserId === $currentUserId) {
        cv_flash_set('danger', 'Vous ne pouvez pas désactiver votre propre compte depuis cette page.');
      } elseif (!in_array($newStatus, ['actif', 'inactif'], true)) {
        cv_flash_set('danger', 'Statut invalide.');
      } else {
        $stmt = $pdo->prepare('UPDATE utilisateur SET statut = :statut WHERE Id_Utilisateur = :user_id');
        $stmt->execute([
          ':statut' => $newStatus,
          ':user_id' => $targetUserId,
        ]);

        cv_flash_set('success', $newStatus === 'actif' ? 'Utilisateur activé.' : 'Utilisateur désactivé.');
      }

      header('Location: admin.php');
      exit();
    }

    if ($action === 'delete_user') {
        $targetUserId = (int)($_POST['user_id'] ?? 0);

        if ($targetUserId <= 0) {
            cv_flash_set('danger', 'Utilisateur invalide.');
        } elseif ($targetUserId === $currentUserId) {
            cv_flash_set('danger', 'Vous ne pouvez pas supprimer votre propre compte depuis cette page.');
        } else {
            $pdo->beginTransaction();

            try {
                $stmt = $pdo->prepare('SELECT Id_Capsule FROM capsule WHERE Id_Utilisateur = :user_id');
                $stmt->execute([':user_id' => $targetUserId]);
                $capsuleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if ($capsuleIds) {
                    $placeholders = implode(',', array_fill(0, count($capsuleIds), '?'));

                    $stmt = $pdo->prepare("DELETE FROM invitation WHERE Id_Capsule IN ($placeholders)");
                    $stmt->execute($capsuleIds);

                    $stmt = $pdo->prepare("DELETE FROM souvenir WHERE Id_Capsule IN ($placeholders)");
                    $stmt->execute($capsuleIds);

                    $stmt = $pdo->prepare("DELETE FROM capsule WHERE Id_Capsule IN ($placeholders)");
                    $stmt->execute($capsuleIds);
                }

                $stmt = $pdo->prepare('DELETE FROM invitation WHERE Id_Utilisateur = :user_id');
                $stmt->execute([':user_id' => $targetUserId]);

                $stmt = $pdo->prepare('DELETE FROM souvenir WHERE Id_Utilisateur = :user_id');
                $stmt->execute([':user_id' => $targetUserId]);

                $stmt = $pdo->prepare('DELETE FROM utilisateur WHERE Id_Utilisateur = :user_id');
                $stmt->execute([':user_id' => $targetUserId]);

                $pdo->commit();
                cv_flash_set('success', 'Utilisateur supprimé.');
            } catch (Throwable $e) {
                $pdo->rollBack();
                cv_flash_set('danger', 'Impossible de supprimer cet utilisateur.');
            }
        }

        header('Location: admin.php');
        exit();
    }

    if ($action === 'toggle_capsule_status') {
        $capsuleId = (int)($_POST['capsule_id'] ?? 0);

        if ($capsuleId <= 0) {
            cv_flash_set('danger', 'Capsule invalide.');
        } else {
        // Use `date_ouverture` to determine open/closed state (no statut_ouverture column)
        $stmt = $pdo->prepare('SELECT date_ouverture FROM capsule WHERE Id_Capsule = :capsule_id LIMIT 1');
        $stmt->execute([':capsule_id' => $capsuleId]);
        $currentDate = $stmt->fetchColumn();

        if ($currentDate === false) {
          cv_flash_set('danger', 'Capsule introuvable.');
        } else {
          $hasDate = !empty($currentDate);
          $isCurrentlyOpen = $hasDate && strtotime((string)$currentDate) <= time();

          if ($isCurrentlyOpen) {
            // currently open -> lock (clear opening date)
            $newDate = null;
          } else {
            // opening: preserve planned date if set, otherwise set to now
            $newDate = $hasDate ? $currentDate : cv_now();
          }

          $stmt = $pdo->prepare('UPDATE capsule SET date_ouverture = :date_ouverture WHERE Id_Capsule = :capsule_id');
          $stmt->execute([
            ':date_ouverture' => $newDate,
            ':capsule_id' => $capsuleId,
          ]);

          cv_flash_set('success', $isCurrentlyOpen ? 'Capsule verrouillée.' : 'Capsule ouverte.');
        }
        }

        header('Location: admin.php');
        exit();
    }

    if ($action === 'delete_capsule') {
        $capsuleId = (int)($_POST['capsule_id'] ?? 0);

        if ($capsuleId <= 0) {
            cv_flash_set('danger', 'Capsule invalide.');
        } else {
            $pdo->beginTransaction();

            try {
                $stmt = $pdo->prepare('DELETE FROM invitation WHERE Id_Capsule = :capsule_id');
                $stmt->execute([':capsule_id' => $capsuleId]);

                $stmt = $pdo->prepare('DELETE FROM souvenir WHERE Id_Capsule = :capsule_id');
                $stmt->execute([':capsule_id' => $capsuleId]);

                $stmt = $pdo->prepare('DELETE FROM capsule WHERE Id_Capsule = :capsule_id');
                $stmt->execute([':capsule_id' => $capsuleId]);

                $pdo->commit();
                cv_flash_set('success', 'Capsule supprimée.');
            } catch (Throwable $e) {
                $pdo->rollBack();
                cv_flash_set('danger', 'Impossible de supprimer cette capsule.');
            }
        }

        header('Location: admin.php');
        exit();
    }
}

$usersStmt = $pdo->query(
  'SELECT u.Id_Utilisateur,
      u.nom,
      u.email,
      u.role,
      u.statut,
      (SELECT COUNT(*) FROM capsule c WHERE c.Id_Utilisateur = u.Id_Utilisateur) AS created_capsules,
      (SELECT COUNT(*) FROM invitation i WHERE i.Id_Utilisateur = u.Id_Utilisateur) AS received_invitations,
      (SELECT COUNT(*) FROM invitation i WHERE i.Id_Utilisateur = u.Id_Utilisateur AND LOWER(i.statut) = "pending") AS pending_invitations
     FROM utilisateur u
    ORDER BY u.role DESC, u.nom ASC'
);
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

$capsulesStmt = $pdo->query(
    'SELECT c.Id_Capsule,
            c.titre,
            c.date_creation,
      c.date_ouverture,
            c.Id_Utilisateur,
            u.nom AS owner_name,
    (SELECT 1 + COUNT(*) FROM invitation i WHERE i.Id_Capsule = c.Id_Capsule AND i.statut = \'accepted\') AS member_count,
    (SELECT COUNT(*) FROM invitation i WHERE i.Id_Capsule = c.Id_Capsule) AS invitation_count,
            (SELECT COUNT(*) FROM souvenir s WHERE s.Id_Capsule = c.Id_Capsule) AS souvenir_count
       FROM capsule c
       INNER JOIN utilisateur u ON u.Id_Utilisateur = c.Id_Utilisateur
      ORDER BY c.date_creation DESC, c.Id_Capsule DESC'
);
$capsules = $capsulesStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/layout.php';
$flash = cv_flash_get();
?>

<header class="page-header">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
      <div class="page-title-bar">
        <div class="title-accent"></div>
        <h2 class="page-title">Administration</h2>
      </div>
      <p class="page-subtitle mb-0">Gérez les utilisateurs et les capsules sans afficher leur contenu.</p>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?php echo h($flash['type'] ?? 'info'); ?>" role="alert"><?php echo h($flash['message'] ?? ''); ?></div>
  <?php endif; ?>
</header>

<section class="cards-section">
  <div class="row g-4">
    <div class="col-12">
      <div class="capsule-card" style="height:auto;">
        <div class="card-body-cv">
          <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h3 class="card-title-cv mb-0">Utilisateurs</h3>
            <span class="card-desc mb-0"><?php echo count($users); ?> compte(s)</span>
          </div>

          <div class="table-responsive">
            <table class="table align-middle admin-table">
              <thead>
                <tr>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Rôle</th>
                  <th>Statut</th>
                  <th>Capsules créées</th>
                  <th>Invitations</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $adminUser): ?>
                  <tr>
                    <td><?php echo h((string)$adminUser['nom']); ?></td>
                    <td><?php echo h((string)$adminUser['email']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo h((string)($adminUser['role'] ?? 'user')); ?></span></td>
                    <td><span class="badge <?php echo ((string)$adminUser['statut'] === 'actif') ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo h((string)$adminUser['statut']); ?></span></td>
                    <td><?php echo (int)$adminUser['created_capsules']; ?></td>
                    <td><?php echo (int)$adminUser['received_invitations']; ?> dont <?php echo (int)$adminUser['pending_invitations']; ?> en attente</td>
                    <td class="text-end">
                      <div class="d-flex flex-column align-items-end gap-2">
                      <form method="post" class="d-flex justify-content-end" style="width: 170px;">
                        <input type="hidden" name="user_id" value="<?php echo (int)$adminUser['Id_Utilisateur']; ?>" />
                        <input type="hidden" name="action" value="update_user_role" />
                        <input type="hidden" name="role" value="<?php echo ((string)($adminUser['role'] ?? 'user') === 'admin') ? 'user' : 'admin'; ?>" />
                        <button type="submit" class="btn-filter w-100 justify-content-center">
                          <?php echo ((string)($adminUser['role'] ?? 'user') === 'admin') ? 'Retirer admin' : 'Promouvoir admin'; ?>
                        </button>
                      </form>
                      <form method="post" class="d-flex justify-content-end" style="width: 170px;">
                        <input type="hidden" name="user_id" value="<?php echo (int)$adminUser['Id_Utilisateur']; ?>" />
                        <input type="hidden" name="action" value="toggle_user_status" />
                        <input type="hidden" name="status" value="<?php echo ((string)$adminUser['statut'] === 'actif') ? 'inactif' : 'actif'; ?>" />
                        <button type="submit" class="btn-filter w-100 justify-content-center">
                          <?php echo ((string)$adminUser['statut'] === 'actif') ? 'Désactiver' : 'Activer'; ?>
                        </button>
                      </form>
                      <form method="post" class="d-flex justify-content-end" style="width: 170px;" onsubmit="return confirm('Supprimer cet utilisateur et ses données ?');">
                        <input type="hidden" name="user_id" value="<?php echo (int)$adminUser['Id_Utilisateur']; ?>" />
                        <input type="hidden" name="action" value="delete_user" />
                        <button type="submit" class="btn-delete w-100">Supprimer</button>
                      </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="capsule-card" style="height:auto;">
        <div class="card-body-cv">
          <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h3 class="card-title-cv mb-0">Capsules</h3>
            <span class="card-desc mb-0"><?php echo count($capsules); ?> capsule(s)</span>
          </div>

          <div class="table-responsive">
            <table class="table align-middle admin-table">
              <thead>
                <tr>
                  <th>Titre</th>
                  <th>Créateur</th>
                  <th>Créée le</th>
                  <th>Ouverture</th>
                  <th>État</th>
                  <th>Membres</th>
                  <th>Invitations</th>
                  <th>Souvenirs</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($capsules as $capsule): ?>
                  <?php
                    $isOpen = !empty($capsule['date_ouverture']) && strtotime((string)$capsule['date_ouverture']) <= time();
                    $createdAt = !empty($capsule['date_creation']) ? date('d/m/Y', strtotime((string)$capsule['date_creation'])) : 'N/A';
                    $openingAt = !empty($capsule['date_ouverture']) ? date('d/m/Y', strtotime((string)$capsule['date_ouverture'])) : 'N/A';
                  ?>
                  <tr>
                    <td><?php echo h((string)$capsule['titre']); ?></td>
                    <td><?php echo h((string)$capsule['owner_name']); ?></td>
                    <td><?php echo h($createdAt); ?></td>
                    <td><?php echo h($openingAt); ?></td>
                    <td><span class="badge <?php echo $isOpen ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo $isOpen ? 'Ouverte' : 'Verrouillée'; ?></span></td>
                    <td><?php echo (int)($capsule['member_count'] ?? 1); ?></td>
                    <td><?php echo (int)$capsule['invitation_count']; ?></td>
                    <td><?php echo (int)$capsule['souvenir_count']; ?></td>
                    <td class="text-end">
                      <div class="d-flex flex-column align-items-end gap-2">
                      <form method="post" class="d-flex justify-content-end" style="width: 170px;">
                        <input type="hidden" name="capsule_id" value="<?php echo (int)$capsule['Id_Capsule']; ?>" />
                        <input type="hidden" name="action" value="toggle_capsule_status" />
                        <button type="submit" class="btn-filter w-100 justify-content-center"><?php echo $isOpen ? 'Verrouiller' : 'Ouvrir'; ?></button>
                      </form>
                      <form method="post" class="d-flex justify-content-end" style="width: 170px;" onsubmit="return confirm('Supprimer cette capsule ?');">
                        <input type="hidden" name="capsule_id" value="<?php echo (int)$capsule['Id_Capsule']; ?>" />
                        <input type="hidden" name="action" value="delete_capsule" />
                        <button type="submit" class="btn-delete w-100">Supprimer</button>
                      </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>