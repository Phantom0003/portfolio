<?php
// admin/partials/sidebar.php
$currentFile = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
$admin = current_user();
$unreadMsg = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM messages WHERE is_read=0")['c'] ?? 0);
$pendingComments = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM comments WHERE status='pending'")['c'] ?? 0);
?>
<aside class="admin-sidebar" id="admin-sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logo"><i class="bi bi-rocket-takeoff-fill"></i></div>
    <div>
      <div class="sidebar-brand-text">Galaxy CMS</div>
      <div class="sidebar-brand-sub"><?= sanitize_output(is_super_admin() ? 'Super Admin' : 'Admin Panel') ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Overview</div>
    <a href="/portfolio/admin/dashboard.php" class="sidebar-link <?= $currentFile==='dashboard.php'?'active':'' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="sidebar-section-label">Portfolio</div>
    <a href="/portfolio/admin/profile.php" class="sidebar-link <?= $currentFile==='profile.php'?'active':'' ?>">
      <i class="bi bi-person-badge"></i> My Profile
    </a>
    <a href="/portfolio/admin/projects.php" class="sidebar-link <?= $currentFile==='projects.php'?'active':'' ?>">
      <i class="bi bi-folder2-open"></i> Projects
    </a>
    <a href="/portfolio/admin/skills.php" class="sidebar-link <?= $currentFile==='skills.php'?'active':'' ?>">
      <i class="bi bi-lightning-fill"></i> Skills
    </a>
    <a href="/portfolio/admin/experience.php" class="sidebar-link <?= $currentFile==='experience.php'?'active':'' ?>">
      <i class="bi bi-clock-history"></i> Experience
    </a>
    <a href="/portfolio/admin/achievements.php" class="sidebar-link <?= $currentFile==='achievements.php'?'active':'' ?>">
      <i class="bi bi-award-fill"></i> Achievements
    </a>

    <div class="sidebar-section-label">Content</div>
    <a href="/portfolio/admin/blog.php" class="sidebar-link <?= $currentFile==='blog.php'||$currentFile==='blog-editor.php'?'active':'' ?>">
      <i class="bi bi-newspaper"></i> Blog
    </a>
    <a href="/portfolio/admin/media.php" class="sidebar-link <?= $currentFile==='media.php'?'active':'' ?>">
      <i class="bi bi-images"></i> Media Library
    </a>

    <div class="sidebar-section-label">Engagement</div>
    <a href="/portfolio/admin/messages.php" class="sidebar-link <?= $currentFile==='messages.php'?'active':'' ?>">
      <i class="bi bi-chat-dots"></i> Messages
      <?php if($unreadMsg): ?><span class="sidebar-badge"><?= $unreadMsg ?></span><?php endif; ?>
    </a>
    <a href="/portfolio/admin/users.php" class="sidebar-link <?= $currentFile==='users.php'?'active':'' ?>">
      <i class="bi bi-people"></i> Visitors
    </a>
    <a href="/portfolio/admin/notifications.php" class="sidebar-link <?= $currentFile==='notifications.php'?'active':'' ?>">
      <i class="bi bi-bell"></i> Notifications
    </a>

    <div class="sidebar-section-label">System</div>
    <a href="/portfolio/admin/settings.php" class="sidebar-link <?= $currentFile==='settings.php'?'active':'' ?>">
      <i class="bi bi-gear"></i> Settings & Theme
    </a>
    <a href="/portfolio/admin/backup.php" class="sidebar-link <?= $currentFile==='backup.php'?'active':'' ?>">
      <i class="bi bi-cloud-arrow-down"></i> Backup
    </a>
    <?php if(is_super_admin()): ?>
    <a href="/portfolio/admin/super-admin.php" class="sidebar-link <?= $currentFile==='super-admin.php'?'active':'' ?>">
      <i class="bi bi-shield-lock-fill"></i> Super Admin
    </a>
    <?php endif; ?>

    <div class="sidebar-section-label">Navigation</div>
    <a href="/portfolio/" target="_blank" class="sidebar-link">
      <i class="bi bi-box-arrow-up-right"></i> View Portfolio
    </a>
    <a href="/portfolio/logout.php" class="sidebar-link" style="color:rgba(239,68,68,0.7);">
      <i class="bi bi-box-arrow-left"></i> Logout
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user-card">
      <img src="<?= get_avatar_url($admin['avatar'] ?? null, $admin['name']) ?>" class="sidebar-user-avatar" alt="">
      <div style="overflow:hidden;">
        <div class="sidebar-user-name"><?= sanitize_output($admin['name']) ?></div>
        <div class="sidebar-user-role"><?= sanitize_output($admin['role'] ?? 'admin') ?></div>
      </div>
    </div>
  </div>
</aside>
