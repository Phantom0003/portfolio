<?php
/**
 * admin/media.php — Media Library Manager
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/upload.php';
require_admin();

$msg = '';
$uploader = new FileUploader();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pa = $_POST['action'] ?? '';
    
    if ($pa === 'upload') {
        $folder = strip_and_trim($_POST['folder'] ?? 'general');
        if (!in_array($folder, ['profile', 'projects', 'certificates', 'blog', 'documents', 'general'])) {
            $folder = 'general';
        }
        
        $up = $uploader->upload('media_file', $folder, 'all');
        if ($up['success']) {
            $msg = 'success|File uploaded to media library!';
        } else {
            $msg = 'error|' . $up['message'];
        }
    }
    
    if ($pa === 'delete') {
        $mediaId = (int)($_POST['id'] ?? 0);
        $media = Database::fetchOne("SELECT path FROM media WHERE id = ?", [$mediaId]);
        if ($media) {
            $uploader->delete($media['path']);
            $msg = 'success|File deleted from library.';
        } else {
            $msg = 'error|File not found.';
        }
    }
}

// Folders list
$folders = ['all' => 'All Sectors', 'general' => 'General', 'profile' => 'Profile', 'projects' => 'Projects', 'certificates' => 'Certificates', 'blog' => 'Blog', 'documents' => 'Documents'];

$selectedFolder = strip_and_trim($_GET['folder'] ?? 'all');

// Build SQL
$sql = "SELECT * FROM media";
$params = [];
if ($selectedFolder !== 'all') {
    $sql .= " WHERE folder = ?";
    $params[] = $selectedFolder;
}
$sql .= " ORDER BY created_at DESC";

$mediaFiles = Database::fetchAll($sql, $params);
[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Media Library — Admin Panel</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/portfolio/assets/css/space.css">
  <link rel="stylesheet" href="/portfolio/assets/css/animations.css">
  <link rel="stylesheet" href="/portfolio/assets/css/glassmorphism.css">
  <link rel="stylesheet" href="/portfolio/assets/css/main.css">
  <link rel="stylesheet" href="/portfolio/assets/css/admin.css">
  <style>
    .media-card {
      position: relative;
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid rgba(196,181,253,0.1);
      background: rgba(255,255,255,0.02);
      transition: all 0.3s ease;
    }
    .media-card:hover {
      transform: translateY(-5px);
      border-color: rgba(147,51,234,0.3);
      box-shadow: 0 5px 15px rgba(147,51,234,0.1);
    }
    .media-preview-box {
      height: 160px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0,0,0,0.2);
      overflow: hidden;
      position: relative;
    }
    .media-preview-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .media-info {
      padding: 0.8rem;
    }
    .media-name {
      font-size: 0.82rem;
      font-weight: 600;
      color: #fff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .media-meta {
      font-size: 0.72rem;
      color: rgba(196,181,253,0.5);
      margin-top: 0.2rem;
    }
    .media-actions {
      position: absolute;
      top: 8px;
      right: 8px;
      display: flex;
      gap: 4px;
      opacity: 0;
      transition: opacity 0.2s ease;
      z-index: 5;
    }
    .media-card:hover .media-actions {
      opacity: 1;
    }
    .media-action-btn {
      width: 28px;
      height: 28px;
      border-radius: 6px;
      border: none;
      background: rgba(0,0,0,0.6);
      backdrop-filter: blur(5px);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8rem;
      transition: all 0.2s ease;
    }
    .media-action-btn:hover {
      background: #9333EA;
      color: #fff;
    }
    .media-action-btn.btn-delete:hover {
      background: #EF4444;
    }
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
          <h1 class="admin-page-title">Media Library</h1>
          <div class="admin-breadcrumb"><i class="bi bi-images"></i> Content &rsaquo; Media</div>
        </div>
        <button class="btn-galaxy btn-primary-galaxy" data-bs-toggle="modal" data-bs-target="#uploadModal">
          <i class="bi bi-upload"></i> Upload File
        </button>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-4">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill' ?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <!-- Filter Tabs -->
      <div class="d-flex flex-wrap gap-2 mb-4">
        <?php foreach ($folders as $fold => $lbl): ?>
        <a href="/portfolio/admin/media.php?folder=<?= urlencode($fold) ?>" 
           class="btn-galaxy btn-sm-galaxy btn-<?= $selectedFolder === $fold ? 'primary' : 'outline' ?>-galaxy">
          <?= $lbl ?>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Files Grid -->
      <div class="row g-3">
        <?php if ($mediaFiles): ?>
          <?php foreach ($mediaFiles as $file): ?>
          <?php 
          $isImage = strpos($file['file_type'], 'image/') === 0;
          $icon = 'bi-file-earmark-check';
          if ($file['file_type'] === 'application/pdf') $icon = 'bi-file-pdf-fill';
          ?>
          <div class="col-6 col-sm-4 col-md-3">
            <div class="media-card">
              <!-- Actions hover overlay -->
              <div class="media-actions">
                <button type="button" class="media-action-btn" onclick="copyLink('<?= sanitize_output($file['url']) ?>')" title="Copy URL">
                  <i class="bi bi-link-45deg"></i>
                </button>
                <a href="<?= sanitize_output($file['url']) ?>" target="_blank" class="media-action-btn" title="View File">
                  <i class="bi bi-eye"></i>
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this file?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $file['id'] ?>">
                  <button type="submit" class="media-action-btn btn-delete" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
              </div>

              <!-- Preview Box -->
              <div class="media-preview-box">
                <?php if ($isImage): ?>
                  <img src="<?= sanitize_output($file['url']) ?>" alt="">
                <?php else: ?>
                  <div class="text-center">
                    <i class="bi <?= $icon ?>" style="font-size:3rem;color:rgba(196,181,253,0.3);"></i>
                  </div>
                <?php endif; ?>
              </div>

              <!-- File Info -->
              <div class="media-info">
                <div class="media-name" title="<?= sanitize_output($file['original_name']) ?>">
                  <?= sanitize_output($file['original_name']) ?>
                </div>
                <div class="media-meta d-flex justify-content-between">
                  <span class="text-uppercase"><?= sanitize_output($file['folder']) ?></span>
                  <span><?= format_bytes($file['file_size']) ?></span>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center py-5">
            <div class="glass-panel py-5" style="color:rgba(196,181,253,0.4);">
              <i class="bi bi-images" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
              No media files found in this folder.
            </div>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<!-- Upload Modal -->
<div class="modal fade glass-modal" id="uploadModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-gradient">Upload File</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data" class="galaxy-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="upload">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Select File (Images, PDF. Max: 10MB)</label>
              <input type="file" name="media_file" class="galaxy-input form-control" required accept="image/*,application/pdf">
            </div>
            
            <div class="col-12">
              <label class="form-label">Sector / Folder</label>
              <select name="folder" class="galaxy-input form-select">
                <option value="general">General</option>
                <option value="blog">Blog Assets</option>
                <option value="projects">Projects Assets</option>
                <option value="certificates">Certificates</option>
                <option value="profile">Profile Assets</option>
                <option value="documents">Documents</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-galaxy btn-outline-galaxy" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-galaxy btn-primary-galaxy"><i class="bi bi-upload"></i> Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
<script>
function copyLink(url) {
  const fullUrl = window.location.origin + url;
  navigator.clipboard.writeText(fullUrl).then(() => {
    if (window.showToast) {
      window.showToast('Copied media URL to clipboard!', 'success');
    } else {
      alert('Link copied: ' + fullUrl);
    }
  }).catch(() => {
    alert('Failed to copy. URL: ' + fullUrl);
  });
}
</script>
</body>
</html>
