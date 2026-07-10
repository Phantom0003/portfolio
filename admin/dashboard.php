<?php
/**
 * admin/dashboard.php — Admin Dashboard
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin();

$stats    = get_admin_stats();
$unreadN  = get_unread_notifications('admin', current_user_id());

// Recent activities
$recentMessages  = Database::fetchAll("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
$recentUsers     = Database::fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recentComments  = Database::fetchAll("SELECT c.*, b.title AS post_title FROM comments c JOIN blog_posts b ON c.post_id=b.id ORDER BY c.created_at DESC LIMIT 5");

$admin = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Dashboard — Admin Panel</title>
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
  <!-- Sidebar -->
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <!-- Main -->
  <div class="admin-main">
    <!-- Topbar -->
    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <!-- Content -->
    <div class="admin-content">
      <div class="admin-page-header">
        <div>
          <h1 class="admin-page-title">Dashboard</h1>
          <div class="admin-breadcrumb"><i class="bi bi-house"></i> Home &rsaquo; Dashboard</div>
        </div>
        <div class="d-flex gap-2">
          <a href="/portfolio/" target="_blank" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy">
            <i class="bi bi-box-arrow-up-right"></i> View Site
          </a>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid mb-4">
        <?php
        $cards = [
          ['Visitors',     $stats['visitors'],   'bi-people-fill',      'linear-gradient(135deg,#9333EA,#7C3AED)', 'rgba(147,51,234,0.15)'],
          ['Projects',     $stats['projects'],   'bi-folder-fill',      'linear-gradient(135deg,#4F46E5,#3730A3)', 'rgba(79,70,229,0.15)'],
          ['Blog Posts',   $stats['blog_posts'], 'bi-newspaper',        'linear-gradient(135deg,#D946EF,#A21CAF)', 'rgba(217,70,239,0.15)'],
          ['Skills',       $stats['skills'],     'bi-lightning-fill',   'linear-gradient(135deg,#0EA5E9,#0369A1)', 'rgba(14,165,233,0.15)'],
          ['Messages',     $stats['messages'],   'bi-chat-dots-fill',   'linear-gradient(135deg,#10B981,#059669)', 'rgba(16,185,129,0.15)'],
          ['Comments',     $stats['comments'],   'bi-chat-left-fill',   'linear-gradient(135deg,#F59E0B,#D97706)', 'rgba(245,158,11,0.15)'],
          ['Media Files',  $stats['media'],      'bi-images',           'linear-gradient(135deg,#EF4444,#DC2626)', 'rgba(239,68,68,0.15)'],
          ['Total Msg',    $stats['total_msg'],  'bi-envelope-fill',    'linear-gradient(135deg,#8B5CF6,#6D28D9)', 'rgba(139,92,246,0.15)'],
        ];
        foreach ($cards as [$label, $val, $icon, $grad, $color]):
        ?>
        <div class="admin-stat-card" style="--card-color:<?= $color ?>;">
          <div class="stat-card-icon" style="background:<?= $grad ?>;box-shadow:none;">
            <i class="bi <?= $icon ?>" style="color:#fff;"></i>
          </div>
          <div class="stat-card-value" data-count="<?= $val ?>"><?= $val ?></div>
          <div class="stat-card-label"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-4">
        <!-- Recent Messages -->
        <div class="col-lg-6">
          <div class="admin-table-wrap">
            <div style="padding:1.25rem;border-bottom:1px solid rgba(196,181,253,0.1);display:flex;align-items:center;justify-content:space-between;">
              <h3 style="font-size:1rem;font-weight:700;color:#fff;margin:0;font-family:'Space Grotesk',sans-serif;">
                <i class="bi bi-chat-dots" style="color:#9333EA;margin-right:6px;"></i> Recent Messages
              </h3>
              <a href="/portfolio/admin/messages.php" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy">View All</a>
            </div>
            <div style="padding:0.5rem;">
              <?php foreach ($recentMessages as $msg): ?>
              <div class="activity-item">
                <div class="activity-icon" style="background:rgba(147,51,234,0.15);">
                  <i class="bi bi-envelope" style="color:#9333EA;"></i>
                </div>
                <div>
                  <div class="activity-text">
                    <strong><?= sanitize_output($msg['name']) ?></strong>
                    <?php if ($msg['subject']): ?> — <?= sanitize_output($msg['subject']) ?><?php endif; ?>
                    <?php if (!$msg['is_read']): ?><span class="galaxy-badge ms-1" style="font-size:0.6rem;">New</span><?php endif; ?>
                  </div>
                  <div class="activity-time"><?= time_ago($msg['created_at']) ?></div>
                </div>
              </div>
              <?php endforeach; ?>
              <?php if (!$recentMessages): ?>
              <div class="p-3 text-center" style="color:rgba(196,181,253,0.4);font-size:0.85rem;">No messages yet</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Recent Users -->
        <div class="col-lg-6">
          <div class="admin-table-wrap">
            <div style="padding:1.25rem;border-bottom:1px solid rgba(196,181,253,0.1);display:flex;align-items:center;justify-content:space-between;">
              <h3 style="font-size:1rem;font-weight:700;color:#fff;margin:0;font-family:'Space Grotesk',sans-serif;">
                <i class="bi bi-people" style="color:#9333EA;margin-right:6px;"></i> Recent Visitors
              </h3>
              <a href="/portfolio/admin/users.php" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy">View All</a>
            </div>
            <div style="padding:0.5rem;">
              <?php foreach ($recentUsers as $u): ?>
              <div class="activity-item">
                <img src="<?= get_avatar_url($u['avatar'], $u['name']) ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(147,51,234,0.4);flex-shrink:0;">
                <div>
                  <div class="activity-text"><strong><?= sanitize_output($u['name']) ?></strong></div>
                  <div class="activity-time"><?= sanitize_output($u['email']) ?> &middot; <?= time_ago($u['created_at']) ?></div>
                </div>
              </div>
              <?php endforeach; ?>
              <?php if (!$recentUsers): ?>
              <div class="p-3 text-center" style="color:rgba(196,181,253,0.4);font-size:0.85rem;">No visitors yet</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-12">
          <div class="glass-panel">
            <h3 style="font-size:1rem;font-weight:700;color:#fff;margin-bottom:1.5rem;font-family:'Space Grotesk',sans-serif;">
              <i class="bi bi-lightning-fill" style="color:#D946EF;margin-right:6px;"></i> Quick Actions
            </h3>
            <div class="row g-3">
              <?php
              $actions = [
                ['/portfolio/admin/projects.php?action=add',     'bi-plus-circle',  '#9333EA', 'Add Project'],
                ['/portfolio/admin/blog.php?action=new',         'bi-pen',          '#4F46E5', 'Write Post'],
                ['/portfolio/admin/skills.php',                  'bi-lightning',    '#D946EF', 'Add Skill'],
                ['/portfolio/admin/media.php',                   'bi-upload',       '#0EA5E9', 'Upload File'],
                ['/portfolio/admin/messages.php',                'bi-chat-dots',    '#10B981', 'View Messages'],
                ['/portfolio/admin/settings.php',                'bi-palette',      '#F59E0B', 'Customize Theme'],
                ['/portfolio/admin/achievements.php',            'bi-award',        '#EF4444', 'Add Achievement'],
                ['/portfolio/admin/backup.php',                  'bi-cloud-arrow-down','#8B5CF6','Backup DB'],
              ];
              foreach ($actions as [$url, $icon, $color, $label]):
              ?>
              <div class="col-6 col-md-3">
                <a href="<?= $url ?>" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none transition-all hover-lift" style="background:rgba(255,255,255,0.04);border:1px solid rgba(196,181,253,0.1);">
                  <div style="width:40px;height:40px;border-radius:12px;background:<?= $color ?>22;display:flex;align-items:center;justify-content:center;">
                    <i class="bi <?= $icon ?>" style="color:<?= $color ?>;font-size:1.1rem;"></i>
                  </div>
                  <span style="font-family:'Space Grotesk',sans-serif;font-size:0.85rem;color:rgba(255,255,255,0.8);font-weight:500;"><?= $label ?></span>
                </a>
              </div>
              <?php endforeach; ?>
            </div>
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
