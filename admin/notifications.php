<?php
/**
 * admin/notifications.php — Notifications Manager
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_admin();

$adminId = current_user_id();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pa = $_POST['action'] ?? '';
    
    if ($pa === 'mark_all_read') {
        Database::update(
            'notifications',
            ['is_read' => 1],
            "recipient_type = 'admin' AND is_read = 0",
            []
        );
        $msg = 'success|All notifications marked as read.';
    }
    
    if ($pa === 'delete_all') {
        Database::delete('notifications', "recipient_type = 'admin'", []);
        $msg = 'success|Notifications cleared.';
    }
}

// Fetch admin notifications
$notificationsList = Database::fetchAll("SELECT * FROM notifications WHERE recipient_type = 'admin' ORDER BY created_at DESC LIMIT 100");
[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Notifications — Admin Panel</title>
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
          <h1 class="admin-page-title">Notifications</h1>
          <div class="admin-breadcrumb"><i class="bi bi-bell"></i> Engagement &rsaquo; Alerts</div>
        </div>
        <div class="d-flex gap-2">
          <form method="POST" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn-galaxy btn-outline-galaxy btn-sm-galaxy">
              <i class="bi bi-check2-all"></i> Mark All Read
            </button>
          </form>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Clear all notifications?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_all">
            <button type="submit" class="btn-galaxy btn-outline-galaxy btn-sm-galaxy" style="color:rgba(239,68,68,0.85);border-color:rgba(239,68,68,0.25);">
              <i class="bi bi-trash"></i> Clear All
            </button>
          </form>
        </div>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-4">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill' ?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <div class="glass-panel">
        <?php if ($notificationsList): ?>
          <div class="d-flex flex-column gap-2">
            <?php foreach ($notificationsList as $n): ?>
            <div class="activity-item <?= $n['is_read'] ? 'opacity-60' : '' ?>" style="padding: 1rem; border-bottom: 1px solid rgba(196,181,253,0.08);">
              <div class="activity-icon" style="background:<?= sanitize_output($n['color'] ?? '#9333EA') ?>22;width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi <?= sanitize_output($n['icon'] ?? 'bi-bell') ?>" style="color:<?= sanitize_output($n['color'] ?? '#9333EA') ?>;font-size:1.1rem;"></i>
              </div>
              <div style="flex-grow:1;">
                <div class="activity-text" style="font-weight:<?= $n['is_read'] ? 400 : 700 ?>;color:#fff;">
                  <?= sanitize_output($n['title']) ?>
                </div>
                <?php if ($n['message']): ?>
                  <div style="font-size:0.85rem;color:rgba(196,181,253,0.6);margin-top:0.2rem;"><?= sanitize_output($n['message']) ?></div>
                <?php endif; ?>
                <div class="activity-time" style="font-size:0.75rem;margin-top:0.3rem;"><?= time_ago($n['created_at']) ?></div>
              </div>
              
              <div class="d-flex align-items-center gap-2">
                <?php if ($n['action_url']): ?>
                <a href="<?= sanitize_output($n['action_url']) ?>" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy" style="font-size:0.75rem;padding:0.3rem 0.6rem;">
                  View Action
                </a>
                <?php endif; ?>
                <?php if (!$n['is_read']): ?>
                <div style="width:8px;height:8px;border-radius:50%;background:#D946EF;" title="New notification"></div>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-5" style="color:rgba(196,181,253,0.4);">
            <i class="bi bi-bell-slash" style="font-size:3.5rem;display:block;margin-bottom:1rem;"></i>
            No notifications in your solar system.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
</body>
</html>
