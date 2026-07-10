<?php
/**
 * visitor/dashboard.php — Visitor Dashboard
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/upload.php';

require_visitor();

$user    = current_user();
$userId  = current_user_id();
$unread  = get_unread_notifications('user', $userId);
$msg     = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    csrf_check();
    $name = strip_and_trim($_POST['name'] ?? '');
    $bio  = strip_and_trim($_POST['bio'] ?? '');
    $web  = strip_and_trim($_POST['website'] ?? '');

    if (!$name) { $msg = 'error|Name is required.'; }
    else {
        $data = ['name' => $name, 'bio' => $bio, 'website' => $web];

        // Avatar upload
        if (!empty($_FILES['avatar']['name'])) {
            $uploader = new FileUploader();
            $up = $uploader->upload('avatar', 'profile', 'image');
            if ($up['success']) $data['avatar'] = $up['path'];
        }

        Database::update('users', $data, 'id=?', [$userId]);
        // Refresh session
        $updated = Database::fetchOne("SELECT * FROM users WHERE id=?", [$userId]);
        $_SESSION['current_user'] = $updated;
        $_SESSION['user_name']    = $updated['name'];
        $user = $updated;
        $msg = 'success|Profile updated!';
    }
}

$savedProjects    = Database::fetchAll("SELECT p.* FROM projects p JOIN saved_projects s ON p.id=s.project_id WHERE s.user_id=? ORDER BY s.created_at DESC", [$userId]);
$myComments       = Database::fetchAll("SELECT c.*, b.title AS post_title, b.slug AS post_slug FROM comments c JOIN blog_posts b ON c.post_id=b.id WHERE c.user_id=? ORDER BY c.created_at DESC LIMIT 10", [$userId]);
$notifications    = Database::fetchAll("SELECT * FROM notifications WHERE recipient_type='user' AND recipient_id=? ORDER BY created_at DESC LIMIT 20", [$userId]);

[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];

$siteName = get_setting('site_name', 'Galaxy Portfolio');
$ownerName = get_setting('owner_name', 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>My Dashboard — <?= sanitize_output($siteName) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/portfolio/assets/css/animations.css">
  <link rel="stylesheet" href="/portfolio/assets/css/space.css">
  <link rel="stylesheet" href="/portfolio/assets/css/glassmorphism.css">
  <link rel="stylesheet" href="/portfolio/assets/css/main.css">
</head>
<body>
<div class="galaxy-bg" style="position:fixed;"></div>
<canvas id="star-canvas"></canvas>
<div style="position:fixed;inset:0;z-index:0;"><div class="nebula-layer nebula-1"></div><div class="nebula-layer nebula-2"></div></div>

<!-- Visitor Nav -->
<nav class="galaxy-nav scrolled" style="position:sticky;">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="/portfolio/" class="nav-brand text-decoration-none">
      <span style="font-family:'Orbitron',monospace;"><?= sanitize_output(get_setting('site_name','Portfolio')) ?></span>
    </a>
    <div class="d-flex gap-2 align-items-center">
      <a href="/portfolio/" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy"><i class="bi bi-arrow-left"></i> Portfolio</a>
      <a href="/portfolio/logout.php" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy" style="color:rgba(239,68,68,0.8);"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container py-5" style="position:relative;z-index:10;">
  <?php if ($msgText): ?>
  <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-4">
    <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill'?>"></i> <?= sanitize_output($msgText) ?>
  </div>
  <?php endif; ?>

  <!-- Profile Header -->
  <div class="glass-panel mb-4 reveal">
    <div class="row align-items-center g-4">
      <div class="col-md-auto text-center">
        <img src="<?= get_avatar_url($user['avatar']??null, $user['name']) ?>" style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid rgba(147,51,234,0.5);box-shadow:0 0 30px rgba(147,51,234,0.3);" alt="">
      </div>
      <div class="col-md">
        <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:#fff;margin-bottom:0.3rem;"><?= sanitize_output($user['name']) ?></h2>
        <p style="color:rgba(196,181,253,0.6);font-size:0.9rem;margin-bottom:0.5rem;"><?= sanitize_output($user['email']) ?></p>
        <div class="d-flex flex-wrap gap-2">
          <span class="galaxy-badge"><i class="bi bi-person-check"></i> Visitor</span>
          <?php if ($user['website']): ?><a href="<?= sanitize_output($user['website']) ?>" target="_blank" class="galaxy-badge"><i class="bi bi-globe"></i> Website</a><?php endif; ?>
          <span class="galaxy-badge"><i class="bi bi-bookmark"></i> <?= count($savedProjects) ?> Saved</span>
        </div>
      </div>
      <div class="col-md-auto">
        <?php if ($unread): ?>
        <span class="galaxy-badge galaxy-badge-pinned" style="font-size:0.85rem;"><i class="bi bi-bell-fill"></i> <?= $unread ?> new</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <ul class="nav mb-4 gap-2" id="visitorTabs">
    <?php foreach(['profile'=>'bi-person Profile','saved'=>'bi-bookmark Saved Projects','comments'=>'bi-chat My Comments','notifications'=>'bi-bell Notifications'] as $tab => $info): ?>
    <?php [$icon, $text] = explode(' ', $info, 2); ?>
    <li class="nav-item">
      <button class="btn-galaxy btn-sm-galaxy visitor-tab-btn btn-<?= $tab==='profile'?'primary':'outline' ?>-galaxy" data-tab="vtab-<?= $tab ?>">
        <i class="bi <?= $icon ?>"></i> <?= $text ?>
        <?php if ($tab==='notifications' && $unread): ?><span class="sidebar-badge ms-1"><?= $unread ?></span><?php endif; ?>
      </button>
    </li>
    <?php endforeach; ?>
  </ul>

  <!-- Profile Tab -->
  <div id="vtab-profile" class="reveal">
    <div class="glass-panel">
      <h3 style="font-size:1rem;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;margin-bottom:1.5rem;">Edit Profile</h3>
      <form method="POST" enctype="multipart/form-data" class="galaxy-form">
        <?= csrf_field() ?>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="galaxy-input form-control" value="<?= sanitize_output($user['name']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Website</label>
            <input type="url" name="website" class="galaxy-input form-control" placeholder="https://..." value="<?= sanitize_output($user['website']??'') ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Bio</label>
            <textarea name="bio" rows="3" class="galaxy-input form-control" placeholder="Tell us about yourself..."><?= sanitize_output($user['bio']??'') ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Profile Photo</label>
            <input type="file" name="avatar" class="galaxy-input form-control" accept="image/*" data-preview="dash-avatar">
          </div>
        </div>
        <div class="mt-4">
          <button type="submit" name="update_profile" class="btn-galaxy btn-primary-galaxy">
            <i class="bi bi-save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Saved Projects Tab -->
  <div id="vtab-saved" style="display:none;">
    <?php if ($savedProjects): ?>
    <div class="row g-4">
      <?php foreach ($savedProjects as $proj): ?>
      <div class="col-lg-4 col-md-6">
        <div class="project-card h-100">
          <?php if ($proj['thumbnail']): ?>
          <img src="/portfolio/uploads/<?= sanitize_output($proj['thumbnail']) ?>" class="project-thumb" alt="">
          <?php else: ?>
          <div class="project-thumb d-flex align-items-center justify-content-center" style="background:rgba(107,33,168,0.2);"><i class="bi bi-code-slash" style="font-size:2rem;color:rgba(196,181,253,0.3);"></i></div>
          <?php endif; ?>
          <div class="card-body">
            <h3 class="project-title"><?= sanitize_output($proj['title']) ?></h3>
            <p class="project-desc"><?= sanitize_output($proj['description']) ?></p>
            <div class="d-flex gap-2">
              <?php if ($proj['demo_url']): ?><a href="<?= sanitize_output($proj['demo_url']) ?>" target="_blank" class="btn-galaxy btn-sm-galaxy btn-primary-galaxy"><i class="bi bi-box-arrow-up-right"></i> Demo</a><?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="glass-panel text-center py-5" style="color:rgba(196,181,253,0.4);">
      <i class="bi bi-bookmark" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
      No saved projects yet. <a href="/portfolio/#projects" style="color:#9333EA;">Browse projects</a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Comments Tab -->
  <div id="vtab-comments" style="display:none;">
    <?php if ($myComments): ?>
    <div class="admin-table-wrap">
      <?php foreach ($myComments as $c): ?>
      <div class="activity-item" style="padding:1rem;">
        <div class="activity-icon" style="background:rgba(79,70,229,0.15);"><i class="bi bi-chat-left" style="color:#4F46E5;"></i></div>
        <div>
          <div class="activity-text"><?= sanitize_output(substr($c['content'],0,120)) ?><?= strlen($c['content'])>120?'...':'' ?></div>
          <div class="activity-time">
            On: <a href="/portfolio/blog-post.php?slug=<?= urlencode($c['post_slug']) ?>" style="color:#9333EA;"><?= sanitize_output($c['post_title']) ?></a>
            &middot; <?= time_ago($c['created_at']) ?>
            &middot; <span class="status-pill status-<?= $c['status']==='approved'?'active':'pending' ?>"><?= ucfirst($c['status']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="glass-panel text-center py-5" style="color:rgba(196,181,253,0.4);">
      <i class="bi bi-chat" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
      You haven't commented yet.
    </div>
    <?php endif; ?>
  </div>

  <!-- Notifications Tab -->
  <div id="vtab-notifications" style="display:none;">
    <div class="glass-panel">
      <?php if ($notifications): ?>
      <?php foreach ($notifications as $n): ?>
      <div class="activity-item <?= !$n['is_read']?'':'opacity-60' ?>" style="padding:0.75rem;">
        <div class="activity-icon" style="background:<?= sanitize_output($n['color'] ?? '#9333EA') ?>22;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="bi <?= sanitize_output($n['icon']??'bi-bell') ?>" style="color:<?= sanitize_output($n['color']??'#9333EA') ?>;"></i>
        </div>
        <div>
          <div class="activity-text" style="font-weight:<?= $n['is_read']?400:600 ?>;"><?= sanitize_output($n['title']) ?></div>
          <?php if ($n['message']): ?><div style="font-size:0.8rem;color:rgba(196,181,253,0.5);"><?= sanitize_output($n['message']) ?></div><?php endif; ?>
          <div class="activity-time"><?= time_ago($n['created_at']) ?></div>
        </div>
        <?php if (!$n['is_read']): ?><div style="width:8px;height:8px;border-radius:50%;background:#9333EA;margin-left:auto;flex-shrink:0;"></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="text-center py-5" style="color:rgba(196,181,253,0.4);">
        <i class="bi bi-bell" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
        No notifications yet
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/main.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
<script>
document.querySelectorAll('.visitor-tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('[id^="vtab-"]').forEach(t => t.style.display='none');
    document.querySelectorAll('.visitor-tab-btn').forEach(b => b.classList.replace('btn-primary-galaxy','btn-outline-galaxy'));
    document.getElementById(this.dataset.tab).style.display='';
    this.classList.replace('btn-outline-galaxy','btn-primary-galaxy');
  });
});
// Mark notifications read
fetch('/portfolio/visitor/mark_notifs.php', {method:'POST', body:'csrf_token=<?= csrf_token() ?>'});
</script>
</body>
</html>
