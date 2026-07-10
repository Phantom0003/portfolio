<?php
/**
 * admin/skills.php — Skills Manager
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
    if ($pa === 'save') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = strip_and_trim($_POST['name'] ?? '');
        if (!$name) { $msg = 'error|Skill name is required.'; }
        else {
            $data = [
                'name'        => $name,
                'category'    => $_POST['category'] ?? 'other',
                'level'       => min(100, max(0, (int)($_POST['level'] ?? 80))),
                'icon'        => strip_and_trim($_POST['icon'] ?? 'bi-circle'),
                'color'       => strip_and_trim($_POST['color'] ?? '#9333EA'),
                'sort_order'  => (int)($_POST['sort_order'] ?? 0),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ];
            if ($id) { Database::update('skills', $data, 'id=?', [$id]); $msg = 'success|Skill updated!'; }
            else { Database::insert('skills', $data); $msg = 'success|Skill added!'; }
        }
    }
    if ($pa === 'delete') {
        Database::delete('skills', 'id=?', [(int)($_POST['id'] ?? 0)]);
        $msg = 'success|Skill deleted.';
    }
}

$skills = Database::fetchAll("SELECT * FROM skills ORDER BY category, sort_order, name");
$editId = (int)($_GET['id'] ?? 0);
$editSkill = $editId ? Database::fetchOne("SELECT * FROM skills WHERE id=?", [$editId]) : null;
[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
$catLabels = ['frontend'=>'Frontend','backend'=>'Backend','database'=>'Database','tools'=>'Tools','languages'=>'Languages','devops'=>'DevOps','design'=>'Design','other'=>'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Skills — Admin</title>
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
        <div><h1 class="admin-page-title">Skills</h1><div class="admin-breadcrumb"><i class="bi bi-lightning-fill"></i> Portfolio &rsaquo; Skills</div></div>
        <button class="btn-galaxy btn-primary-galaxy" data-bs-toggle="modal" data-bs-target="#skillModal">
          <i class="bi bi-plus-lg"></i> Add Skill
        </button>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-3">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill' ?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <?php
      $grouped = [];
      foreach ($skills as $s) $grouped[$s['category']][] = $s;
      foreach ($grouped as $cat => $catSkills):
      ?>
      <div class="admin-table-wrap mb-4">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid rgba(196,181,253,0.1);">
          <h3 style="margin:0;font-size:0.9rem;font-weight:700;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;"><?= $catLabels[$cat] ?? ucfirst($cat) ?></h3>
        </div>
        <table class="admin-table">
          <thead><tr><th>Icon</th><th>Skill</th><th>Level</th><th>Featured</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($catSkills as $skill): ?>
            <tr>
              <td><i class="bi <?= sanitize_output($skill['icon']) ?>" style="font-size:1.3rem;color:<?= sanitize_output($skill['color']) ?>;"></i></td>
              <td><span style="font-weight:600;color:#fff;"><?= sanitize_output($skill['name']) ?></span></td>
              <td style="min-width:200px;">
                <div class="d-flex align-items-center gap-2">
                  <div class="skill-bar-track flex-grow-1">
                    <div class="skill-bar-fill" data-level="<?= $skill['level'] ?>" style="width:<?= $skill['level'] ?>%;background:linear-gradient(90deg,<?= sanitize_output($skill['color']) ?>,#D946EF);"></div>
                  </div>
                  <span style="font-size:0.78rem;color:<?= sanitize_output($skill['color']) ?>;font-family:'Orbitron',monospace;width:35px;"><?= $skill['level'] ?>%</span>
                </div>
              </td>
              <td>
                <?php if ($skill['is_featured']): ?>
                <span class="status-pill status-active"><i class="bi bi-star-fill"></i> Yes</span>
                <?php else: ?>
                <span style="color:rgba(196,181,253,0.3);font-size:0.78rem;">No</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex gap-2">
                  <a href="?id=<?= $skill['id'] ?>" class="topbar-icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this skill?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $skill['id'] ?>">
                    <button type="submit" class="topbar-icon-btn" style="color:rgba(239,68,68,0.7);" title="Delete"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endforeach; ?>
      <?php if (!$skills): ?>
      <div class="glass-panel text-center py-5" style="color:rgba(196,181,253,0.4);">
        <i class="bi bi-lightning" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
        No skills yet. Add your first skill!
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Skill Modal -->
<div class="modal fade glass-modal" id="skillModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-gradient"><?= $editSkill ? 'Edit' : 'Add' ?> Skill</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" class="galaxy-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editSkill['id'] ?? 0 ?>">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Skill Name *</label>
              <input type="text" name="name" class="galaxy-input form-control" value="<?= sanitize_output($editSkill['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Color</label>
              <input type="color" name="color" class="galaxy-input form-control" value="<?= sanitize_output($editSkill['color'] ?? '#9333EA') ?>" style="height:46px;">
            </div>
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <select name="category" class="galaxy-input form-select">
                <?php foreach ($catLabels as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= ($editSkill['category']??'other')===$val?'selected':'' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Icon (Bootstrap Icons class)</label>
              <input type="text" name="icon" class="galaxy-input form-control" placeholder="bi-code-slash" value="<?= sanitize_output($editSkill['icon'] ?? 'bi-circle') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Skill Level: <span id="skill-level-display"><?= $editSkill['level'] ?? 80 ?>%</span></label>
              <input type="range" name="level" id="skill-level" min="0" max="100" value="<?= $editSkill['level'] ?? 80 ?>" class="form-range w-100">
            </div>
            <div class="col-md-6">
              <label class="form-label">Sort Order</label>
              <input type="number" name="sort_order" class="galaxy-input form-control" value="<?= $editSkill['sort_order'] ?? 0 ?>">
            </div>
            <div class="col-md-6 d-flex align-items-center gap-2 pt-3">
              <label class="galaxy-toggle">
                <input type="checkbox" name="is_featured" <?= ($editSkill['is_featured'] ?? 0) ? 'checked' : '' ?>>
                <span class="galaxy-toggle-slider"></span>
              </label>
              <span style="color:rgba(196,181,253,0.7);font-size:0.88rem;">Featured on homepage</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-galaxy btn-outline-galaxy" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-galaxy btn-primary-galaxy"><i class="bi bi-save"></i> Save Skill</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
<?php if ($editSkill): ?>
<script>document.addEventListener('DOMContentLoaded',()=>new bootstrap.Modal(document.getElementById('skillModal')).show());</script>
<?php endif; ?>
</body>
</html>
