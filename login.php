<?php
/**
 * login.php — Galaxy Portfolio Login
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

// Redirect if already logged in
if (is_admin()) redirect('/portfolio/admin/dashboard.php');
if (is_visitor()) redirect('/portfolio/visitor/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'visitor';

        if (!$email || !$password) {
            $error = 'Please enter your email and password.';
        } else {
            // Try admin
            if ($role === 'admin') {
                $admin = Database::fetchOne("SELECT * FROM admins WHERE email = ?", [$email]);
                if (!$admin) {
                    $error = 'No admin account found with this email.';
                } elseif (!check_login_throttle('admins', $admin['id'])) {
                    $error = 'Account temporarily locked. Try again in 15 minutes.';
                } elseif (!verify_password($password, $admin['password'])) {
                    increment_login_attempts('admins', $admin['id']);
                    $error = 'Incorrect password.';
                } else {
                    login_user($admin, $admin['role']);
                    $redir = $_SESSION['redirect_after_login'] ?? '/portfolio/admin/dashboard.php';
                    unset($_SESSION['redirect_after_login']);
                    redirect($redir);
                }
            } else {
                $user = Database::fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
                if (!$user) {
                    $error = 'No visitor account found with this email.';
                } elseif (!$user['is_active']) {
                    $error = 'Your account has been suspended.';
                } elseif (!check_login_throttle('users', $user['id'])) {
                    $error = 'Account temporarily locked. Try again in 15 minutes.';
                } elseif (!verify_password($password, $user['password'])) {
                    increment_login_attempts('users', $user['id']);
                    $error = 'Incorrect password.';
                } else {
                    login_user($user, 'visitor');
                    $redir = $_SESSION['redirect_after_login'] ?? '/portfolio/visitor/dashboard.php';
                    unset($_SESSION['redirect_after_login']);
                    redirect($redir);
                }
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
  <title>Login — <?= sanitize_output($siteName) ?></title>
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
  <div class="nebula-layer nebula-2"></div>
</div>

<!-- Floating shapes -->
<div class="planet-decor planet-1" style="width:60px;height:60px;top:10%;right:10%;"></div>
<div class="planet-decor planet-2" style="bottom:15%;left:8%;"></div>

<div class="min-vh-100 d-flex align-items-center justify-content-center py-5" style="position:relative;z-index:10;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-9 col-lg-6">

        <!-- Brand -->
        <div class="text-center mb-4 reveal">
          <a href="/portfolio/" style="font-family:'Orbitron',monospace;font-size:1.6rem;font-weight:900;text-decoration:none;background:linear-gradient(135deg,#C4B5FD,#D946EF);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            <?= sanitize_output($siteName) ?>
          </a>
          <p style="color:rgba(196,181,253,0.5);font-size:0.85rem;margin-top:0.4rem;">Sign in to your universe</p>
        </div>

        <div class="glass-panel reveal">
          <!-- Role Switcher -->
          <div class="d-flex gap-2 mb-4" id="role-tabs" style="background:rgba(255,255,255,0.04);border-radius:12px;padding:0.4rem;">
            <button type="button" onclick="switchRole('visitor')" id="tab-visitor"
              class="btn flex-fill" style="border-radius:10px;padding:0.6rem;font-family:'Space Grotesk',sans-serif;font-size:0.88rem;transition:0.3s;background:linear-gradient(135deg,#9333EA,#4F46E5);color:#fff;border:none;">
              <i class="bi bi-person-circle"></i> Visitor
            </button>
            <button type="button" onclick="switchRole('admin')" id="tab-admin"
              class="btn flex-fill" style="border-radius:10px;padding:0.6rem;font-family:'Space Grotesk',sans-serif;font-size:0.88rem;transition:0.3s;background:transparent;color:rgba(196,181,253,0.6);border:none;">
              <i class="bi bi-shield-lock"></i> Admin
            </button>
          </div>

          <?php if ($error): ?>
          <div class="galaxy-alert galaxy-alert-error mb-3">
            <i class="bi bi-x-circle-fill"></i> <span><?= sanitize_output($error) ?></span>
          </div>
          <?php endif; ?>

          <form method="POST" class="galaxy-form" id="login-form">
            <?= csrf_field() ?>
            <input type="hidden" name="role" id="role-input" value="<?= sanitize_output($_POST['role'] ?? 'visitor') ?>">

            <div class="mb-3">
              <label class="form-label"><i class="bi bi-envelope" style="color:#9333EA;"></i> Email Address</label>
              <input type="email" name="email" class="galaxy-input form-control" placeholder="you@example.com" value="<?= sanitize_output($_POST['email'] ?? '') ?>" required autofocus>
            </div>

            <div class="mb-4 position-relative">
              <label class="form-label"><i class="bi bi-lock" style="color:#9333EA;"></i> Password</label>
              <input type="password" name="password" id="password-input" class="galaxy-input form-control" placeholder="••••••••" required>
              <button type="button" onclick="togglePass()" class="btn position-absolute" style="right:10px;top:36px;padding:0.3rem;background:none;border:none;color:rgba(196,181,253,0.5);">
                <i class="bi bi-eye" id="pass-eye"></i>
              </button>
            </div>

            <button type="submit" class="btn-galaxy btn-primary-galaxy w-100 mb-3">
              <i class="bi bi-rocket-takeoff-fill"></i> Launch In
            </button>

            <div class="text-center" style="font-size:0.85rem;color:rgba(196,181,253,0.5);">
              New to the galaxy? <a href="/portfolio/register.php" style="color:#9333EA;">Create account</a>
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
function switchRole(role) {
  document.getElementById('role-input').value = role;
  const tv = document.getElementById('tab-visitor');
  const ta = document.getElementById('tab-admin');
  const active = 'background:linear-gradient(135deg,#9333EA,#4F46E5);color:#fff;border:none;';
  const inactive = 'background:transparent;color:rgba(196,181,253,0.6);border:none;';
  if (role === 'visitor') { tv.style.cssText += active; ta.style.cssText += inactive; }
  else { ta.style.cssText += active; tv.style.cssText += inactive; }
}
function togglePass() {
  const inp = document.getElementById('password-input');
  const eye = document.getElementById('pass-eye');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  eye.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
// Restore role tab
const savedRole = document.getElementById('role-input').value;
if (savedRole) switchRole(savedRole);
</script>
</body>
</html>
