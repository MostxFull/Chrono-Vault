<?php
require_once __DIR__ . '/../includes/app.php';
cv_require_login();

$activePage = 'invitations';

$user = cv_current_user();
$userId = (int)($user['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $invitationId = (int)($_POST['invitation_id'] ?? 0);

    if (in_array($action, ['accept', 'decline'], true)) {
        $status = $action === 'accept' ? 'accepted' : 'declined';
      cv_invitation_update_status_for_user($invitationId, $userId, $status);

        cv_flash_set('success', $status === 'accepted' ? 'Invitation acceptée.' : 'Invitation refusée.');
    }

    header('Location: invitations.php');
    exit();
}

$invitations = cv_invitation_recent_pending_for_user($userId, 100);

require_once __DIR__ . '/../includes/layout.php';
$flash = cv_flash_get();
?>

<header class="page-header">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
      <div class="page-title-bar">
        <div class="title-accent"></div>
        <h2 class="page-title">Invitations en attente</h2>
      </div>
      <p class="page-subtitle mb-0">Répondez aux invitations reçues pour rejoindre des capsules partagées.</p>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?php echo h($flash['type'] ?? 'info'); ?>" role="alert"><?php echo h($flash['message'] ?? ''); ?></div>
  <?php endif; ?>
</header>

<section class="cards-section">
  <div class="capsule-card" style="height:auto; min-height: 220px;">
    <div class="card-body-cv">
      <h3 class="card-title-cv mb-4">Boîte de réception</h3>

      <?php if (!$invitations): ?>
        <p class="card-desc mb-0">Aucune invitation en attente pour le moment.</p>
      <?php else: ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($invitations as $invitation): ?>
            <?php
              $status = (string)$invitation['statut'];
              $badgeClass = 'badge-soon';
              $openDate = !empty($invitation['date_ouverture']) ? date('d/m/Y', strtotime((string)$invitation['date_ouverture'])) : 'Non définie';
            ?>
            <div class="d-flex justify-content-between align-items-center gap-3 pb-3 border-bottom flex-wrap">
              <div>
                <strong><?php echo h((string)$invitation['titre']); ?></strong>
                <div class="card-desc mb-0" style="-webkit-line-clamp:1; line-clamp:1; margin-bottom:0;">
                  Par <?php echo h((string)$invitation['owner_name']); ?> · ouverture <?php echo h($openDate); ?>
                </div>
              </div>

              <div class="d-flex flex-wrap justify-content-end gap-2 align-items-center">
                <span class="badge-cv <?php echo $badgeClass; ?>"><?php echo h(ucfirst($status)); ?></span>
                <form method="post" class="d-inline">
                  <input type="hidden" name="action" value="accept" />
                  <input type="hidden" name="invitation_id" value="<?php echo (int)$invitation['Id_Invitation']; ?>" />
                  <button class="btn-primary-cv" type="submit">Accepter</button>
                </form>
                <form method="post" class="d-inline">
                  <input type="hidden" name="action" value="decline" />
                  <input type="hidden" name="invitation_id" value="<?php echo (int)$invitation['Id_Invitation']; ?>" />
                  <button class="btn-filter" type="submit">Refuser</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>