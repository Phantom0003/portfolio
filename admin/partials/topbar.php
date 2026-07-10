<?php // admin/partials/topbar.php ?>
<header class="admin-topbar">
  <div class="d-flex align-items-center gap-3">
    <button class="topbar-icon-btn d-md-none" id="sidebar-toggle">
      <i class="bi bi-list fs-5"></i>
    </button>
    <div>
      <div class="topbar-title" id="topbar-title">Admin Panel</div>
    </div>
  </div>
  <div class="topbar-actions">
    <!-- Notifications -->
    <a href="/portfolio/admin/notifications.php" class="topbar-icon-btn" title="Notifications">
      <i class="bi bi-bell"></i>
      <?php if($unreadN??0): ?><span class="topbar-notif-dot"></span><?php endif; ?>
    </a>
    <!-- Messages -->
    <a href="/portfolio/admin/messages.php" class="topbar-icon-btn" title="Messages">
      <i class="bi bi-chat-dots"></i>
      <?php
      $unreadMsgTop = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM messages WHERE is_read=0")['c']??0);
      if($unreadMsgTop): ?><span class="topbar-notif-dot" style="background:#10B981;"></span><?php endif; ?>
    </a>
    <!-- View Site -->
    <a href="/portfolio/" target="_blank" class="topbar-icon-btn" title="View Portfolio">
      <i class="bi bi-globe2"></i>
    </a>
    <!-- Profile -->
    <div class="dropdown">
      <button class="topbar-icon-btn" data-bs-toggle="dropdown">
        <img src="<?= get_avatar_url(current_user()['avatar']??null, current_user()['name']) ?>" style="width:24px;height:24px;border-radius:50%;object-fit:cover;" alt="">
      </button>
      <ul class="dropdown-menu dropdown-menu-end" style="background:rgba(19,0,37,0.95);backdrop-filter:blur(20px);border:1px solid rgba(196,181,253,0.15);border-radius:12px;min-width:180px;">
        <li><a class="dropdown-item" href="/portfolio/admin/profile.php" style="color:rgba(196,181,253,0.8);font-size:0.85rem;"><i class="bi bi-person me-2"></i>My Profile</a></li>
        <li><a class="dropdown-item" href="/portfolio/admin/settings.php" style="color:rgba(196,181,253,0.8);font-size:0.85rem;"><i class="bi bi-gear me-2"></i>Settings</a></li>
        <li><hr class="dropdown-divider" style="border-color:rgba(196,181,253,0.1);"></li>
        <li><a class="dropdown-item" href="/portfolio/logout.php" style="color:rgba(239,68,68,0.8);font-size:0.85rem;"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</header>
