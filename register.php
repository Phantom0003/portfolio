<?php
/**
 * register.php — Visitor Registration
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

if (is_logged_in()) redirect('/portfolio/');

$error = ''; $success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security token error. Please try again.';
    } else {
        $name     = strip_and_trim($_POST['name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!$name || !$email || !$password) {
            $error = 'All fields are required.';
        } elseif (strlen($name) < 2) {
            $error = 'Name must be at least 2 characters.';
        } elseif (!is_valid_email($email)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $result = register_visitor($name, $email, $password);
            if (!$result['success']) {
                $error = $result['message'];
            } else {
                // Auto-login
                $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$result['id']]);
                login_user($user, 'visitor');
                redirect('/portfolio/visitor/dashboard.php');
            }
        }
    }
}

$siteName = get_setting('site_name', 'Galaxy Portfolio');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — <?= sanitize_output($siteName) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/portfolio/assets/css/animations.css">
  <link rel="stylesheet" href="/portfolio/assets/css/space.css">
  <link rel="stylesheet" href="/portfolio/assets/css/glassmorphism.css">
  <link rel="stylesheet" href="/portfolio/assets/css/main.css">
</head>
<body>
<div class="galaxy-bg"></div>
<canvas id="star-canvas"></canvas>
<div style="position:fixed;inset:0;z-index:0;">
  <div class="nebula-layer nebula-1"></div>
  <div class="nebula-layer nebula-3"></div>
</div>
<div class="planet-decor planet-1" style="width:60px;height:60px;top:8%;right:8%;"></div>
<div class="planet-decor planet-3"></div>

<div class="min-vh-100 d-flex align-items-center justify-content-center py-5" style="position:relative;z-index:10;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-9 col-lg-6">
        <div class="text-center mb-4 reveal">
          <a href="/portfolio/" style="font-family:'Orbitron',monospace;font-size:1.6rem;font-weight:900;text-decoration:none;background:linear-gradient(135deg,#C4B5FD,#D946EF);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            <?= sanitize_output($siteName) ?>
          </a>
          <p style="color:rgba(196,181,253,0.5);font-size:0.85rem;margin-top:0.4rem;">Join the galaxy</p>
        </div>

        <div class="glass-panel reveal">
          <h2 style="font-size:1.4rem;color:#fff;font-family:'Space Grotesk',sans-serif;margin-bottom:0.3rem;">Create Account</h2>
          <p style="font-size:0.85rem;color:rgba(196,181,253,0.5);margin-bottom:1.5rem;">Register as a visitor to save projects, comment, and more</p>

          <?php if ($error): ?>
          <div class="galaxy-alert galaxy-alert-error mb-3">
            <i class="bi bi-x-circle-fill"></i> <span><?= sanitize_output($error) ?></span>
          </div>
          <?php endif; ?>

          <form method="POST" class="galaxy-form">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label class="form-label"><i class="bi bi-person" style="color:#9333EA;"></i> Full Name</label>
              <input type="text" name="name" class="galaxy-input form-control" placeholder="John Doe" value="<?= sanitize_output($_POST['name'] ?? '') ?>" required autofocus>
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="bi bi-envelope" style="color:#9333EA;"></i> Email Address</label>
              <input type="email" name="email" class="galaxy-input form-control" placeholder="you@example.com" value="<?= sanitize_output($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="mb-3 position-relative">
              <label class="form-label"><i class="bi bi-lock" style="color:#9333EA;"></i> Password <span style="color:rgba(196,181,253,0.4);font-size:0.75rem;">(min 8 chars)</span></label>
              <input type="password" name="password" id="pass1" class="galaxy-input form-control" placeholder="••••••••" required>
              <button type="button" onclick="togglePass('pass1','eye1')" class="btn position-absolute" style="right:10px;top:36px;padding:0.3rem;background:none;border:none;color:rgba(196,181,253,0.5);">
                <i class="bi bi-eye" id="eye1"></i>
              </button>
            </div>
            <div class="mb-4 position-relative">
              <label class="form-label"><i class="bi bi-lock-fill" style="color:#9333EA;"></i> Confirm Password</label>
              <input type="password" name="confirm_password" id="pass2" class="galaxy-input form-control" placeholder="••••••••" required>
              <button type="button" onclick="togglePass('pass2','eye2')" class="btn position-absolute" style="right:10px;top:36px;padding:0.3rem;background:none;border:none;color:rgba(196,181,253,0.5);">
                <i class="bi bi-eye" id="eye2"></i>
              </button>
            </div>
            <button type="submit" class="btn-galaxy btn-primary-galaxy w-100 mb-3">
              <i class="bi bi-stars"></i> Create My Universe
            </button>
            <div class="text-center" style="font-size:0.85rem;color:rgba(196,181,253,0.5);">
              Already have an account? <a href="/portfolio/login.php" style="color:#9333EA;">Sign in</a>
            </div>
          </form>
        </div>

        <div class="text-center mt-3" style="font-size:0.78rem;color:rgba(196,181,253,0.3);">
          <a href="/portfolio/" style="color:inherit;text-decoration:none;"><i class="bi bi-arrow-left"></i> Back to Portfolio</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/main.js"></script>
<script>
function togglePass(id, eyeId) {
  const inp = document.getElementById(id);
  const eye = document.getElementById(eyeId);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  eye.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
