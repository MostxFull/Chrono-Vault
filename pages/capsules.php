<?php
require_once __DIR__ . '/../includes/app.php';
cv_require_login();

$activePage = 'capsules';
$user = cv_current_user();
$userId = (int)($user['id'] ?? 0);

$stmt = $pdo->prepare(
        'SELECT DISTINCT c.Id_Capsule, c.titre, c.description, c.date_creation, c.date_ouverture, c.photo, c.Id_Utilisateur,
          (SELECT COUNT(*) FROM souvenir s WHERE s.Id_Capsule = c.Id_Capsule) AS souvenir_count,
          (SELECT COUNT(*) FROM invitation i WHERE i.Id_Capsule = c.Id_Capsule) AS invitation_count
         FROM capsule c
        LEFT JOIN invitation inv ON c.Id_Capsule = inv.Id_Capsule AND inv.Id_Utilisateur = ?
         WHERE c.Id_Utilisateur = ? OR inv.Id_Utilisateur = ?  AND inv.statut = \'accepted\'
         ORDER BY c.date_creation DESC, c.Id_Capsule DESC'
);
$stmt->execute([$userId, $userId, $userId]);
$capsules = $stmt->fetchAll();

require_once __DIR__ . '/../includes/layout.php';
$flash = cv_flash_get();
?>

<header class="page-header">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
      <div class="page-title-bar">
        <div class="title-accent"></div>
        <h2 class="page-title">Mes Capsules</h2>
      </div>
      <p class="page-subtitle mb-0">Gérez vos souvenirs et vos capsules temporelles.</p>
    </div>
    <a class="btn-primary-cv text-decoration-none" href="new-capsule.php">Nouvelle capsule</a>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?php echo h($flash['type'] ?? 'info'); ?>" role="alert"><?php echo h($flash['message'] ?? ''); ?></div>
  <?php endif; ?>
</header>

<section class="cards-section">
  <?php if (!$capsules): ?>
    <div class="capsule-card" style="height:auto; min-height: 180px;">
      <div class="card-body-cv d-flex align-items-center justify-content-center text-center">
        <div>
          <h3 class="card-title-cv">Aucune capsule pour le moment</h3>
          <p class="card-desc mb-4">Créez votre première capsule pour commencer à stocker vos souvenirs.</p>
          <a class="btn-primary-cv text-decoration-none" href="new-capsule.php">Créer une capsule</a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($capsules as $capsule): ?>
        <?php
          $isOpen = !empty($capsule['date_ouverture']) && strtotime((string)$capsule['date_ouverture']) <= time();
          $openDate = !empty($capsule['date_ouverture']) ? date('d/m/Y', strtotime((string)$capsule['date_ouverture'])) : 'Non définie';
        ?>
        <div class="col-12 col-md-6 col-lg-4">
          <a href="capsule.php?id=<?php echo (int)$capsule['Id_Capsule']; ?>" class="text-decoration-none" style="display: block; height: 100%;">
            <div class="capsule-card" style="height:auto; min-height: 280px; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)';" onmouseout="this.style.transform='translateY(0)';">
              <div class="card-img-wrapper">
                <img src="<?php echo h(!empty($capsule['photo']) ? '../' . ltrim((string)$capsule['photo'], '/') : '../images/default-capsule.jpg'); ?>" alt="<?php echo h((string)$capsule['titre']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
              <div class="card-body-cv d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                  <p class="card-category mb-0"><?php echo $isOpen ? 'Ouverte' : 'Verrouillée'; ?></p>
                  <span class="badge-cv <?php echo $isOpen ? 'badge-sealed' : 'badge-soon'; ?>"><?php echo $isOpen ? 'Accessible' : 'En attente'; ?></span>
                </div>

                <h3 class="card-title-cv"><?php echo h((string)$capsule['titre']); ?></h3>
                <p class="card-desc"><?php echo h((string)($capsule['description'] ?: 'Aucune description')); ?></p>

                <div class="mb-3">
                  <div class="open-label">Date d'ouverture</div>
                  <div class="open-value"><?php echo h($openDate); ?></div>
                </div>

                <div class="card-desc mb-0 mt-auto" style="-webkit-line-clamp:1; line-clamp:1; margin-bottom:0;">
                  <?php echo (int)$capsule['souvenir_count']; ?> souvenir(s) · <?php echo (int)$capsule['invitation_count']; ?> invitation(s)
                </div>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>