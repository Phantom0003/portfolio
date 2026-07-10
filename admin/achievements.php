<?php
/**
 * admin/achievements.php — Achievements & Certificates Manager
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/upload.php';
require_admin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pa = $_POST['action'] ?? '';
    
    if ($pa === 'save') {
        $id           = (int)($_POST['id'] ?? 0);
        $title        = strip_and_trim($_POST['title'] ?? '');
        $issuer       = strip_and_trim($_POST['issuer'] ?? '');
        
        if (!$title || !$issuer) {
            $msg = 'error|Achievement title and issuer are required.';
        } else {
            $data = [
                'title'          => $title,
                'issuer'         => $issuer,
                'type'           => $_POST['type'] ?? 'certificate',
                'description'    => $_POST['description'] ?? '',
                'date_awarded'   => $_POST['date_awarded'] !== '' ? $_POST['date_awarded'] : null,
                'expiry_date'    => $_POST['expiry_date'] !== '' ? $_POST['expiry_date'] : null,
                'credential_id'  => strip_and_trim($_POST['credential_id'] ?? ''),
                'credential_url' => strip_and_trim($_POST['credential_url'] ?? ''),
                'is_featured'    => isset($_POST['is_featured']) ? 1 : 0,
                'sort_order'     => (int)($_POST['sort_order'] ?? 0),
            ];
            
            // Handle certificate file upload
            if (!empty($_FILES['cert_file']['name'])) {
                $uploader = new FileUploader();
                $up = $uploader->upload('cert_file', 'certificates', 'document');
                if ($up['success']) {
                    $data['file_path'] = $up['path'];
                } else {
                    $msg = 'error|' . $up['message'];
                }
            }

            if (empty($msg) || strpos($msg, 'success|') === 0) {
                if ($id) {
                    Database::update('achievements', $data, 'id=?', [$id]);
                    $msg = 'success|Achievement updated!';
                } else {
                    Database::insert('achievements', $data);
                    $msg = 'success|Achievement added!';
                }
            }
        }
    }
    
    if ($pa === 'delete') {
        Database::delete('achievements', 'id=?', [(int)($_POST['id'] ?? 0)]);
        $msg = 'success|Achievement deleted.';
    }
}

$achievements = Database::fetchAll("SELECT * FROM achievements ORDER BY sort_order, date_awarded DESC");
$editId = (int)($_GET['id'] ?? 0);
$editAch = $editId ? Database::fetchOne("SELECT * FROM achievements WHERE id=?", [$editId]) : null;
[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];

$typeLabels = [
    'certificate' => 'Certificate',
    'award'       => 'Award',
    'badge'       => 'Badge',
    'event'       => 'Event Milestone',
    'publication' => 'Publication',
    'other'       => 'Other'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Achievements Manager — Admin Panel</title>
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
          <h1 class="admin-page-title">Achievements & Badges</h1>
          <div class="admin-breadcrumb"><i class="bi bi-award-fill"></i> Portfolio &rsaquo; Achievements</div>
        </div>
        <button class="btn-galaxy btn-primary-galaxy" data-bs-toggle="modal" data-bs-target="#achModal">
          <i class="bi bi-plus-lg"></i> Add Achievement
        </button>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-3">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill' ?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <div class="admin-table-wrap">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid rgba(196,181,253,0.1);">
          <h3 style="margin:0;font-size:0.9rem;font-weight:700;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;">Awarded Badges</h3>
        </div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Type</th>
              <th>Title</th>
              <th>Issuer</th>
              <th>Award Date</th>
              <th>Featured</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($achievements as $ach): ?>
            <tr>
              <td>
                <span class="galaxy-badge" style="font-size:0.75rem;">
                  <?= $typeLabels[$ach['type']] ?? ucfirst($ach['type']) ?>
                </span>
              </td>
              <td><span style="font-weight:600;color:#fff;"><?= sanitize_output($ach['title']) ?></span></td>
              <td><span style="color:rgba(255,255,255,0.8);"><?= sanitize_output($ach['issuer']) ?></span></td>
              <td>
                <span style="font-size:0.8rem;color:rgba(196,181,253,0.6);">
                  <?= $ach['date_awarded'] ? format_date($ach['date_awarded'], 'M Y') : 'N/A' ?>
                </span>
              </td>
              <td>
                <?php if ($ach['is_featured']): ?>
                <span class="status-pill status-active"><i class="bi bi-star-fill"></i> Yes</span>
                <?php else: ?>
                <span style="color:rgba(196,181,253,0.3);font-size:0.78rem;">No</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex gap-2">
                  <a href="?id=<?= $ach['id'] ?>" class="topbar-icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this achievement?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $ach['id'] ?>">
                    <button type="submit" class="topbar-icon-btn" style="color:rgba(239,68,68,0.7);" title="Delete"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$achievements): ?>
            <tr>
              <td colspan="6" class="text-center py-5" style="color:rgba(196,181,253,0.4);">
                No achievements yet. Add your first badge!
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Achievement Modal -->
<div class="modal fade glass-modal" id="achModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-gradient"><?= $editAch ? 'Edit' : 'Add' ?> Achievement</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data" class="galaxy-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editAch['id'] ?? 0 ?>">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Title *</label>
              <input type="text" name="title" class="galaxy-input form-control" value="<?= sanitize_output($editAch['title'] ?? '') ?>" required placeholder="e.g. AWS Certified Solutions Architect">
            </div>
            <div class="col-md-6">
              <label class="form-label">Issuer *</label>
              <input type="text" name="issuer" class="galaxy-input form-control" value="<?= sanitize_output($editAch['issuer'] ?? '') ?>" required placeholder="e.g. Amazon Web Services, freeCodeCamp">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Type</label>
              <select name="type" class="galaxy-input form-select">
                <?php foreach ($typeLabels as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= ($editAch['type']??'certificate')===$val?'selected':'' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Award Date</label>
              <input type="date" name="date_awarded" class="galaxy-input form-control" value="<?= sanitize_output($editAch['date_awarded'] ?? '') ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Expiry Date</label>
              <input type="date" name="expiry_date" class="galaxy-input form-control" value="<?= sanitize_output($editAch['expiry_date'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Credential ID</label>
              <input type="text" name="credential_id" class="galaxy-input form-control" placeholder="e.g. AWS-SEC-123" value="<?= sanitize_output($editAch['credential_id'] ?? '') ?>">
            </div>

            <div class="col-12">
              <label class="form-label">Credential Verification URL</label>
              <input type="url" name="credential_url" class="galaxy-input form-control" placeholder="https://..." value="<?= sanitize_output($editAch['credential_url'] ?? '') ?>">
            </div>

            <div class="col-md-8">
              <label class="form-label">Upload File (PDF / Image proof)</label>
              <input type="file" name="cert_file" class="galaxy-input form-control" accept="image/*,application/pdf">
              <?php if ($editAch && $editAch['file_path']): ?>
              <div class="mt-1" style="font-size:0.8rem;color:rgba(196,181,253,0.5);">
                Current file: <a href="/portfolio/uploads/<?= sanitize_output($editAch['file_path']) ?>" target="_blank" style="color:#D946EF;"><?= basename($editAch['file_path']) ?></a>
              </div>
              <?php endif; ?>
            </div>

            <div class="col-md-4 d-flex align-items-center gap-2 pt-4">
              <label class="galaxy-toggle">
                <input type="checkbox" name="is_featured" <?= ($editAch['is_featured'] ?? 0) ? 'checked' : '' ?>>
                <span class="galaxy-toggle-slider"></span>
              </label>
              <span style="color:rgba(196,181,253,0.7);font-size:0.88rem;">Featured on homepage</span>
            </div>

            <div class="col-12">
              <label class="form-label">Short Description</label>
              <textarea name="description" rows="3" class="galaxy-input form-control" placeholder="Describe this credential, course contents, or achievement context..."><?= sanitize_output($editAch['description'] ?? '') ?></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label">Sort Order</label>
              <input type="number" name="sort_order" class="galaxy-input form-control" value="<?= $editAch['sort_order'] ?? 0 ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-galaxy btn-outline-galaxy" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-galaxy btn-primary-galaxy"><i class="bi bi-save"></i> Save Achievement</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
<?php if ($editAch): ?>
<script>document.addEventListener('DOMContentLoaded',()=>new bootstrap.Modal(document.getElementById('achModal')).show());</script>
<?php endif; ?>
</body>
</html>
