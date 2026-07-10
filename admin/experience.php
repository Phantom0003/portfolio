<?php
/**
 * admin/experience.php — Experience Timeline Manager
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
        $id           = (int)($_POST['id'] ?? 0);
        $title        = strip_and_trim($_POST['title'] ?? '');
        $org          = strip_and_trim($_POST['organization'] ?? '');
        $startDate    = strip_and_trim($_POST['start_date'] ?? '');
        
        if (!$title || !$org || !$startDate) {
            $msg = 'error|Job title, organization, and start date are required.';
        } else {
            $endDate = strip_and_trim($_POST['end_date'] ?? '');
            $data = [
                'title'        => $title,
                'organization' => $org,
                'type'         => $_POST['type'] ?? 'work',
                'description'  => $_POST['description'] ?? '',
                'start_date'   => $startDate,
                'end_date'     => $endDate !== '' ? $endDate : null,
                'is_current'   => isset($_POST['is_current']) ? 1 : 0,
                'location'     => strip_and_trim($_POST['location'] ?? ''),
                'url'          => strip_and_trim($_POST['url'] ?? ''),
                'icon'         => strip_and_trim($_POST['icon'] ?? 'bi-briefcase'),
                'color'        => strip_and_trim($_POST['color'] ?? '#9333EA'),
                'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            ];
            
            if ($data['is_current']) {
                $data['end_date'] = null;
            }

            if ($id) {
                Database::update('experience', $data, 'id=?', [$id]);
                $msg = 'success|Timeline event updated!';
            } else {
                Database::insert('experience', $data);
                $msg = 'success|Timeline event added!';
            }
        }
    }
    
    if ($pa === 'delete') {
        Database::delete('experience', 'id=?', [(int)($_POST['id'] ?? 0)]);
        $msg = 'success|Timeline event deleted.';
    }
}

$experiences = Database::fetchAll("SELECT * FROM experience ORDER BY start_date DESC, sort_order DESC");
$editId = (int)($_GET['id'] ?? 0);
$editExp = $editId ? Database::fetchOne("SELECT * FROM experience WHERE id=?", [$editId]) : null;
[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];

$typeLabels = [
    'work'        => 'Work Experience',
    'education'   => 'Education',
    'achievement' => 'Achievement',
    'milestone'   => 'Milestone'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Experience Manager — Admin Panel</title>
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
          <h1 class="admin-page-title">Experience Timeline</h1>
          <div class="admin-breadcrumb"><i class="bi bi-clock-history"></i> Portfolio &rsaquo; Experience</div>
        </div>
        <button class="btn-galaxy btn-primary-galaxy" data-bs-toggle="modal" data-bs-target="#expModal">
          <i class="bi bi-plus-lg"></i> Add Event
        </button>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-3">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill' ?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <div class="admin-table-wrap">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid rgba(196,181,253,0.1);">
          <h3 style="margin:0;font-size:0.9rem;font-weight:700;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;">Timeline Events</h3>
        </div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Type</th>
              <th>Title</th>
              <th>Organization</th>
              <th>Duration</th>
              <th>Location</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($experiences as $exp): ?>
            <tr>
              <td>
                <span class="galaxy-badge" style="color:<?= sanitize_output($exp['color']) ?>;border-color:<?= sanitize_output($exp['color']) ?>33;background:<?= sanitize_output($exp['color']) ?>11;font-size:0.75rem;">
                  <i class="bi <?= sanitize_output($exp['icon']) ?> me-1"></i>
                  <?= $typeLabels[$exp['type']] ?? ucfirst($exp['type']) ?>
                </span>
              </td>
              <td><span style="font-weight:600;color:#fff;"><?= sanitize_output($exp['title']) ?></span></td>
              <td><span style="color:rgba(255,255,255,0.8);"><?= sanitize_output($exp['organization']) ?></span></td>
              <td>
                <span style="font-size:0.8rem;color:rgba(196,181,253,0.6);">
                  <?= format_date($exp['start_date'], 'M Y') ?> &mdash; 
                  <?= $exp['is_current'] ? 'Present' : format_date($exp['end_date'], 'M Y') ?>
                </span>
              </td>
              <td><span style="font-size:0.85rem;color:rgba(255,255,255,0.6);"><?= sanitize_output($exp['location'] ?: 'N/A') ?></span></td>
              <td>
                <div class="d-flex gap-2">
                  <a href="?id=<?= $exp['id'] ?>" class="topbar-icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this timeline event?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $exp['id'] ?>">
                    <button type="submit" class="topbar-icon-btn" style="color:rgba(239,68,68,0.7);" title="Delete"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$experiences): ?>
            <tr>
              <td colspan="6" class="text-center py-5" style="color:rgba(196,181,253,0.4);">
                No timeline events yet. Add your first experience!
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Experience Modal -->
<div class="modal fade glass-modal" id="expModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-gradient"><?= $editExp ? 'Edit' : 'Add' ?> Timeline Event</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" class="galaxy-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editExp['id'] ?? 0 ?>">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Job / Degree Title *</label>
              <input type="text" name="title" class="galaxy-input form-control" value="<?= sanitize_output($editExp['title'] ?? '') ?>" required placeholder="e.g. Lead Full Stack Developer">
            </div>
            <div class="col-md-6">
              <label class="form-label">Organization / School *</label>
              <input type="text" name="organization" class="galaxy-input form-control" value="<?= sanitize_output($editExp['organization'] ?? '') ?>" required placeholder="e.g. Google, Harvard University">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Event Type</label>
              <select name="type" class="galaxy-input form-select">
                <?php foreach ($typeLabels as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= ($editExp['type']??'work')===$val?'selected':'' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Icon (Bootstrap Icon)</label>
              <input type="text" name="icon" class="galaxy-input form-control" placeholder="bi-briefcase" value="<?= sanitize_output($editExp['icon'] ?? 'bi-briefcase') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Color</label>
              <input type="color" name="color" class="galaxy-input form-control" value="<?= sanitize_output($editExp['color'] ?? '#9333EA') ?>" style="height:46px;">
            </div>

            <div class="col-md-4">
              <label class="form-label">Start Date *</label>
              <input type="date" name="start_date" class="galaxy-input form-control" value="<?= sanitize_output($editExp['start_date'] ?? '') ?>" required>
            </div>
            <div class="col-md-4" id="end-date-wrapper" style="<?= ($editExp['is_current'] ?? 0) ? 'display:none;' : '' ?>">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" class="galaxy-input form-control" value="<?= sanitize_output($editExp['end_date'] ?? '') ?>">
            </div>
            <div class="col-md-4 d-flex align-items-center gap-2 pt-4">
              <label class="galaxy-toggle">
                <input type="checkbox" name="is_current" id="is_current" <?= ($editExp['is_current'] ?? 0) ? 'checked' : '' ?>>
                <span class="galaxy-toggle-slider"></span>
              </label>
              <span style="color:rgba(196,181,253,0.7);font-size:0.88rem;">Current / Ongoing</span>
            </div>

            <div class="col-md-6">
              <label class="form-label">Location</label>
              <input type="text" name="location" class="galaxy-input form-control" placeholder="e.g. Remote, San Francisco" value="<?= sanitize_output($editExp['location'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Website URL</label>
              <input type="url" name="url" class="galaxy-input form-control" placeholder="https://..." value="<?= sanitize_output($editExp['url'] ?? '') ?>">
            </div>

            <div class="col-12">
              <label class="form-label">Description / Responsibilities</label>
              <textarea name="description" rows="4" class="galaxy-input form-control" placeholder="Describe your achievements, roles, tasks..."><?= sanitize_output($editExp['description'] ?? '') ?></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label">Sort Order</label>
              <input type="number" name="sort_order" class="galaxy-input form-control" value="<?= $editExp['sort_order'] ?? 0 ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-galaxy btn-outline-galaxy" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-galaxy btn-primary-galaxy"><i class="bi bi-save"></i> Save Event</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
<script>
document.getElementById('is_current')?.addEventListener('change', function() {
    const endWrap = document.getElementById('end-date-wrapper');
    if (endWrap) {
        endWrap.style.display = this.checked ? 'none' : 'block';
    }
});
</script>
<?php if ($editExp): ?>
<script>document.addEventListener('DOMContentLoaded',()=>new bootstrap.Modal(document.getElementById('expModal')).show());</script>
<?php endif; ?>
</body>
</html>
