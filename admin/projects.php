<?php
/**
 * admin/projects.php — Project Manager CRUD
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/upload.php';

require_admin();

$uploader = new FileUploader();
$action   = $_GET['action'] ?? 'list';
$editId   = (int)($_GET['id'] ?? 0);
$msg      = '';

// ── Handle POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'save') {
        $id    = (int)($_POST['id'] ?? 0);
        $title = strip_and_trim($_POST['title'] ?? '');
        $desc  = strip_and_trim($_POST['description'] ?? '');
        $cat   = strip_and_trim($_POST['category'] ?? '');

        if (!$title || !$desc) { $msg = 'error|Title and description are required.'; }
        else {
            $slug   = unique_slug('projects', $title, $id ?: null);
            $techs  = $_POST['technologies'] ?? '[]';
            $thumb  = null;

            // Upload thumbnail
            if (!empty($_FILES['thumbnail']['name'])) {
                $up = $uploader->upload('thumbnail', 'projects', 'image');
                if ($up['success']) $thumb = $up['path'];
                else { $msg = 'error|' . $up['message']; goto done; }
            }

            $data = [
                'title'            => $title,
                'slug'             => $slug,
                'description'      => $desc,
                'long_description' => strip_tags($_POST['long_description'] ?? ''),
                'technologies'     => $techs,
                'category'         => $cat,
                'demo_url'         => strip_and_trim($_POST['demo_url'] ?? ''),
                'github_url'       => strip_and_trim($_POST['github_url'] ?? ''),
                'status'           => $_POST['status'] ?? 'active',
                'is_pinned'        => isset($_POST['is_pinned']) ? 1 : 0,
            ];
            if ($thumb) $data['thumbnail'] = $thumb;

            if ($id) { Database::update('projects', $data, 'id = ?', [$id]); $msg = 'success|Project updated!'; }
            else { Database::insert('projects', $data); $msg = 'success|Project added!'; }
        }
        done:
    }

    if ($postAction === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        Database::delete('projects', 'id = ?', [$id]);
        $msg = 'success|Project deleted.';
    }

    if ($postAction === 'toggle_pin') {
        $id = (int)($_POST['id'] ?? 0);
        $proj = Database::fetchOne("SELECT is_pinned FROM projects WHERE id=?", [$id]);
        Database::update('projects', ['is_pinned' => $proj['is_pinned'] ? 0 : 1], 'id=?', [$id]);
    }
}

// ── Fetch for display ────────────────────────────────────────
$search   = trim($_GET['q'] ?? '');
$category = trim($_GET['cat'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$where    = '1=1';
$params   = [];
if ($search)   { $where .= " AND (title LIKE ? OR description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($category) { $where .= " AND category = ?"; $params[] = $category; }
$paged  = paginate("SELECT * FROM projects WHERE $where ORDER BY is_pinned DESC, sort_order, created_at DESC", $params, $page, 12);
$categories = Database::fetchAll("SELECT DISTINCT category FROM projects WHERE category != '' ORDER BY category");
$editProject = $editId ? Database::fetchOne("SELECT * FROM projects WHERE id=?", [$editId]) : null;

[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Projects — Admin</title>
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
          <h1 class="admin-page-title">Projects</h1>
          <div class="admin-breadcrumb"><i class="bi bi-folder2-open"></i> Portfolio &rsaquo; Projects</div>
        </div>
        <button class="btn-galaxy btn-primary-galaxy" data-bs-toggle="modal" data-bs-target="#projectModal">
          <i class="bi bi-plus-lg"></i> Add Project
        </button>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-3">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill' ?>"></i>
        <span><?= sanitize_output($msgText) ?></span>
      </div>
      <?php endif; ?>

      <!-- Search/Filter -->
      <form method="GET" class="glass-card p-3 mb-4 d-flex gap-3 flex-wrap">
        <input type="text" name="q" class="galaxy-input form-control flex-grow-1" style="max-width:300px;" placeholder="Search projects..." value="<?= sanitize_output($search) ?>">
        <select name="cat" class="galaxy-input form-select" style="max-width:180px;">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
          <option value="<?= sanitize_output($c['category']) ?>" <?= $category===$c['category']?'selected':'' ?>><?= sanitize_output($c['category']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-galaxy btn-primary-galaxy"><i class="bi bi-search"></i></button>
        <?php if ($search||$category): ?><a href="/portfolio/admin/projects.php" class="btn-galaxy btn-outline-galaxy"><i class="bi bi-x"></i> Clear</a><?php endif; ?>
      </form>

      <!-- Table -->
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Thumbnail</th><th>Title</th><th>Category</th><th>Status</th><th>Pinned</th><th>Views</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paged['items'] as $proj): ?>
            <tr>
              <td>
                <?php if ($proj['thumbnail']): ?>
                <img src="/portfolio/uploads/<?= sanitize_output($proj['thumbnail']) ?>" style="width:60px;height:40px;object-fit:cover;border-radius:8px;border:1px solid rgba(147,51,234,0.3);" alt="">
                <?php else: ?>
                <div style="width:60px;height:40px;background:rgba(147,51,234,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-image" style="color:rgba(196,181,253,0.4);"></i></div>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-weight:600;color:#fff;"><?= sanitize_output($proj['title']) ?></div>
                <div style="font-size:0.75rem;color:rgba(196,181,253,0.4);"><?= sanitize_output($proj['slug']) ?></div>
              </td>
              <td><?= $proj['category'] ? '<span class="galaxy-badge">' . sanitize_output($proj['category']) . '</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' ?></td>
              <td><span class="status-pill status-<?= $proj['status'] ?>"><?= ucfirst($proj['status']) ?></span></td>
              <td>
                <form method="POST" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle_pin">
                  <input type="hidden" name="id" value="<?= $proj['id'] ?>">
                  <button type="submit" style="background:none;border:none;cursor:pointer;" title="Toggle Pin">
                    <i class="bi bi-pin<?= $proj['is_pinned']?'-fill':'' ?>" style="color:<?= $proj['is_pinned']?'#D946EF':'rgba(196,181,253,0.3)' ?>;font-size:1.1rem;"></i>
                  </button>
                </form>
              </td>
              <td style="color:rgba(196,181,253,0.6);"><?= number_format($proj['views']) ?></td>
              <td>
                <div class="d-flex gap-2">
                  <a href="?action=edit&id=<?= $proj['id'] ?>" class="topbar-icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this project?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $proj['id'] ?>">
                    <button type="submit" class="topbar-icon-btn" style="color:rgba(239,68,68,0.7);" title="Delete"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$paged['items']): ?>
            <tr><td colspan="7" class="text-center py-4" style="color:rgba(196,181,253,0.4);">No projects found</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($paged['total_pages'] > 1): ?>
        <div class="d-flex justify-content-center gap-2 p-3">
          <?php for ($p = 1; $p <= $paged['total_pages']; $p++): ?>
          <a href="?page=<?= $p ?>&q=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>"
             class="btn-galaxy btn-sm-galaxy <?= $p===$page?'btn-primary-galaxy':'btn-outline-galaxy' ?>"><?= $p ?></a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade glass-modal" id="projectModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-gradient"><?= $editProject ? 'Edit' : 'Add' ?> Project</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data" class="galaxy-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editProject['id'] ?? 0 ?>">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Title *</label>
              <input type="text" name="title" id="input-title" class="galaxy-input form-control" value="<?= sanitize_output($editProject['title'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Slug</label>
              <input type="text" name="slug" id="input-slug" class="galaxy-input form-control" value="<?= sanitize_output($editProject['slug'] ?? '') ?>" placeholder="auto-generated">
            </div>
            <div class="col-12">
              <label class="form-label">Short Description *</label>
              <textarea name="description" rows="2" class="galaxy-input form-control" required><?= sanitize_output($editProject['description'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Full Description</label>
              <textarea name="long_description" rows="5" class="galaxy-input form-control"><?= sanitize_output($editProject['long_description'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <input type="text" name="category" class="galaxy-input form-control" placeholder="e.g. Web App" value="<?= sanitize_output($editProject['category'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" class="galaxy-input form-select">
                <?php foreach (['active','in_progress','archived'] as $s): ?>
                <option value="<?= $s ?>" <?= ($editProject['status']??'active')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Technologies (press Enter or comma)</label>
              <div id="tech-container"><input type="text" id="tech-input" placeholder="Add tech..."></div>
              <input type="hidden" name="technologies" id="techs-hidden" value="<?= sanitize_output($editProject['technologies'] ?? '[]') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Demo URL</label>
              <input type="url" name="demo_url" class="galaxy-input form-control" placeholder="https://..." value="<?= sanitize_output($editProject['demo_url'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">GitHub URL</label>
              <input type="url" name="github_url" class="galaxy-input form-control" placeholder="https://github.com/..." value="<?= sanitize_output($editProject['github_url'] ?? '') ?>">
            </div>
            <div class="col-md-8">
              <label class="form-label">Thumbnail Image</label>
              <input type="file" name="thumbnail" class="galaxy-input form-control" accept="image/*" data-preview="thumb-preview">
              <?php if ($editProject['thumbnail'] ?? null): ?>
              <img src="/portfolio/uploads/<?= sanitize_output($editProject['thumbnail']) ?>" id="thumb-preview" style="height:80px;margin-top:0.5rem;border-radius:8px;" alt="">
              <?php else: ?>
              <img id="thumb-preview" style="display:none;height:80px;margin-top:0.5rem;border-radius:8px;" alt="">
              <?php endif; ?>
            </div>
            <div class="col-md-4 d-flex align-items-center">
              <div>
                <label class="form-label">Pin to Homepage</label><br>
                <label class="galaxy-toggle">
                  <input type="checkbox" name="is_pinned" <?= ($editProject['is_pinned'] ?? 0) ? 'checked' : '' ?>>
                  <span class="galaxy-toggle-slider"></span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-galaxy btn-outline-galaxy" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-galaxy btn-primary-galaxy"><i class="bi bi-save"></i> Save Project</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/main.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
<?php if ($editProject): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  new bootstrap.Modal(document.getElementById('projectModal')).show();
});
</script>
<?php endif; ?>
</body>
</html>
