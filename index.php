<?php
require_once __DIR__ . '/includes/app.php';

cv_redirect_if_logged_in('pages/dashboard.php');

if (cv_try_remember_login($pdo)) {
    header('Location: pages/dashboard.php');
    exit();
}
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ChronoVault</title>
	<link rel="icon" type="image/x-icon" href="https://chronovault.infinityfree.me/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>" />
  </head>

  <body>

    <nav class="navbar navbar-expand-lg navbar-custom fixed-top py-3">
      <div class="container">
        <a class="navbar-brand brand" href="#">ChronoVault</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
          <ul class="navbar-nav mx-auto gap-lg-4">
            <li class="nav-item"><a class="nav-link fw-bold text-dark" href="#">Accueil</a></li>
            <li class="nav-item"><a class="nav-link" href="#features">Fonctionnalités</a></li>
            <li class="nav-item"><a class="nav-link" href="#how">Comment ça marche</a></li>
            <li class="nav-item"><a class="nav-link" href="#testimonials">Témoignages</a></li>
          </ul>
          <div>
            <a href="pages/login.php" class="btn btn-chrono">Commencer</a>
          </div>
        </div>
      </div>
    </nav>

    <section class="hero">
      <div class="floating-shapes" aria-hidden="true">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
      </div>
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <span class="hero-badge">The Timeless Curator</span>
            <h1 class="hero-title mt-4">ChronoVault : Vos souvenirs, à l'abri du temps.</h1>
            <p class="hero-text mt-4">
              Sanctuaire numérique pour vos moments les plus précieux. Archivez
              aujourd'hui vos émotions et transmettez-les en toute sécurité à l'avenir.
            </p>
            <div class="d-flex flex-wrap gap-3 mt-5">
              <a href="pages/signup.php" class="btn btn-chrono">Créer une capsule</a>
              <a href="#features" class="btn btn-outline-chrono">En savoir plus</a>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="hero-illustration">
              <div class="capsule-3d">
                <div class="capsule-body">
                  <div class="capsule-icon">
                    <i class="bi bi-hourglass-split"></i>
                  </div>
                  <div class="capsule-glow"></div>
                  <div class="capsule-ring ring-1"></div>
                  <div class="capsule-ring ring-2"></div>
                  <div class="capsule-ring ring-3"></div>
                </div>
                <div class="floating-item item-1">
                  <i class="bi bi-image"></i>
                </div>
                <div class="floating-item item-2">
                  <i class="bi bi-chat-heart"></i>
                </div>
                <div class="floating-item item-3">
                  <i class="bi bi-lock-fill"></i>
                </div>
                <div class="floating-item item-4">
                  <i class="bi bi-people-fill"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-5" id="features">
      <div class="container py-5">
        <div class="text-center mb-5">
          <h2 class="section-title">Pourquoi choisir ChronoVault ?</h2>
          <p class="text-muted mt-3">La technologie au service de vos souvenirs.</p>
        </div>
        <div class="row g-4">
          <div class="col-md-6 col-lg-4">
            <div class="feature-card">
              <div class="feature-icon"><span class="material-symbols-outlined">update</span></div>
              <h4>Création différée</h4>
              <p class="text-muted mt-3">Préparez vos messages aujourd'hui pour les découvrir plus tard.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="feature-card">
              <div class="feature-icon"><span class="material-symbols-outlined">photo_library</span></div>
              <h4>Photos & Messages</h4>
              <p class="text-muted mt-3">Associez émotions, images et souvenirs précieux.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="feature-card">
              <div class="feature-icon"><span class="material-symbols-outlined">verified_user</span></div>
              <h4>Verrouillage sécurisé</h4>
              <p class="text-muted mt-3">Vos données sont protégées jusqu'à la date choisie.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-5 bg-light" id="how">
      <div class="container py-5">
        <div class="text-center mb-5">
          <h2 class="section-title">Comment ça marche ?</h2>
        </div>
        <div class="row g-5 text-center">
          <div class="col-md-4">
            <div class="step-circle">1</div>
            <h4>Créer</h4>
            <p class="text-muted mt-3">Ouvrez une nouvelle capsule temporelle.</p>
          </div>
          <div class="col-md-4">
            <div class="step-circle">2</div>
            <h4>Ajouter du contenu</h4>
            <p class="text-muted mt-3">Ajoutez photos, vidéos et messages.</p>
          </div>
          <div class="col-md-4">
            <div class="step-circle">3</div>
            <h4>Découvrir</h4>
            <p class="text-muted mt-3">Redécouvrez vos souvenirs plus tard.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-5" id="testimonials">
      <div class="container py-5">
        <div class="text-center mb-5">
          <h2 class="section-title">Ils nous font confiance</h2>
        </div>
        <div class="row g-4">
          <div class="col-lg-4">
            <div class="testimonial">
              <p class="fst-italic">"Une expérience magnifique pour préserver nos souvenirs."</p>
              <h6 class="mt-4 mb-0 fw-bold">Yassir A.</h6>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="testimonial testimonial-primary">
              <p class="fst-italic">"Le plus beau cadeau émotionnel que nous ayons créé."</p>
              <h6 class="mt-4 mb-0 fw-bold">Karim E.</h6>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="testimonial">
              <p class="fst-italic">"Simple, élégant et très sécurisé."</p>
              <h6 class="mt-4 mb-0 fw-bold">Collectif Mostxfull</h6>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-5">
      <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
          <div>
            <h2 class="section-title">Galerie de souvenirs</h2>
            <p class="text-muted">Capsules en attente d'ouverture.</p>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-sm-6 col-lg-3"><div class="gallery-card"><img src="images/chrono-vault-index-2.jpg" alt="" /></div></div>
          <div class="col-sm-6 col-lg-3"><div class="gallery-card"><img src="images/chrono-vault-index-3.jpg" alt="" /></div></div>
          <div class="col-sm-6 col-lg-3"><div class="gallery-card"><img src="images/chrono-vault-index-4.jpg" alt="" /></div></div>
          <div class="col-sm-6 col-lg-3"><div class="gallery-card"><img src="images/chrono-vault-index-5.jpg" alt="" /></div></div>
        </div>
      </div>
    </section>

    <footer class="py-5">
      <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-4">
          <p class="small text-muted mb-0">© 2026 ChronoVault — Tous droits réservés.</p>
          <div class="d-flex gap-4">
            <a href="#">Mentions légales</a>
            <a href="#">Confidentialité</a>
            <a href="#">Contact</a>
          </div>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>