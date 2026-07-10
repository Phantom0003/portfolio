<?php
/**
 * Shared Layout Header
 * Galaxy Portfolio CMS
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

$siteName     = get_setting('site_name', 'Galaxy Portfolio');
$ownerName    = get_setting('owner_name', 'Your Name');
$ownerTitle   = get_setting('owner_title', 'Full Stack Developer');
$themeData    = json_encode([
    'primary'   => get_setting('theme_primary', '#9333EA'),
    'secondary' => get_setting('theme_secondary', '#4F46E5'),
    'accent'    => get_setting('theme_accent', '#D946EF'),
]);
$enableParticles = get_setting('enable_particles', '1');
$currentPage  = $currentPage ?? '';
$pageTitle    = $pageTitle ?? $siteName;
$pageDesc     = $pageDesc ?? get_setting('owner_bio', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= sanitize_output($pageDesc) ?>">
  <meta name="theme-data" content='<?= htmlspecialchars($themeData) ?>'>
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title><?= sanitize_output($pageTitle) ?></title>

  <!-- Preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <!-- Galaxy CSS -->
  <link rel="stylesheet" href="/portfolio/assets/css/animations.css">
  <link rel="stylesheet" href="/portfolio/assets/css/space.css">
  <link rel="stylesheet" href="/portfolio/assets/css/glassmorphism.css">
  <link rel="stylesheet" href="/portfolio/assets/css/main.css">
</head>
<body>

<!-- Page Load Progress -->
<div id="page-progress"></div>

<!-- Galaxy Loader -->
<div id="galaxy-loader">
  <div class="loader-ring">
    <div class="loader-ring-inner"></div>
    <div class="loader-ring-inner"></div>
    <div class="loader-ring-inner"></div>
  </div>
  <div class="loader-text">Loading Universe...</div>
  <div class="loader-progress"><div class="loader-progress-bar"></div></div>
</div>

<!-- Galaxy Background -->
<div class="galaxy-bg"></div>
<div class="galaxy-bg" style="position:fixed;">
  <div class="nebula-layer nebula-1"></div>
  <div class="nebula-layer nebula-2"></div>
  <div class="nebula-layer nebula-3"></div>
</div>

<!-- Star Canvas -->
<?php if ($enableParticles): ?>
<canvas id="star-canvas"></canvas>
<?php endif; ?>

<!-- Navigation -->
<nav class="galaxy-nav" id="main-nav">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between">
      <a href="/portfolio/" class="nav-brand text-decoration-none">
        &lt;<?= sanitize_output(substr($ownerName, 0, 1)) ?>/&gt;
      </a>

      <!-- Desktop Nav -->
      <div class="d-none d-lg-flex align-items-center gap-1" id="nav-menu">
        <a href="/portfolio/#hero"         class="nav-link <?= $currentPage==='home'?'active':'' ?>">Home</a>
        <a href="/portfolio/#about"        class="nav-link">About</a>
        <a href="/portfolio/#skills"       class="nav-link">Skills</a>
        <a href="/portfolio/#projects"     class="nav-link">Projects</a>
        <a href="/portfolio/#experience"   class="nav-link">Experience</a>
        <a href="/portfolio/blog.php"      class="nav-link <?= $currentPage==='blog'?'active':'' ?>">Blog</a>
        <a href="/portfolio/#contact"      class="nav-link">Contact</a>
      </div>

      <div class="d-flex align-items-center gap-2">
        <?php if (is_admin()): ?>
          <a href="/portfolio/admin/dashboard.php" class="btn-galaxy btn-sm-galaxy btn-primary-galaxy">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
        <?php elseif (is_visitor()): ?>
          <a href="/portfolio/visitor/dashboard.php" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy">
            <i class="bi bi-person-circle"></i> <?= sanitize_output($_SESSION['user_name']) ?>
          </a>
        <?php else: ?>
          <a href="/portfolio/login.php" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy">Login</a>
          <a href="/portfolio/register.php" class="btn-galaxy btn-sm-galaxy btn-primary-galaxy">Register</a>
        <?php endif; ?>

        <!-- Mobile Toggle -->
        <button class="btn-galaxy btn-sm-galaxy btn-outline-galaxy d-lg-none" id="mobile-menu-btn" style="padding:0.4rem 0.8rem;">
          <i class="bi bi-list"></i>
        </button>
      </div>
    </div>

    <!-- Mobile Nav -->
    <div class="d-lg-none" id="mobile-nav" style="display:none!important;">
      <div class="glass-card mt-3 p-3">
        <a href="/portfolio/#hero"       class="d-block nav-link py-2">Home</a>
        <a href="/portfolio/#about"      class="d-block nav-link py-2">About</a>
        <a href="/portfolio/#skills"     class="d-block nav-link py-2">Skills</a>
        <a href="/portfolio/#projects"   class="d-block nav-link py-2">Projects</a>
        <a href="/portfolio/#experience" class="d-block nav-link py-2">Experience</a>
        <a href="/portfolio/blog.php"    class="d-block nav-link py-2">Blog</a>
        <a href="/portfolio/#contact"    class="d-block nav-link py-2">Contact</a>
      </div>
    </div>
  </div>
</nav>
