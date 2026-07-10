<?php
/**
 * admin/users.php — Visitor Account Manager
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_admin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pa = $_POST['action'] ?? '';
    
    if ($pa === 'toggle_status') {
        $userId = (int)($_POST['id'] ?? 0);
        $user = Database::fetchOne("SELECT is_active, name FROM users WHERE id = ?", [$userId]);
        if ($user) {
            $newStatus = $user['is_active'] ? 0 : 1;
            Database::update('users', ['is_active' => $newStatus], 'id = ?', [$userId]);
            $statusStr = $newStatus ? 'activated' : 'suspended';
            $msg = "success|Visitor account for '{$user['name']}' has been {$statusStr}.";
        } else {
            $msg = 'error|Visitor account not found.';
        }
    }
}

$search = strip_and_trim($_GET['search'] ?? '');

// Build query
$sql = "SELECT * FROM users";
$params = [];

if ($search !== '') {
    $sql .= " WHERE name LIKE ? OR email LIKE ? OR bio LIKE ?";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY created_at DESC";
$usersList = Database::fetchAll($sql, $params);
[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Visitors Management — Admin Panel</title>
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
          <h1 class="admin-page-title">Registered Visitors</h1>
          <div class="admin-breadcrumb"><i class="bi bi-people"></i> Engagement &rsaquo; Visitors</div>
        </div>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-3">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill' ?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <!-- Search bar -->
      <div class="row mb-4">
        <div class="col-md-5">
          <form method="GET" class="glass-panel p-2 d-flex align-items-center">
            <i class="bi bi-search ms-3 me-2" style="color:rgba(196,181,253,0.5);"></i>
            <input type="text" name="search" class="form-control bg-transparent border-0 text-white me-2" 
                   placeholder="Search visitors..." value="<?= sanitize_output($search) ?>" style="box-shadow:none;">
            <button type="submit" class="btn-galaxy btn-sm-galaxy btn-primary-galaxy">Search</button>
          </form>
        </div>
      </div>

      <div class="admin-table-wrap">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid rgba(196,181,253,0.1);">
          <h3 style="margin:0;font-size:0.9rem;font-weight:700;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;">Visitor Accounts</h3>
        </div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Avatar</th>
              <th>Name</th>
              <th>Email</th>
              <th>Registered</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usersList as $u): ?>
            <tr>
              <td>
                <img src="<?= get_avatar_url($u['avatar'], $u['name']) ?>" 
                     style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid rgba(147,51,234,0.4);" alt="">
              </td>
              <td><span style="font-weight:600;color:#fff;"><?= sanitize_output($u['name']) ?></span></td>
              <td><span style="color:rgba(255,255,255,0.8);"><?= sanitize_output($u['email']) ?></span></td>
              <td>
                <span style="font-size:0.8rem;color:rgba(196,181,253,0.6);">
                  <?= format_date($u['created_at']) ?>
                </span>
              </td>
              <td>
                <?php if ($u['is_active']): ?>
                <span class="status-pill status-active">Active</span>
                <?php else: ?>
                <span class="status-pill status-suspended" style="background:rgba(239,68,68,0.15);color:#EF4444;border-color:rgba(239,68,68,0.25);">Suspended</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="POST" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle_status">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <?php if ($u['is_active']): ?>
                  <button type="submit" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy" style="color:rgba(239,68,68,0.85);border-color:rgba(239,68,68,0.25);" title="Suspend Account">
                    <i class="bi bi-person-x"></i> Suspend
                  </button>
                  <?php else: ?>
                  <button type="submit" class="btn-galaxy btn-sm-galaxy btn-primary-galaxy" title="Activate Account">
                    <i class="bi bi-person-check"></i> Activate
                  </button>
                  <?php endif; ?>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$usersList): ?>
            <tr>
              <td colspan="6" class="text-center py-5" style="color:rgba(196,181,253,0.4);">
                No registered visitors found.
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
</body>
</html>
