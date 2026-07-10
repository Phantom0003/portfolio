<?php
/**
 * admin/blog-editor.php — Blog Post Editor (Quill.js)
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/upload.php';
require_admin();

$editId   = (int)($_GET['id'] ?? 0);
$editPost = $editId ? Database::fetchOne("SELECT * FROM blog_posts WHERE id=?", [$editId]) : null;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title   = strip_and_trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    if (!$title) { $msg = 'error|Title is required.'; }
    else {
        $uploader = new FileUploader();
        $cover = null;
        if (!empty($_FILES['cover_image']['name'])) {
            $up = $uploader->upload('cover_image', 'blog', 'image');
            if ($up['success']) $cover = $up['path'];
        }
        $tags    = $_POST['tags'] ?? '[]';
        $status  = $_POST['status'] ?? 'draft';
        $data = [
            'admin_id'     => current_user_id(),
            'title'        => $title,
            'slug'         => unique_slug('blog_posts', $title, $editId ?: null),
            'excerpt'      => strip_and_trim($_POST['excerpt'] ?? ''),
            'content'      => $content,
            'tags'         => $tags,
            'category'     => strip_and_trim($_POST['category'] ?? ''),
            'status'       => $status,
            'is_featured'  => isset($_POST['is_featured']) ? 1 : 0,
            'reading_time' => reading_time($content),
            'published_at' => $status === 'published' ? ($editPost['published_at'] ?? date('Y-m-d H:i:s')) : null,
        ];
        if ($cover) $data['cover_image'] = $cover;

        if ($editId) {
            Database::update('blog_posts', $data, 'id=?', [$editId]);
            $msg = 'success|Post updated!';
            $editPost = Database::fetchOne("SELECT * FROM blog_posts WHERE id=?", [$editId]);
        } else {
            $newId = Database::insert('blog_posts', $data);
            redirect('/portfolio/admin/blog-editor.php?id=' . $newId . '&saved=1');
        }
    }
}
[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title><?= $editPost ? 'Edit' : 'New' ?> Post — Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
  <link rel="stylesheet" href="/portfolio/assets/css/space.css">
  <link rel="stylesheet" href="/portfolio/assets/css/animations.css">
  <link rel="stylesheet" href="/portfolio/assets/css/glassmorphism.css">
  <link rel="stylesheet" href="/portfolio/assets/css/main.css">
  <link rel="stylesheet" href="/portfolio/assets/css/admin.css">
  <style>
    .ql-toolbar { background:rgba(107,33,168,0.15); border:1px solid rgba(196,181,253,0.2)!important; border-radius:12px 12px 0 0; }
    .ql-container { background:rgba(255,255,255,0.03); border:1px solid rgba(196,181,253,0.2)!important; border-top:none!important; border-radius:0 0 12px 12px; min-height:350px; color:rgba(255,255,255,0.85); font-family:'Inter',sans-serif; }
    .ql-editor { min-height:350px; font-size:0.95rem; line-height:1.8; }
    .ql-toolbar .ql-stroke { stroke:#C4B5FD; }
    .ql-toolbar .ql-fill { fill:#C4B5FD; }
    .ql-toolbar button:hover .ql-stroke { stroke:#fff; }
  </style>
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
          <h1 class="admin-page-title"><?= $editPost ? 'Edit Post' : 'New Post' ?></h1>
          <div class="admin-breadcrumb"><i class="bi bi-newspaper"></i> Blog &rsaquo; <?= $editPost ? 'Edit' : 'New' ?></div>
        </div>
        <a href="/portfolio/admin/blog.php" class="btn-galaxy btn-outline-galaxy"><i class="bi bi-arrow-left"></i> Back</a>
      </div>

      <?php if ($msgText || isset($_GET['saved'])): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?? 'success' ?> mb-3">
        <i class="bi bi-check-circle-fill"></i> <?= sanitize_output($msgText ?? 'Post saved!') ?>
      </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="galaxy-form" id="editor-form">
        <?= csrf_field() ?>
        <div class="row g-4">
          <!-- Main Content -->
          <div class="col-lg-8">
            <div class="admin-form-section">
              <div class="mb-3">
                <label class="form-label">Post Title *</label>
                <input type="text" name="title" id="input-title" class="galaxy-input form-control" style="font-size:1.2rem;font-weight:700;" placeholder="Enter an engaging title..." value="<?= sanitize_output($editPost['title'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Excerpt</label>
                <textarea name="excerpt" rows="2" class="galaxy-input form-control" placeholder="Brief summary shown in listings..."><?= sanitize_output($editPost['excerpt'] ?? '') ?></textarea>
              </div>
              <div>
                <label class="form-label">Content *</label>
                <div id="quill-editor"><?= $editPost ? $editPost['content'] : '' ?></div>
                <input type="hidden" name="content" id="content-hidden">
              </div>
            </div>
          </div>

          <!-- Sidebar Options -->
          <div class="col-lg-4">
            <!-- Publish Box -->
            <div class="glass-panel mb-4">
              <h4 style="font-size:0.9rem;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;margin-bottom:1rem;">Publish</h4>
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="galaxy-input form-select">
                  <option value="draft" <?= ($editPost['status']??'draft')==='draft'?'selected':'' ?>>Draft</option>
                  <option value="published" <?= ($editPost['status']??'')==='published'?'selected':'' ?>>Published</option>
                  <option value="archived" <?= ($editPost['status']??'')==='archived'?'selected':'' ?>>Archived</option>
                </select>
              </div>
              <div class="d-flex align-items-center gap-2 mb-4">
                <label class="galaxy-toggle">
                  <input type="checkbox" name="is_featured" <?= ($editPost['is_featured']??0)?'checked':'' ?>>
                  <span class="galaxy-toggle-slider"></span>
                </label>
                <span style="color:rgba(196,181,253,0.7);font-size:0.88rem;">Featured Post</span>
              </div>
              <button type="submit" class="btn-galaxy btn-primary-galaxy w-100" onclick="syncContent()">
                <i class="bi bi-save"></i> Save Post
              </button>
            </div>

            <!-- Cover Image -->
            <div class="glass-panel mb-4">
              <h4 style="font-size:0.9rem;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;margin-bottom:1rem;">Cover Image</h4>
              <?php if ($editPost['cover_image'] ?? null): ?>
              <img src="/portfolio/uploads/<?= sanitize_output($editPost['cover_image']) ?>" id="cover-preview" style="width:100%;height:120px;object-fit:cover;border-radius:10px;margin-bottom:0.75rem;" alt="">
              <?php else: ?>
              <img id="cover-preview" style="display:none;width:100%;height:120px;object-fit:cover;border-radius:10px;margin-bottom:0.75rem;" alt="">
              <?php endif; ?>
              <input type="file" name="cover_image" class="galaxy-input form-control" accept="image/*" data-preview="cover-preview">
            </div>

            <!-- Meta -->
            <div class="glass-panel">
              <h4 style="font-size:0.9rem;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;margin-bottom:1rem;">Meta</h4>
              <div class="mb-3">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="galaxy-input form-control" placeholder="e.g. Career, Dev Tips" value="<?= sanitize_output($editPost['category'] ?? '') ?>">
              </div>
              <div>
                <label class="form-label">Tags (press Enter)</label>
                <div id="tag-container"><input type="text" id="tag-input" placeholder="Add tag..."></div>
                <input type="hidden" name="tags" id="tags-hidden" value="<?= sanitize_output($editPost['tags'] ?? '[]') ?>">
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
<script>
const quill = new Quill('#quill-editor', {
  theme: 'snow',
  modules: {
    toolbar: [
      [{ header: [1,2,3,false] }],
      ['bold','italic','underline','strike'],
      ['blockquote','code-block'],
      [{ list: 'ordered'},{ list: 'bullet'}],
      [{ color:[] },{ background:[] }],
      ['link','image'],
      ['clean']
    ]
  }
});
function syncContent() {
  document.getElementById('content-hidden').value = quill.root.innerHTML;
}
document.getElementById('editor-form').addEventListener('submit', syncContent);
</script>
</body>
</html>
