<?php
/**
 * admin/super-admin.php — Manage Administrative Accounts (Super Admin only)
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin();

// Ensure only super admin can access
if (!is_super_admin()) {
    set_flash('error', 'Access denied. You must be a Super Admin to view this page.');
    redirect('/portfolio/admin/dashboard.php');
}

$msg = '';
$currentAdminId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pa = $_POST['action'] ?? '';
    
    if ($pa === 'save') {
        $id       = (int)($_POST['id'] ?? 0);
        $name     = strip_and_trim($_POST['name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $role     = $_POST['role'] ?? 'admin';
        
        if (!$name || !$email) {
            $msg = 'error|Name and Email are required.';
        } elseif (!is_valid_email($email)) {
            $msg = 'error|Please enter a valid email address.';
        } else {
            // Check if email already in use
            $existing = Database::fetchOne("SELECT id FROM admins WHERE email = ? AND id != ?", [$email, $id]);
            if ($existing) {
                $msg = 'error|This email is already in use by another administrator.';
            } else {
                $data = [
                    'name'  => $name,
                    'email' => $email,
                    'role'  => $role
                ];
                
                // Handle password
                $password = $_POST['password'] ?? '';
                if ($password !== '') {
                    if (strlen($password) < 8) {
                        $msg = 'error|Password must be at least 8 characters long.';
                    } else {
                        $data['password'] = password_hash($password, PASSWORD_BCRYPT);
                    }
                } elseif (!$id) {
                    $msg = 'error|Password is required for new administrators.';
                }
                
                if (empty($msg) || strpos($msg, 'success|') === 0) {
                    if ($id) {
                        // Prevent changing own role if self-editing
                        if ($id === $currentAdminId) {
                            unset($data['role']); // Keep own role
                        }
                        Database::update('admins', $data, 'id=?', [$id]);
                        $msg = 'success|Admin account updated!';
                    } else {
                        Database::insert('admins', $data);
                        $msg = 'success|Admin account created!';
                    }
                }
            }
        }
    }
    
    if ($pa === 'delete') {
        $targetId = (int)($_POST['id'] ?? 0);
        if ($targetId === $currentAdminId) {
            $msg = 'error|You cannot delete your own account!';
        } else {
            Database::delete('admins', 'id=?', [$targetId]);
            $msg = 'success|Admin account deleted.';
        }
    }
}

// Fetch all administrators
$adminsList = Database::fetchAll("SELECT * FROM admins ORDER BY role DESC, created_at DESC");
$editId = (int)($_GET['id'] ?? 0);
$editAdmin = $editId ? Database::fetchOne("SELECT * FROM admins WHERE id=?", [$editId]) : null;
[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Super Admin Panel — Admin Panel</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/portfolio/assets/css/space.css">
  <link rel="stylesheet" href="/portfolio/assets/css/animations.css">
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
          <h1 class="admin-page-title">Super Admin Panel</h1>
          <div class="admin-breadcrumb"><i class="bi bi-shield-lock-fill"></i> System &rsaquo; Administrative Accounts</div>
        </div>
        <button class="btn-galaxy btn-primary-galaxy" data-bs-toggle="modal" data-bs-target="#adminModal">
          <i class="bi bi-plus-lg"></i> Create Admin Account
        </button>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-4">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill' ?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <div class="admin-table-wrap">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid rgba(196,181,253,0.1);">
          <h3 style="margin:0;font-size:0.9rem;font-weight:700;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;">Administrative Accounts</h3>
        </div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Last Login</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($adminsList as $a): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= get_avatar_url($a['avatar'], $a['name']) ?>" 
                       style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid rgba(147,51,234,0.4);" alt="">
                  <span style="font-weight:600;color:#fff;"><?= sanitize_output($a['name']) ?></span>
                  <?php if ($a['id'] === $currentAdminId): ?>
                    <span class="galaxy-badge" style="font-size:0.6rem;padding:0.1rem 0.3rem;">You</span>
                  <?php endif; ?>
                </div>
              </td>
              <td><span style="color:rgba(255,255,255,0.8);"><?= sanitize_output($a['email']) ?></span></td>
              <td>
                <?php if ($a['role'] === 'super_admin'): ?>
                <span class="status-pill status-active"><i class="bi bi-shield-fill-check"></i> Super Admin</span>
                <?php else: ?>
                <span class="status-pill status-active" style="background:rgba(79,70,229,0.15);color:#818CF8;border-color:rgba(79,70,229,0.25);">Admin</span>
                <?php endif; ?>
              </td>
              <td>
                <span style="font-size:0.8rem;color:rgba(196,181,253,0.6);">
                  <?= $a['last_login'] ? format_date($a['last_login'], 'M j, Y H:i') : 'Never' ?>
                </span>
              </td>
              <td>
                <div class="d-flex gap-2">
                  <a href="?id=<?= $a['id'] ?>" class="topbar-icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                  <?php if ($a['id'] !== $currentAdminId): ?>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this administrator account?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button type="submit" class="topbar-icon-btn" style="color:rgba(239,68,68,0.7);" title="Delete"><i class="bi bi-trash"></i></button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Admin Modal -->
<div class="modal fade glass-modal" id="adminModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-gradient"><?= $editAdmin ? 'Edit' : 'Create' ?> Admin Account</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" class="galaxy-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editAdmin['id'] ?? 0 ?>">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Full Name *</label>
              <input type="text" name="name" class="galaxy-input form-control" value="<?= sanitize_output($editAdmin['name'] ?? '') ?>" required>
            </div>
            
            <div class="col-12">
              <label class="form-label">Email Address *</label>
              <input type="email" name="email" class="galaxy-input form-control" value="<?= sanitize_output($editAdmin['email'] ?? '') ?>" required>
            </div>

            <div class="col-12">
              <label class="form-label">Administrative Role</label>
              <select name="role" class="galaxy-input form-select" <?= ($editAdmin && $editAdmin['id'] === $currentAdminId) ? 'disabled' : '' ?>>
                <option value="admin" <?= ($editAdmin['role']??'') === 'admin' ? 'selected' : '' ?>>Admin (Standard)</option>
                <option value="super_admin" <?= ($editAdmin['role']??'') === 'super_admin' ? 'selected' : '' ?>>Super Admin (All Access)</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">Password <?= $editAdmin ? '(leave blank to keep current)' : '*' ?></label>
              <input type="password" name="password" class="galaxy-input form-control" placeholder="Minimum 8 characters" <?= $editAdmin ? '' : 'required' ?>>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-galaxy btn-outline-galaxy" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-galaxy btn-primary-galaxy"><i class="bi bi-save"></i> Save Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
<?php if ($editAdmin): ?>
<script>document.addEventListener('DOMContentLoaded',()=>new bootstrap.Modal(document.getElementById('adminModal')).show());</script>
<?php endif; ?>
</body>
</html>
