<?php
/**
 * admin/messages.php — Inbox
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
    if ($pa === 'mark_read') {
        Database::update('messages', ['is_read' => 1], 'id=?', [(int)($_POST['id']??0)]);
    }
    if ($pa === 'delete') {
        Database::delete('messages', 'id=?', [(int)($_POST['id']??0)]);
        $msg = 'success|Message deleted.';
    }
    if ($pa === 'mark_all_read') {
        Database::query("UPDATE messages SET is_read=1");
        $msg = 'success|All messages marked as read.';
    }
}

$viewId = (int)($_GET['view'] ?? 0);
$viewMsg = null;
if ($viewId) {
    $viewMsg = Database::fetchOne("SELECT * FROM messages WHERE id=?", [$viewId]);
    if ($viewMsg && !$viewMsg['is_read']) {
        Database::update('messages', ['is_read' => 1], 'id=?', [$viewId]);
        $viewMsg['is_read'] = 1;
    }
}

$messages = Database::fetchAll("SELECT * FROM messages ORDER BY created_at DESC");
[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages — Admin</title>
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
        <div><h1 class="admin-page-title">Messages</h1><div class="admin-breadcrumb"><i class="bi bi-chat-dots"></i> Engagement &rsaquo; Messages</div></div>
        <form method="POST" style="display:inline;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="mark_all_read">
          <button type="submit" class="btn-galaxy btn-outline-galaxy btn-sm-galaxy"><i class="bi bi-check-all"></i> Mark All Read</button>
        </form>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-3"><i class="bi bi-check-circle-fill"></i> <?= sanitize_output($msgText) ?></div>
      <?php endif; ?>

      <div class="row g-4">
        <!-- Message List -->
        <div class="col-lg-4">
          <div class="admin-table-wrap">
            <?php foreach ($messages as $m): ?>
            <a href="?view=<?= $m['id'] ?>" style="text-decoration:none;display:block;">
              <div class="activity-item <?= !$m['is_read']?'':'opacity-50' ?>" style="padding:1rem;background:<?= $viewId===$m['id']?'rgba(147,51,234,0.15)':'' ?>;border-left:3px solid <?= !$m['is_read']?'#9333EA':'transparent' ?>;">
                <div class="activity-icon" style="background:rgba(147,51,234,0.15);"><i class="bi bi-envelope<?= $m['is_read']?'-open':'' ?>" style="color:#9333EA;"></i></div>
                <div style="overflow:hidden;">
                  <div style="font-weight:<?= $m['is_read']?'400':'700' ?>;color:#fff;font-size:0.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= sanitize_output($m['name']) ?></div>
                  <div style="font-size:0.78rem;color:rgba(196,181,253,0.5);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= sanitize_output($m['subject'] ?: $m['email']) ?></div>
                  <div style="font-size:0.72rem;color:rgba(196,181,253,0.3);"><?= time_ago($m['created_at']) ?></div>
                </div>
              </div>
            </a>
            <?php endforeach; ?>
            <?php if (!$messages): ?>
            <div class="p-4 text-center" style="color:rgba(196,181,253,0.4);">No messages yet</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Message Detail -->
        <div class="col-lg-8">
          <?php if ($viewMsg): ?>
          <div class="glass-panel">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h3 style="font-size:1.2rem;color:#fff;margin-bottom:0.3rem;"><?= sanitize_output($viewMsg['subject'] ?: 'No Subject') ?></h3>
                <div style="font-size:0.85rem;color:rgba(196,181,253,0.6);">
                  From: <strong style="color:#C4B5FD;"><?= sanitize_output($viewMsg['name']) ?></strong>
                  &lt;<a href="mailto:<?= sanitize_output($viewMsg['email']) ?>" style="color:#9333EA;"><?= sanitize_output($viewMsg['email']) ?></a>&gt;
                  &middot; <?= format_date($viewMsg['created_at'], 'M j, Y g:ia') ?>
                </div>
              </div>
              <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $viewMsg['id'] ?>">
                <button type="submit" class="topbar-icon-btn" style="color:rgba(239,68,68,0.7);" onclick="return confirm('Delete this message?')"><i class="bi bi-trash"></i></button>
              </form>
            </div>
            <hr style="border-color:rgba(196,181,253,0.1);">
            <div style="color:rgba(255,255,255,0.8);line-height:1.8;font-size:0.95rem;white-space:pre-wrap;"><?= sanitize_output($viewMsg['message']) ?></div>
            <div class="mt-4">
              <a href="mailto:<?= sanitize_output($viewMsg['email']) ?>?subject=Re: <?= urlencode($viewMsg['subject'] ?? '') ?>" class="btn-galaxy btn-primary-galaxy">
                <i class="bi bi-reply-fill"></i> Reply via Email
              </a>
            </div>
          </div>
          <?php else: ?>
          <div class="glass-panel text-center py-5" style="color:rgba(196,181,253,0.4);">
            <i class="bi bi-chat-dots" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
            Select a message to read
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
</body>
</html>
