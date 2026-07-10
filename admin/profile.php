<?php
/**
 * admin/profile.php — Edit Admin User Profile
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/upload.php';

require_admin();

$adminId = current_user_id();
$admin   = Database::fetchOne("SELECT * FROM admins WHERE id = ?", [$adminId]);
$msg     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    
    $name  = strip_and_trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    
    if (!$name || !$email) {
        $msg = 'error|Name and Email are required.';
    } elseif (!is_valid_email($email)) {
        $msg = 'error|Please enter a valid email address.';
    } else {
        // Check if email already exists for another admin
        $existing = Database::fetchOne("SELECT id FROM admins WHERE email = ? AND id != ?", [$email, $adminId]);
        if ($existing) {
            $msg = 'error|This email is already in use by another admin.';
        } else {
            $data = [
                'name'  => $name,
                'email' => $email
            ];
            
            // Handle avatar upload
            if (!empty($_FILES['avatar']['name'])) {
                $uploader = new FileUploader();
                $up = $uploader->upload('avatar', 'profile', 'image');
                if ($up['success']) {
                    $data['avatar'] = $up['path'];
                } else {
                    $msg = 'error|' . $up['message'];
                }
            }
            
            // Password update
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';
            
            if ($newPassword !== '') {
                if (strlen($newPassword) < 8) {
                    $msg = 'error|Password must be at least 8 characters long.';
                } elseif ($newPassword !== $confirmPass) {
                    $msg = 'error|Passwords do not match.';
                } else {
                    $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
                }
            }
            
            if (empty($msg) || strpos($msg, 'success|') === 0) {
                Database::update('admins', $data, 'id = ?', [$adminId]);
                
                // Refresh session data
                $updatedAdmin = Database::fetchOne("SELECT * FROM admins WHERE id = ?", [$adminId]);
                $_SESSION['current_user'] = $updatedAdmin;
                $_SESSION['user_name']    = $updatedAdmin['name'];
                $admin = $updatedAdmin;
                $msg = 'success|Profile updated successfully.';
            }
        }
    }
}

[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Admin Profile — Admin Panel</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/portfolio/assets/css/animations.css">
  <link rel="stylesheet" href="/portfolio/assets/css/space.css">
  <link rel="stylesheet" href="/portfolio/assets/css/glassmorphism.css">
  <link rel="stylesheet" href="/portfolio/assets/css/main.css">
  <link rel="stylesheet" href="/portfolio/assets/css/admin.css">
</head>
<body>
<div class="galaxy-bg" style="position:fixed;"></div>
<canvas id="star-canvas"></canvas>

<div class="admin-wrapper">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <div class="admin-main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>
    <div class="admin-content">
      <div class="admin-page-header">
        <div>
          <h1 class="admin-page-title">My Profile</h1>
          <div class="admin-breadcrumb"><i class="bi bi-person"></i> Account &rsaquo; Profile</div>
        </div>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-4">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill'?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <div class="row g-4">
        <div class="col-lg-4 text-center">
          <div class="glass-panel">
            <div style="position:relative;display:inline-block;margin-bottom:1.5rem;">
              <img src="<?= get_avatar_url($admin['avatar'], $admin['name']) ?>" id="avatar-preview" 
                   style="width:130px;height:130px;border-radius:50%;object-fit:cover;border:3px solid rgba(147,51,234,0.5);box-shadow:0 0 30px rgba(147,51,234,0.3);" alt="">
            </div>
            <h4 class="text-white mb-1"><?= sanitize_output($admin['name']) ?></h4>
            <p style="color:rgba(196,181,253,0.5);font-size:0.85rem;margin-bottom:1rem;"><?= sanitize_output($admin['email']) ?></p>
            <span class="galaxy-badge"><?= sanitize_output(ucfirst($admin['role'])) ?></span>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="glass-panel">
            <h3 style="font-size:1.1rem;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;margin-bottom:1.5rem;font-family:'Space Grotesk',sans-serif;">
              Edit Profile Details
            </h3>
            <form method="POST" enctype="multipart/form-data" class="galaxy-form">
              <?= csrf_field() ?>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="name" class="galaxy-input form-control" value="<?= sanitize_output($admin['name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email Address</label>
                  <input type="email" name="email" class="galaxy-input form-control" value="<?= sanitize_output($admin['email']) ?>" required>
                </div>
                
                <div class="col-12">
                  <label class="form-label">Change Photo</label>
                  <input type="file" name="avatar" class="galaxy-input form-control" accept="image/*" data-preview="avatar-preview">
                </div>

                <div class="col-12 mt-4">
                  <h4 style="font-size:0.9rem;color:#C4B5FD;text-transform:uppercase;letter-spacing:1px;margin-bottom:1rem;font-family:'Space Grotesk',sans-serif;">
                    Change Password (leave blank to keep current)
                  </h4>
                </div>

                <div class="col-md-6">
                  <label class="form-label">New Password</label>
                  <input type="password" name="new_password" class="galaxy-input form-control" placeholder="Minimum 8 characters">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Confirm New Password</label>
                  <input type="password" name="confirm_password" class="galaxy-input form-control" placeholder="Confirm password">
                </div>

                <div class="col-12 mt-4">
                  <button type="submit" class="btn-galaxy btn-primary-galaxy">
                    <i class="bi bi-save"></i> Save Profile Details
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/main.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
</body>
</html>
