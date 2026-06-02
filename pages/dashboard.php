<?php
require_once __DIR__ . '/../includes/app.php';
cv_require_login();

$activePage = 'dashboard';
$user = cv_current_user();
$userId = (int)($user['id'] ?? 0);

$totalCapsules = cv_capsule_count_accessible_for_user($userId);
$openCapsules = cv_capsule_count_open_accessible_for_user($userId);
$pendingInvitations = cv_invitation_count_pending_for_user($userId);
$totalSouvenirs = cv_souvenir_count_for_user($userId);
$souvenirOpen = cv_souvenir_count_open_accessible_for_user($userId);

$activityRate = $totalCapsules > 0 ? (int)round(($openCapsules / $totalCapsules) * 100) : 0;

$lockedCapsules = (int)max(0, $totalCapsules - $openCapsules);

$recentCapsules = cv_capsule_recent_for_user($userId, 3);
$recentInvitations = cv_invitation_recent_pending_for_user($userId, 5);

require_once __DIR__ . '/../includes/layout.php';
?>

<header class="page-header">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
      <div class="page-title-bar">
        <div class="title-accent"></div>
        <h2 class="page-title">Dashboard</h2>
      </div>
      <p class="page-subtitle mb-0">Vue d’ensemble des capsules, souvenirs et invitations.</p>
    </div>
    <a class="btn-primary-cv text-decoration-none" href="new-capsule.php">Nouvelle capsule</a>
  </div>
</header>

<section class="cards-section">
  <div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
      <div class="capsule-card" style="height:auto; min-height: 170px;">
        <div class="card-body-cv">
          <p class="card-category">Capsules totales</p>
          <h3 class="card-title-cv stat-number"><?php echo $totalCapsules; ?></h3>
          <p class="card-desc">Vos capsules créées</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
      <div class="capsule-card" style="height:auto; min-height: 170px;">
        <div class="card-body-cv">
          <p class="card-category">Ouvertes</p>
          <h3 class="card-title-cv stat-number"><?php echo $openCapsules; ?></h3>
          <p class="card-desc">Capsules déjà accessibles</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
      <div class="capsule-card" style="height:auto; min-height: 170px;">
        <div class="card-body-cv">
          <p class="card-category">Invitations</p>
          <h3 class="card-title-cv stat-number"><?php echo $pendingInvitations; ?></h3>
          <p class="card-desc">En attente de réponse</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
      <div class="capsule-card" style="height:auto; min-height: 170px;">
        <div class="card-body-cv">
          <p class="card-category">Activité</p>
          <h3 class="card-title-cv stat-number"><?php echo $activityRate; ?>%</h3>
          <p class="card-desc">Capsules accessibles</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-12">
      <div class="capsule-card" style="height:auto; min-height: 240px;">
        <div class="card-body-cv">
          <div class="d-flex justify-content-between align-items-center gap-3 mb-3 flex-wrap">
            <h3 class="card-title-cv mb-0">Statistiques des Capsules</h3>
            <p class="card-desc mb-0">Vue rapide des capsules ouvertes, fermées et des souvenirs.</p>
          </div>
          <div style="max-width: 520px; margin: 0 auto;">
            <canvas id="statsChart" style="max-height: 210px;"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-lg-7">
      <div class="capsule-card" style="height:auto; min-height: 320px;">
        <div class="card-body-cv">
          <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h3 class="card-title-cv mb-0">Capsules récentes</h3>
            <a class="btn-filter text-decoration-none" href="capsules.php">Voir toutes</a>
          </div>

          <?php if (!$recentCapsules): ?>
            <p class="card-desc mb-0">Aucune capsule pour le moment. Créez votre première capsule pour commencer.</p>
          <?php else: ?>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($recentCapsules as $capsule): ?>
                <?php
                  $openDate = !empty($capsule['date_ouverture']) ? date('d/m/Y', strtotime((string)$capsule['date_ouverture'])) : 'Non définie';
                  $isOpen = !empty($capsule['date_ouverture']) && strtotime((string)$capsule['date_ouverture']) <= time();
                ?>
                <div class="d-flex justify-content-between align-items-center gap-3 pb-3 border-bottom">
                  <div>
                    <strong><?php echo h((string)$capsule['titre']); ?></strong>
                    <div class="card-desc mb-0" style="-webkit-line-clamp:1; line-clamp:1; margin-bottom:0;">
                      <?php echo h((string)($capsule['description'] ?: 'Aucune description')); ?>
                    </div>
                  </div>
                  <div class="text-end">
                    <span class="badge-cv <?php echo $isOpen ? 'badge-sealed' : 'badge-soon'; ?>">
                      <?php echo $isOpen ? 'Ouverte' : 'Verrouillée'; ?>
                    </span>
                    <div class="card-desc mb-0 mt-1" style="-webkit-line-clamp:1; line-clamp:1; margin-bottom:0;">
                      Ouverture: <?php echo h($openDate); ?> · <?php echo (int)$capsule['souvenir_count']; ?> souvenir(s)
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="capsule-card" style="height:auto; min-height: 320px;">
        <div class="card-body-cv">
          <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h3 class="card-title-cv mb-0">Invitations en attente</h3>
            <a class="btn btn-link p-0 text-decoration-none" href="invitations.php" style="color: var(--color-primary); font-weight: 700;">Gérer</a>
          </div>

          <?php if (!$recentInvitations): ?>
            <p class="card-desc mb-0">Aucune invitation en attente pour le moment.</p>
          <?php else: ?>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($recentInvitations as $invitation): ?>
                <div class="d-flex justify-content-between align-items-center gap-3 pb-3 border-bottom">
                  <div>
                    <strong><?php echo h((string)$invitation['titre']); ?></strong>
                    <div class="card-desc mb-0" style="-webkit-line-clamp:1; line-clamp:1; margin-bottom:0;">
                      Par <?php echo h((string)$invitation['owner_name']); ?> · <?php echo h(date('d/m/Y', strtotime((string)$invitation['date_envoi']))); ?>
                    </div>
                  </div>
                  <span class="badge-cv <?php echo $invitation['statut'] === 'accepted' ? 'badge-sealed' : ($invitation['statut'] === 'declined' ? 'badge-draft' : 'badge-soon'); ?>">
                    <?php echo h(ucfirst((string)$invitation['statut'])); ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('statsChart');
  if (ctx) {
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Capsules ouvertes', 'Capsules verrouillées'],
        datasets: [{
          data: [<?php echo (int)$openCapsules; ?>, <?php echo (int)$lockedCapsules; ?>],
          backgroundColor: [
            'rgba(99, 102, 241, 0.9)',
            'rgba(239, 68, 68, 0.9)'
          ],
          borderColor: [
            'rgba(99, 102, 241, 1)',
            'rgba(239, 68, 68, 1)'
          ],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              font: { size: 14, family: "'Plus Jakarta Sans', sans-serif" },
              color: '#333'
            }
          },
          title: {
            display: true,
            text: 'Statistiques des Capsules',
            font: { size: 18, weight: 'bold', family: "'Plus Jakarta Sans', sans-serif" }
          }
        }
      }
    });
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>