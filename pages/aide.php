<?php
require_once __DIR__ . '/../includes/app.php';
cv_require_login();

$pageTitle = 'Aide | ChronoVault';
$activePage = 'aide';

require_once __DIR__ . '/../includes/layout.php';
?>

<header class="page-header">
  <div class="page-title-bar">
    <div class="title-accent"></div>
    <h2 class="page-title">Aide</h2>
  </div>
  <p class="page-subtitle mb-0">Guide rapide pour utiliser ChronoVault.</p>
</header>

<section class="cards-section">
  <div class="row g-4">
    <div class="col-12 col-lg-8">
      <div class="capsule-card" style="height:auto; min-height: 240px;">
        <div class="card-body-cv">
          <h3 class="card-title-cv">Premiers pas</h3>
          <p class="card-desc" style="-webkit-line-clamp:unset; line-clamp:unset;">
            1. Créez une capsule depuis "Nouvelle Capsule".<br>
            2. Choisissez une date d'ouverture.<br>
            3. Ajoutez vos souvenirs (messages ou photos).<br>
            4. Invitez des participants si nécessaire.
          </p>
          <h3 class="card-title-cv mt-3">Besoin d'aide supplémentaire ?</h3>
          <p class="card-desc" style="-webkit-line-clamp:unset; line-clamp:unset; margin-bottom: 0;">
            Contactez l'administrateur de la plateforme ou consultez la section Paramètres pour mettre à jour votre compte.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
