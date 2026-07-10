<?php
/**
 * admin/backup.php — Database Backup Panel
 */
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_admin();

$msg = '';

// Handle Backup Export Request
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    // Basic csrf verification via query parameter is good, but let's check CSRF token
    if (!verify_csrf($_GET['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }

    try {
        $tables = [];
        $res = Database::query("SHOW TABLES");
        while ($row = $res->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sqlDump = "-- ====================================================\n";
        $sqlDump .= "-- Galaxy Portfolio Database Backup\n";
        $sqlDump .= "-- Exported on: " . date('Y-m-d H:i:s') . "\n";
        $sqlDump .= "-- ====================================================\n\n";
        
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Get structure
            $createTableRes = Database::query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
            $sqlDump .= $createTableRes['Create Table'] . ";\n\n";

            // Get data
            $rows = Database::fetchAll("SELECT * FROM `$table`");
            if (!empty($rows)) {
                $sqlDump .= "INSERT INTO `$table` VALUES \n";
                $insertRows = [];
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $val) {
                        if ($val === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = Database::getInstance()->quote($val);
                        }
                    }
                    $insertRows[] = "(" . implode(', ', $values) . ")";
                }
                $sqlDump .= implode(",\n", $insertRows) . ";\n\n";
            }
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Download headers
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="portfolio_backup_' . date('Ymd_His') . '.sql"');
        header('Content-Length: ' . strlen($sqlDump));
        echo $sqlDump;
        exit;

    } catch (Exception $e) {
        $msg = 'error|Failed to export backup: ' . $e->getMessage();
    }
}

// Handle Backup Import Request (Restore)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import') {
    csrf_check();

    if (!empty($_FILES['backup_file']['name'])) {
        $file = $_FILES['backup_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $msg = 'error|Upload error: ' . $file['error'];
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if ($ext !== 'sql') {
                $msg = 'error|Invalid file type. Please upload a .sql file.';
            } else {
                try {
                    $sqlContent = file_get_contents($file['tmp_name']);
                    
                    // Execute queries
                    Database::beginTransaction();
                    Database::getInstance()->exec($sqlContent);
                    Database::commit();
                    
                    $msg = 'success|Database restored successfully!';
                } catch (Exception $e) {
                    Database::rollback();
                    $msg = 'error|Failed to restore database: ' . $e->getMessage();
                }
            }
        }
    } else {
        $msg = 'error|Please select a .sql file to import.';
    }
}

[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Database Backup — Admin Panel</title>
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
          <h1 class="admin-page-title">Database Backup & Restore</h1>
          <div class="admin-breadcrumb"><i class="bi bi-cloud-arrow-down"></i> System &rsaquo; Backup</div>
        </div>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-4">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill' ?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <div class="row g-4">
        <!-- Export Card -->
        <div class="col-md-6">
          <div class="glass-panel h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:48px;height:48px;border-radius:12px;background:rgba(147,51,234,0.15);display:flex;align-items:center;justify-content:center;">
                  <i class="bi bi-cloud-download-fill" style="color:#9333EA;font-size:1.4rem;"></i>
                </div>
                <h3 style="font-size:1.1rem;color:#fff;margin:0;font-family:'Space Grotesk',sans-serif;font-weight:700;">
                  Export SQL Dump
                </h3>
              </div>
              <p style="color:rgba(196,181,253,0.6);font-size:0.9rem;line-height:1.6;margin-bottom:1.5rem;">
                Download a full snapshot of your galaxy portfolio. This contains all schema definitions, settings, project lists, timeline events, and visitor accounts.
              </p>
            </div>
            <div>
              <a href="?action=export&csrf_token=<?= csrf_token() ?>" class="btn-galaxy btn-primary-galaxy w-100 text-center">
                <i class="bi bi-download"></i> Generate & Download SQL
              </a>
            </div>
          </div>
        </div>

        <!-- Import Card -->
        <div class="col-md-6">
          <div class="glass-panel h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:48px;height:48px;border-radius:12px;background:rgba(217,70,239,0.15);display:flex;align-items:center;justify-content:center;">
                  <i class="bi bi-cloud-upload-fill" style="color:#D946EF;font-size:1.4rem;"></i>
                </div>
                <h3 style="font-size:1.1rem;color:#fff;margin:0;font-family:'Space Grotesk',sans-serif;font-weight:700;">
                  Restore Database
                </h3>
              </div>
              <p style="color:rgba(196,181,253,0.6);font-size:0.9rem;line-height:1.6;margin-bottom:1.5rem;">
                Upload a previously exported `.sql` backup file to restore your portfolio database. <strong style="color:#EF4444;">WARNING: This will overwrite all current data!</strong>
              </p>
            </div>
            <form method="POST" enctype="multipart/form-data" class="galaxy-form">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="import">
              <div class="mb-3">
                <input type="file" name="backup_file" class="galaxy-input form-control" accept=".sql" required>
              </div>
              <button type="submit" class="btn-galaxy btn-outline-galaxy w-100" style="color:#D946EF;border-color:rgba(217,70,239,0.3);" onclick="return confirm('Overwriting database is irreversible! Proceed?')">
                <i class="bi bi-upload"></i> Upload & Restore Backup
              </button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
</body>
</html>
