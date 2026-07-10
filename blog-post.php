<?php
/**
 * blog-post.php — Single Blog Post & Comments
 * Galaxy Portfolio
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$slug = strip_and_trim($_GET['slug'] ?? '');

if (!$slug) {
    redirect('/portfolio/blog.php');
}

// Fetch post
$post = Database::fetchOne("SELECT b.*, a.name AS author_name, a.avatar AS author_avatar FROM blog_posts b JOIN admins a ON b.admin_id = a.id WHERE b.slug = ? AND b.status = 'published'", [$slug]);

if (!$post) {
    redirect('/portfolio/blog.php');
}

// Increment views
Database::query("UPDATE blog_posts SET views = views + 1 WHERE id = ?", [$post['id']]);

$currentPage = 'blog';
$pageTitle   = $post['title'] . ' — ' . get_setting('site_name', 'Galaxy Portfolio');
$pageDesc    = $post['excerpt'] ?? substr(strip_tags($post['content']), 0, 150);

require_once __DIR__ . '/includes/header.php';

// Comment Submission Handler
$commentMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $commentMsg = 'error|Security token mismatch';
    } else {
        $content = strip_and_trim($_POST['content'] ?? '');
        
        if (is_logged_in()) {
            $userId     = current_user_id();
            $guestName  = null;
            $guestEmail = null;
        } else {
            $userId     = null;
            $guestName  = strip_and_trim($_POST['guest_name'] ?? '');
            $guestEmail = strip_and_trim($_POST['guest_email'] ?? '');
        }

        if (!$content) {
            $commentMsg = 'error|Please enter comment content.';
        } elseif (!is_logged_in() && (!$guestName || !$guestEmail)) {
            $commentMsg = 'error|Name and email are required for guests.';
        } elseif (!is_logged_in() && !is_valid_email($guestEmail)) {
            $commentMsg = 'error|Please enter a valid email address.';
        } else {
            Database::insert('comments', [
                'post_id'     => $post['id'],
                'user_id'     => $userId,
                'guest_name'  => $guestName,
                'guest_email' => $guestEmail,
                'content'     => $content,
                'status'      => 'pending' // Moderated by default
            ]);
            
            // Notify Admin
            create_notification('admin', 1, 'new_comment', '💬 New Comment Pending', 
                "On post: " . $post['title'], "/portfolio/admin/blog.php");

            $commentMsg = 'success|Your comment is submitted and awaiting moderation.';
        }
    }
}

// Fetch comments
$comments = Database::fetchAll("SELECT c.*, u.name AS user_name, u.avatar AS user_avatar FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.post_id = ? AND c.status = 'approved' ORDER BY c.created_at DESC", [$post['id']]);

// Check if liked
$userId = is_logged_in() ? current_user_id() : null;
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if ($userId) {
    $isLiked = Database::fetchOne("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?", [$post['id'], $userId]);
} else {
    $isLiked = Database::fetchOne("SELECT id FROM post_likes WHERE post_id = ? AND ip_address = ? AND user_id IS NULL", [$post['id'], $ip]);
}

[$commentMsgType, $commentMsgText] = $commentMsg ? explode('|', $commentMsg, 2) : [null, null];
?>

<section class="section" style="padding-top:120px;position:relative;z-index:10;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        
        <!-- Breadcrumb & Back -->
        <div class="mb-4 reveal">
          <a href="/portfolio/blog.php" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy">
            <i class="bi bi-arrow-left"></i> All Chronicles
          </a>
        </div>

        <!-- Article Card -->
        <article class="glass-panel p-4 p-md-5 mb-5 reveal">
          <!-- Post Meta -->
          <div class="d-flex align-items-center gap-3 mb-4">
            <span class="galaxy-badge"><?= sanitize_output($post['category'] ?? 'General') ?></span>
            <span style="font-size:0.85rem;color:rgba(196,181,253,0.5);">
              <i class="bi bi-calendar3"></i> <?= format_date($post['published_at']) ?>
            </span>
            <span style="font-size:0.85rem;color:rgba(196,181,253,0.5);">
              <i class="bi bi-clock"></i> <?= (int)$post['reading_time'] ?> min read
            </span>
            <span style="font-size:0.85rem;color:rgba(196,181,253,0.5);">
              <i class="bi bi-eye"></i> <?= (int)$post['views'] ?> views
            </span>
          </div>

          <h1 class="text-gradient mb-4" style="font-family:'Space Grotesk',sans-serif;font-weight:800;font-size:2.2rem;line-height:1.3;">
            <?= sanitize_output($post['title']) ?>
          </h1>

          <!-- Cover Image -->
          <?php if ($post['cover_image'] && file_exists(__DIR__ . '/uploads/' . $post['cover_image'])): ?>
          <div class="mb-4 text-center">
            <img src="/portfolio/uploads/<?= sanitize_output($post['cover_image']) ?>" class="img-fluid rounded-4" 
                 style="max-height:450px;width:100%;object-fit:cover;border:1px solid rgba(196,181,253,0.25);" alt="">
          </div>
          <?php endif; ?>

          <!-- Post Content -->
          <div class="blog-post-content text-white-50" style="line-height:1.9;font-size:1.05rem;">
            <?= $post['content'] ?>
          </div>

          <!-- Like Section -->
          <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top" style="border-top-color:rgba(196,181,253,0.1)!important;">
            <div class="d-flex align-items-center gap-2">
              <button id="like-btn" class="btn-galaxy btn-sm-galaxy <?= $isLiked ? 'liked' : 'btn-outline-galaxy' ?>" 
                      data-post-id="<?= $post['id'] ?>" style="border-radius:20px;padding:0.4rem 1rem;">
                <i class="bi bi-heart<?= $isLiked ? '-fill' : '' ?>" style="color:<?= $isLiked ? '#D946EF' : '' ?>;"></i> 
                <span id="like-count" class="ms-1"><?= (int)$post['likes'] ?></span> Likes
              </button>
            </div>
            
            <!-- Tags -->
            <?php
            $tags = json_decode_safe($post['tags']);
            if ($tags):
            ?>
            <div class="d-flex gap-1 flex-wrap">
              <?php foreach ($tags as $tag): ?>
                <span class="galaxy-badge" style="font-size:0.75rem;">#<?= sanitize_output($tag) ?></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </article>

        <!-- Comments Section -->
        <section class="comments-section reveal" id="comments">
          <h3 class="text-white mb-4" style="font-family:'Space Grotesk',sans-serif;font-weight:700;">
            <i class="bi bi-chat-left-text" style="color:#9333EA;"></i> Comments (<?= count($comments) ?>)
          </h3>

          <!-- Comment Alerts -->
          <?php if ($commentMsgText): ?>
          <div class="galaxy-alert galaxy-alert-<?= $commentMsgType ?> mb-4">
            <i class="bi bi-<?= $commentMsgType==='success'?'check-circle-fill':'x-circle-fill'?>"></i> 
            <span><?= sanitize_output($commentMsgText) ?></span>
          </div>
          <?php endif; ?>

          <!-- Submit Comment Form -->
          <div class="glass-panel p-4 mb-5">
            <h4 class="text-white mb-3" style="font-size:1.05rem;font-weight:700;">Leave a Comment</h4>
            <form method="POST" class="galaxy-form">
              <?= csrf_field() ?>
              
              <?php if (!is_logged_in()): ?>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label">Name <span style="color:#D946EF;">*</span></label>
                  <input type="text" name="guest_name" class="galaxy-input form-control" placeholder="Your Name" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email <span style="color:#D946EF;">*</span></label>
                  <input type="email" name="guest_email" class="galaxy-input form-control" placeholder="your@email.com" required>
                </div>
              </div>
              <?php else: ?>
              <div class="mb-3 d-flex align-items-center gap-2" style="font-size:0.88rem;color:rgba(196,181,253,0.7);">
                <i class="bi bi-person-circle"></i> Logged in as <strong><?= sanitize_output($_SESSION['user_name']) ?></strong>
              </div>
              <?php endif; ?>

              <div class="mb-3">
                <label class="form-label">Comment <span style="color:#D946EF;">*</span></label>
                <textarea name="content" rows="4" class="galaxy-input form-control" placeholder="Share your thoughts..." required></textarea>
              </div>

              <button type="submit" name="submit_comment" class="btn-galaxy btn-primary-galaxy">
                <i class="bi bi-send"></i> Submit Comment
              </button>
            </form>
          </div>

          <!-- Comments List -->
          <div class="comments-list d-flex flex-column gap-3">
            <?php if ($comments): ?>
              <?php foreach ($comments as $comment): ?>
              <div class="glass-card p-3 d-flex gap-3">
                <div class="flex-shrink-0">
                  <img src="<?= get_avatar_url($comment['user_avatar'] ?? null, $comment['user_name'] ?? $comment['guest_name']) ?>" 
                       style="width:45px;height:45px;border-radius:50%;object-fit:cover;border:2px solid rgba(147,51,234,0.4);" alt="">
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <strong style="color:#fff;font-size:0.92rem;">
                      <?= sanitize_output($comment['user_name'] ?? $comment['guest_name']) ?>
                    </strong>
                    <?php if ($comment['user_id'] === null): ?>
                      <span class="galaxy-badge" style="font-size:0.6rem;padding:0.1rem 0.4rem;">Guest</span>
                    <?php endif; ?>
                    <span style="font-size:0.75rem;color:rgba(196,181,253,0.4);">
                      <?= time_ago($comment['created_at']) ?>
                    </span>
                  </div>
                  <p class="mb-0" style="font-size:0.88rem;color:rgba(255,255,255,0.75);line-height:1.6;white-space:pre-line;">
                    <?= sanitize_output($comment['content']) ?>
                  </p>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-4 glass-card" style="color:rgba(196,181,253,0.4);font-size:0.88rem;">
                No comments yet. Be the first to share your thoughts!
              </div>
            <?php endif; ?>
          </div>
        </section>

      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
