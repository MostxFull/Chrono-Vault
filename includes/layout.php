<?php
// Ce fichier contient la structure HTML globale de l'application (header, sidebar, footer) 
// et les styles communs. Les pages spécifiques sont incluses à l'intérieur de ce layout.

$pageTitle = $pageTitle ?? 'ChronoVault';
$activePage = $activePage ?? '';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$sidebarUserName = trim((string)($_SESSION['user']['name'] ?? 'Utilisateur'));
if ($sidebarUserName === '') {
  $sidebarUserName = 'Utilisateur';
}

$sidebarUserEmail = trim((string)($_SESSION['user']['email'] ?? ''));

$sidebarUserAvatarAlt = $sidebarUserName;
if ($sidebarUserEmail !== '') {
  $sidebarUserAvatarAlt .= ' (' . $sidebarUserEmail . ')';
}

$sidebarIsAdmin = false;
$sidebarUserStatut = strtolower(trim((string)($_SESSION['user']['statut'] ?? '')));
$sidebarUserRole = strtolower(trim((string)($_SESSION['user']['role'] ?? '')));
if ($sidebarUserRole === 'admin' && $sidebarUserStatut === 'actif') {
  $sidebarIsAdmin = true;
}

// Pending invitations count for the current user (used in the sidebar nav)
$pendingInvitations = 0;
if (!empty($_SESSION['user']['id'])) {
  $pendingInvitations = cv_invitation_count_pending_for_user((int)$_SESSION['user']['id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
	<link rel="icon" type="image/x-icon" href="https://chronovault.infinityfree.me/favicon.svg">
  <!-- Bootstrap 5 -->
  <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet"/>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

  <!-- Theme variables -->
  <link href="../css/variables.css" rel="stylesheet"/>
<script src="../js/themes.js"></script>

  <style>
    :root {
      --ui-title-size: clamp(1.75rem, 2.4vw, 2.25rem);
      --ui-card-title-size: 1.05rem;
      --ui-card-image-height: 196px;
      --ui-btn-radius: 0.5rem;
      --ui-btn-transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.1s;
    }

    *, *::before, *::after { box-sizing: border-box; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
      color: var(--color-on-surface);
      min-height: 100vh;
      display: flex;
      align-items: stretch;
      margin: 0;
    }
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      font-size: 20px;
      vertical-align: middle;
      line-height: 1;
    }

    #sidebar {
      width: 248px;
      min-height: 100vh;
    background: var(--color-surface-container);
      border-right: 1px solid var(--color-outline-variant);
      display: flex;
      flex-direction: column;
      padding: 1.25rem 1rem;
      position: sticky;
      top: 0;
      flex-shrink: 0;
      backdrop-filter: blur(10px);
    }
    #sidebar .brand-name {
      font-size: 1.05rem;
      font-weight: 900;
      color: var(--color-primary);
      letter-spacing: -0.04em;
    }
    .admin-table thead th,
  .admin-table tbody td {
    background: transparent;
  }

    #sidebar .brand-sub {
      font-size: 0.65rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.14em;
      color: var(--color-outline);
      margin-top: 2px;
    }
    #sidebar nav {
      padding-top: 0.5rem;
    }
    .nav-link-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.8rem 0.9rem;
      border-radius: 0.8rem;
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--color-on-surface-variant);
      text-decoration: none;
      transition: background 0.15s, color 0.15s, transform 0.15s;
    }
    .nav-link-item:hover {
      background: var(--color-surface-container);
      color: var(--color-on-surface);
      transform: translateX(2px);
    }
    .nav-link-item.active {
      background: var(--color-secondary-container);
      color: var(--color-on-secondary-container);
      font-weight: 600;
    }
    .nav-link-item.active .material-symbols-outlined {
      font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
    }
    .sidebar-divider {
      border-top: 1px solid var(--color-outline-variant);
      margin: 0.75rem 0;
    }
    .user-profile {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 0.9rem;
      margin-top: 0.5rem;
      border-radius: 0.85rem;
      background: var(--color-surface-container);
    }
    .user-avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      object-fit: cover;
    }
    .user-name {
      font-size: 0.875rem;
      font-weight: 700;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    #main {
      flex: 1;
      min-width: 0;
      background: var(--color-surface-bright);
      display: flex;
      flex-direction: column;
    }

    .main-content {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 1.75rem 1.5rem 2rem;
      flex: 1;
    }

    .page-header {
      padding: 0;
      margin-bottom: 1.5rem;
    }
    .page-title-bar {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.4rem;
    }
    .title-accent {
      width: 4px;
      height: 2rem;
      background: var(--color-tertiary-fixed-dim);
      border-radius: 2px;
    }
    .page-title {
      font-size: var(--ui-title-size);
      font-weight: 800;
      letter-spacing: -0.03em;
      color: var(--color-on-surface);
      margin: 0;
      line-height: 1.15;
    }
    .page-subtitle {
      color: var(--color-on-surface-variant);
      font-weight: 500;
      font-size: 0.95rem;
    }
    .btn-primary-cv {
      background: var(--color-primary);
      color: #fff;
      border: none;
      padding: 0.7rem 1.4rem;
      border-radius: var(--ui-btn-radius);
      font-weight: 700;
      font-size: 0.9rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      box-shadow: none;
      transition: var(--ui-btn-transition);
      cursor: pointer;
      white-space: nowrap;
    }
    .btn-primary-cv:hover {
      background: var(--color-primary-container);
      color: #fff;
      transform: translateY(-1px);
    }
    .btn-primary-cv:active { transform: scale(0.97); }

    .filter-bar {
      background: var(--color-surface-container-low);
      border-radius: 0.75rem;
      padding: 1rem;
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      align-items: center;
    }
    .search-wrapper {
      flex: 1;
      position: relative;
      min-width: 180px;
    }
    .search-wrapper .material-symbols-outlined {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--color-on-surface-variant);
    }
    .search-input {
      width: 100%;
      background: var(--color-surface-container-highest);
      border: none;
      border-radius: 0.75rem;
      padding: 0.7rem 1rem 0.7rem 2.8rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.875rem;
      color: var(--color-on-surface);
      outline: none;
      transition: box-shadow 0.2s;
    }
    .search-input::placeholder { color: var(--color-outline); }
    .search-input:focus { box-shadow: 0 0 0 2px rgba(108,47,0,0.18); }
    .btn-filter {
      background: var(--color-surface-container-highest);
      border: 1px solid var(--color-outline-variant);
      border-radius: var(--ui-btn-radius);
      padding: 0.7rem 1rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.8rem;
      font-weight: 700;
      color: var(--color-on-surface);
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      cursor: pointer;
      transition: var(--ui-btn-transition);
    }
    .btn-filter:hover {
      background: var(--color-secondary-container);
      color: var(--color-on-secondary-container);
      border-color: var(--color-secondary-container);
      transform: translateY(-1px);
    }

    .cards-section {
      padding: 0;
    }
    .capsule-card {
      background: var(--color-surface-container-low);
      border-radius: 0.75rem;
      overflow: hidden;
      height: 420px;
      display: flex;
      flex-direction: column;
      transition: box-shadow 0.3s, transform 0.3s;
    }
   
    .capsule-card.draft {
      border: 2px dashed rgba(218,194,182,0.5);
    }
    .card-img-wrapper {
      height: var(--ui-card-image-height);
      overflow: hidden;
      position: relative;
      flex-shrink: 0;
      background: var(--color-surface-container-high);
    }
    .card-img-wrapper.muted { opacity: 0.6; }
    .card-img-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .card-badges {
      position: absolute;
      top: 1rem;
      right: 1rem;
      display: flex;
      gap: 0.4rem;
    }
    .badge-cv {
      padding: 0.25rem 0.7rem;
      border-radius: 9999px;
      font-size: 0.6rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .badge-sealed   { background: var(--color-tertiary-fixed-dim); color: var(--color-on-tertiary-fixed); }
    .badge-soon     { background: var(--color-tertiary-fixed-dim); color: var(--color-on-tertiary-fixed); }
    .badge-draft    { background: var(--color-surface-container-highest); color: var(--color-on-surface-variant); }
    .badge-shared   { background: var(--color-secondary-container); color: var(--color-on-secondary-container); }

    .card-body-cv {
      padding: 1.4rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .card-category {
      font-size: 0.6rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: var(--color-secondary);
      margin-bottom: 0.25rem;
    }
    .card-category.muted { color: var(--color-outline); }
    .card-title-cv {
      font-size: var(--ui-card-title-size);
      font-weight: 700;
      margin-bottom: 0.4rem;
      color: var(--color-on-surface);
      line-height: 1.3;
    }
    .card-title-cv.muted-title { color: var(--color-on-surface-variant); }
    .stat-number {
      font-size: 4rem;
      font-weight: 900;
      line-height: 1;
      margin-top: 0.6rem;
      margin-bottom: 0.25rem;
    }
    .card-desc {
      font-size: 0.82rem;
      color: var(--color-on-surface-variant);
      line-clamp: 2;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      margin-bottom: 1.25rem;
    }
    .card-footer-cv {
      margin-top: auto;
      padding-top: 1rem;
      border-top: 1px solid rgba(218,194,182,0.15);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .card-footer-cv.no-border { border: none; }
    .open-label {
      font-size: 0.58rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--color-outline);
    }
    .open-value {
      font-size: 1.05rem;
      font-weight: 900;
      color: var(--color-tertiary-fixed-dim);
      line-height: 1.2;
    }
    .avatar-stack {
      display: flex;
      flex-direction: row-reverse;
    }
    .avatar-stack img,
    .avatar-stack .avatar-count {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 2px solid var(--color-surface-container-low);
      margin-left: -10px;
      object-fit: cover;
    }
    .avatar-count {
      background: var(--color-surface-container-highest);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.6rem;
      font-weight: 700;
      color: var(--color-on-surface-variant);
    }
    .avatar-solo {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 2px solid var(--color-surface-container-low);
      background: var(--color-surface-container-highest);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .btn-continue {
      flex: 1;
      padding: 0.5rem;
      background: var(--color-surface-container-highest);
      border: 1px solid var(--color-outline-variant);
      border-radius: var(--ui-btn-radius);
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.8rem;
      font-weight: 700;
      color: var(--color-on-surface);
      cursor: pointer;
      transition: var(--ui-btn-transition);
    }
    .btn-continue:hover {
      background: var(--color-secondary-container);
      color: var(--color-on-secondary-container);
      border-color: var(--color-secondary-container);
      transform: translateY(-1px);
    }
    .btn-delete {
      width: 38px;
      height: 38px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      background: transparent;
      border-radius: 0.5rem;
      cursor: pointer;
      color: var(--color-on-surface-variant);
      transition: background 0.15s, color 0.15s;
    }
    .btn-delete:hover {
      background: var(--color-error-container);
      color: var(--color-error);
    }

    .back-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--color-on-surface-variant);
      padding: 0;
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.85rem;
      font-weight: 600;
      transition: color 0.15s;
    }
    .back-btn:hover { color: var(--color-primary); }

    .stepper {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.75rem;
      margin: 2rem auto 0;
      width: 100%;
      max-width: 680px;
    }
    .step-item {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      flex: 0 0 auto;
    }
    .step-circle {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8rem;
      font-weight: 700;
      background: var(--color-surface-container-highest);
      color: var(--color-on-surface-variant);
      border: 2px solid var(--color-outline-variant);
      flex-shrink: 0;
      transition: all 0.3s;
    }
    .step-circle.active,
    .step-circle.done {
      background: var(--color-tertiary-fixed-dim);
      color: var(--color-on-tertiary-fixed);
      border-color: var(--color-tertiary-fixed-dim);
    }
    .step-label {
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--color-on-surface-variant);
      white-space: nowrap;
    }
    .step-label.active { color: var(--color-on-surface); }
    .step-connector {
      flex: 1 1 72px;
      min-width: 56px;
      height: 2px;
      background: var(--color-outline-variant);
      transition: background 0.3s;
    }
    .step-connector.done { background: var(--color-tertiary-fixed-dim); }

    .step-content {
      padding: 0;
      display: flex;
      justify-content: center;
    }

    .form-label-cv {
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--color-on-surface);
      margin-bottom: 0.4rem;
      display: block;
    }
    .form-input-cv {
      width: 100%;
      background: var(--color-surface-container-highest);
      border: 1.5px solid var(--color-outline-variant);
      border-radius: 0.5rem;
      padding: 0.7rem 0.9rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.875rem;
      color: var(--color-on-surface);
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-input-cv::placeholder { color: var(--color-outline); }
    .form-input-cv:focus {
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px rgba(108,47,0,0.1);
    }
    textarea.form-input-cv { resize: vertical; min-height: 110px; }

    .souvenir-item-card {
    border: 1px solid var(--color-outline-variant);
  }

  .souvenir-message-box {
    background: var(--color-surface-container-high);
    border: 1px solid var(--color-outline-variant);
    border-radius: 0.65rem;
    padding: 0.95rem;
    margin-bottom: 1rem;
  }

  .souvenir-message-text {
    margin: 0;
    white-space: pre-wrap;
    line-height: 1.6;
    color: var(--color-on-surface);
    font-size: 0.9rem;
    -webkit-line-clamp: unset;
    line-clamp: unset;
  }

  .souvenir-type-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    background: var(--color-surface-container-high);
    color: var(--color-on-surface-variant);
    border: 1px solid var(--color-outline-variant);
  }
    .input-icon-wrap { position: relative; }
    .input-icon-wrap .material-symbols-outlined {
      position: absolute;
      left: 0.9rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--color-on-surface-variant);
      font-size: 18px;
    }
    .input-icon-wrap .form-input-cv { padding-left: 2.5rem; }

    .form-hint {
      font-size: 0.75rem;
      color: var(--color-on-surface-variant);
      margin-top: 0.4rem;
    }

    .lock-preview {
      background: var(--color-surface-container-low);
      border: 1.5px solid var(--color-outline-variant);
      border-radius: 0.75rem;
      padding: 1rem 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.9rem;
      margin-top: 1.25rem;
    }
    .lock-icon-wrap {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(108,47,0,0.08);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .lock-preview-title { font-size: 0.9rem; font-weight: 700; }
    .lock-preview-sub { font-size: 0.78rem; color: var(--color-on-surface-variant); }

    .email-input-row {
      display: flex;
      gap: 0.5rem;
      align-items: stretch;
    }
    .email-input-row .form-input-cv { flex: 1; }
    .btn-add-email {
      width: 40px;
      height: 40px;
      border-radius: var(--ui-btn-radius);
      background: var(--color-primary);
      color: #fff;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex-shrink: 0;
      font-size: 20px;
      font-weight: 700;
      transition: var(--ui-btn-transition);
    }
    .btn-add-email:hover {
      background: var(--color-primary-container);
      transform: translateY(-1px);
    }

    .tag-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
      margin-top: 0.75rem;
    }
    .email-tag {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      background: var(--color-tertiary-fixed-dim);
      color: var(--color-on-tertiary-fixed);
      padding: 0.3rem 0.65rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .email-tag .material-symbols-outlined {
      font-size: 14px;
      cursor: pointer;
      opacity: 0.7;
    }
    .email-tag .material-symbols-outlined:hover { opacity: 1; }

    .info-box {
      background: var(--color-surface-container-low);
      border-radius: 0.65rem;
      padding: 0.9rem 1rem;
      font-size: 0.78rem;
      color: var(--color-on-surface-variant);
      line-height: 1.5;
      margin-top: 1rem;
    }

    .recap-table { margin-top: 1.5rem; }
    .recap-title { font-size: 0.9rem; font-weight: 700; margin-bottom: 0.75rem; }
    .recap-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.55rem 0;
      border-bottom: 1px solid var(--color-outline-variant);
      font-size: 0.85rem;
    }
    .recap-row:last-child { border-bottom: none; }
    .recap-key { color: var(--color-on-surface-variant); font-weight: 500; }
    .recap-value { font-weight: 700; color: var(--color-on-surface); text-align: right; }

    .btn-secondary-cv {
      background: transparent;
      color: var(--color-on-surface);
      border: 1.5px solid var(--color-outline-variant);
      padding: 0.65rem 1.4rem;
      border-radius: var(--ui-btn-radius);
      font-weight: 700;
      font-size: 0.875rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      cursor: pointer;
      transition: var(--ui-btn-transition);
    }
    .btn-secondary-cv:hover {
      background: var(--color-secondary-container);
      color: var(--color-on-secondary-container);
      border-color: var(--color-secondary-container);
      transform: translateY(-1px);
    }
    .btn-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 1.75rem;
      gap: 0.75rem;
    }
    .btn-row.end { justify-content: flex-end; }

    .step-panel { display: none; }
    .step-panel.active {
      display: block;
      width: 100%;
      max-width: 720px;
      background: var(--color-surface);
    }

    .page-footer {
      margin-top: auto;
      border-top: 1px solid var(--color-outline-variant);
      background: rgba(255, 255, 255, 0.78);
      backdrop-filter: blur(10px);
      padding: 1rem 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .footer-copy, .footer-links a {
      
      margin-left: 1rem;
      margin-right: 1rem;
      font-size: 0.65rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--color-outline);
      text-decoration: none;
    }
    .footer-links { display: flex; gap: 2rem; }
    .footer-links a:hover { color: var(--color-primary); }

    @media (max-width: 991.98px) {
      body {
        display: block;
      }

      #sidebar {
        width: 100%;
        min-height: auto;
        position: static;
        border-right: none;
        border-bottom: 1px solid var(--color-outline-variant);
        padding: 1rem;
      }

      #sidebar .mb-4.px-2 {
        text-align: center;
      }

      #main {
        width: 100%;
      }

      .main-content {
        padding: 1.25rem 1rem 1.5rem;
      }

      #sidebar nav {
        align-items: center;
      }

      .nav-link-item {
        width: 100%;
        justify-content: center;
        text-align: center;
      }

      .user-profile {
        justify-content: center;
        text-align: center;
      }

      .page-header {
        margin-bottom: 1.25rem;
      }

      .page-title {
        font-size: 1.9rem;
      }

      .cards-section {
        padding: 0;
      }

      .step-content {
        padding: 0;
      }

      .filter-bar {
        padding: 0.85rem;
      }

      .page-footer {
        padding: 1rem 1.25rem;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: center;
        text-align: center;
      }

      .footer-copy {
        width: 100%;
        text-align: center;
      }

      .footer-links {
        width: 100%;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
      }
    }

    @media (max-width: 575.98px) {
      .page-title {
        font-size: 1.55rem;
      }

      .page-subtitle {
        font-size: 0.85rem;
      }

      .cards-section {
        padding: 0;
      }

      .step-content {
        padding: 0;
      }

      .stepper {
        justify-content: flex-start;
        gap: 0.45rem;
        overflow-x: auto;
        padding-bottom: 0.3rem;
      }

      .step-label {
        font-size: 0.75rem;
      }

      .step-connector {
        min-width: 36px;
        flex-basis: 36px;
      }

      .card-body-cv {
        padding: 1rem;
      }

      .page-footer {
        padding: 0.85rem 0.9rem;
      }
    }
  </style>
</head>

<body>

  <aside id="sidebar">
    <div class="mb-4 px-2">
      <div class="brand-name">ChronoVault</div>
      <div class="brand-sub">The Timeless Curator</div>
    </div>

    <nav class="flex-grow-1 d-flex flex-column gap-1">
      <a href="dashboard.php" class="nav-link-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">dashboard</span>
        <span>Dashboard</span>
      </a>
      <a href="capsules.php" class="nav-link-item <?php echo $activePage === 'capsules' ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">inventory_2</span>
        <span>Mes Capsules</span>
      </a>
      <a href="new-capsule.php" class="nav-link-item <?php echo $activePage === 'new-capsule' ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">auto_awesome</span>
        <span>Nouvelle Capsule</span>
      </a>
      <a href="invitations.php" class="nav-link-item <?php echo $activePage === 'invitations' ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">notifications</span>
        <span>Notifications</span>
        <?php if (!empty($pendingInvitations)): ?>
          <span class="badge-cv badge-shared" style="margin-left:0.6rem; font-size:0.7rem; padding:0.2rem 0.55rem;"><?php echo (int)$pendingInvitations; ?></span>
        <?php endif; ?>
      </a>
      <a href="parametre.php" class="nav-link-item <?php echo $activePage === 'parametre' ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">settings</span>
        <span>Paramètres</span>
      </a>
      <?php if ($sidebarIsAdmin): ?>
      <a href="admin.php" class="nav-link-item <?php echo $activePage === 'admin' ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">admin_panel_settings</span>
        <span>Administration</span>
      </a>
      <?php endif; ?>
    </nav>

    <div class="mt-auto">
      <div class="sidebar-divider"></div>
      <a href="aide.php" class="nav-link-item <?php echo $activePage === 'aide' ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">help</span>
        <span>Aide</span>
      </a>
      <a href="logout.php" class="nav-link-item">
        <span class="material-symbols-outlined">logout</span>
        <span>Déconnexion</span>
      </a>
      <div class="user-profile">
        <img
          src="https://lh3.googleusercontent.com/aida-public/AB6AXuCuDd-TmfhHzFEd72RVUQR1QLqv3rap1YuN2tD4wPGsuEuj2TRdZnhQp852Q-_MgU_-CJMOF83pjT582yPw5Bh4zSkjTqAa9jcguFJ6Yg4evelTl__N24VzM540KrOqNwxetmthLkbBlb3LoqzDBCtUKR8NbtfnN2kvcGhVchmAUEp4vhpgcsVSUiJzlw3FaD5bUmVjtFXD3nenzZEZ2yUArKpglz317i6iqEgJro57xU2B7iwls5vKnEy2_VUs4nf5kiHhmj_PgMyV"
          alt="<?php echo htmlspecialchars($sidebarUserAvatarAlt, ENT_QUOTES, 'UTF-8'); ?>" class="user-avatar"/>
        <div class="user-name"><?php echo htmlspecialchars($sidebarUserName, ENT_QUOTES, 'UTF-8'); ?></div>
      </div>
    </div>
  </aside>

  <main id="main">
    <div class="main-content">