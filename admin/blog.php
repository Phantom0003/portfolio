<?php
/**
 * admin/blog.php — Blog Manager
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
    if ($pa === 'delete') {
        Database::delete('blog_posts', 'id=?', [(int)($_POST['id']??0)]);
        $msg = 'success|Post deleted.';
    }
    if ($pa === 'toggle_status') {
        $id = (int)($_POST['id']??0);
        $post = Database::fetchOne("SELECT status FROM blog_posts WHERE id=?", [$id]);
        $newStatus = $post['status']==='published'?'draft':'published';
        Database::update('blog_posts', ['status'=>$newStatus,'published_at'=>$newStatus==='published'?date('Y-m-d H:i:s'):null], 'id=?', [$id]);
        $msg = 'success|Status changed to '.ucfirst($newStatus).'.';
    }
}

$posts = Database::fetchAll("SELECT b.*, a.name AS author_name FROM blog_posts b LEFT JOIN admins a ON b.admin_id=a.id ORDER BY b.created_at DESC");
[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Blog — Admin</title>
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
        <div><h1 class="admin-page-title">Blog</h1><div class="admin-breadcrumb"><i class="bi bi-newspaper"></i> Content &rsaquo; Blog</div></div>
        <a href="/portfolio/admin/blog-editor.php" class="btn-galaxy btn-primary-galaxy"><i class="bi bi-pen"></i> Write Post</a>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-3">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill'?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead><tr><th>Cover</th><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th>Views</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($posts as $post): ?>
            <tr>
              <td>
                <?php if ($post['cover_image']): ?>
                <img src="/portfolio/uploads/<?= sanitize_output($post['cover_image']) ?>" style="width:60px;height:40px;object-fit:cover;border-radius:8px;" alt="">
                <?php else: ?>
                <div style="width:60px;height:40px;background:rgba(147,51,234,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-newspaper" style="color:rgba(196,181,253,0.3);"></i></div>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-weight:600;color:#fff;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= sanitize_output($post['title']) ?></div>
                <div style="font-size:0.72rem;color:rgba(196,181,253,0.4);">by <?= sanitize_output($post['author_name'] ?? 'Admin') ?></div>
              </td>
              <td><?= $post['category'] ? '<span class="galaxy-badge">'.sanitize_output($post['category']).'</span>' : '—' ?></td>
              <td><span class="status-pill status-<?= $post['status']==='published'?'active':($post['status']==='draft'?'draft':'archived') ?>"><?= ucfirst($post['status']) ?></span></td>
              <td style="font-size:0.8rem;color:rgba(196,181,253,0.5);"><?= $post['published_at'] ? format_date($post['published_at']) : '—' ?></td>
              <td style="color:rgba(196,181,253,0.6);"><?= number_format($post['views']) ?></td>
              <td>
                <div class="d-flex gap-2">
                  <a href="/portfolio/admin/blog-editor.php?id=<?= $post['id'] ?>" class="topbar-icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                  <form method="POST" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                    <button type="submit" class="topbar-icon-btn" title="Toggle Publish" style="color:rgba(16,185,129,0.7);"><i class="bi bi-arrow-repeat"></i></button>
                  </form>
                  <a href="/portfolio/blog-post.php?slug=<?= urlencode($post['slug']) ?>" target="_blank" class="topbar-icon-btn" title="View"><i class="bi bi-eye"></i></a>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this post?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                    <button type="submit" class="topbar-icon-btn" style="color:rgba(239,68,68,0.7);" title="Delete"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$posts): ?><tr><td colspan="7" class="text-center py-4" style="color:rgba(196,181,253,0.4);">No blog posts yet. Write your first post!</td></tr><?php endif; ?>
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
