<?php
/**
 * achievements.php — Standalone Achievements & Certificates Page
 * Galaxy Portfolio
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$currentPage = 'achievements';
$pageTitle   = 'Achievements & Certifications — ' . get_setting('site_name', 'Galaxy Portfolio');
$pageDesc    = 'Explore my awards, badges, certificates, and accomplishments.';

require_once __DIR__ . '/includes/header.php';

// Fetch all achievements
$achievements = Database::fetchAll("SELECT * FROM achievements ORDER BY sort_order, date_awarded DESC");
?>

<section class="section" style="padding-top:120px;position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Certificates & Awards</span>
      <h2 class="section-title text-gradient">Star Badges</h2>
      <p class="section-subtitle mx-auto">Awards, credentials, and achievements earned across my professional flights</p>
    </div>

    <div class="row g-4 reveal">
      <?php if ($achievements): ?>
        <?php foreach ($achievements as $i => $ach): ?>
        <div class="col-lg-4 col-md-6 reveal delay-<?= ($i % 3) + 1 ?>">
          <div class="glass-card p-4 h-100 d-flex flex-column text-center">
            <!-- Icon/Visual -->
            <div class="mb-3 d-inline-flex align-items-center justify-content-center" 
                 style="width:70px;height:70px;border-radius:20px;background:linear-gradient(135deg,rgba(147,51,234,0.3),rgba(79,70,229,0.15));margin:0 auto;border:1px solid rgba(196,181,253,0.15);">
              <?php
              $icon = 'bi-award';
              if ($ach['type'] === 'certificate') $icon = 'bi-patch-check';
              if ($ach['type'] === 'badge')       $icon = 'bi-shield-check';
              if ($ach['type'] === 'event')       $icon = 'bi-calendar-event';
              ?>
              <i class="bi <?= $icon ?>" style="font-size:2rem;color:#C4B5FD;"></i>
            </div>

            <!-- Title & Issuer -->
            <h4 style="font-size:1.1rem;color:#fff;font-weight:700;margin-bottom:0.4rem;font-family:'Space Grotesk',sans-serif;">
              <?= sanitize_output($ach['title']) ?>
            </h4>
            
            <?php if ($ach['issuer']): ?>
            <p style="color:var(--purple-light);font-size:0.88rem;margin-bottom:0.8rem;font-weight:500;">
              <?= sanitize_output($ach['issuer']) ?>
            </p>
            <?php endif; ?>

            <?php if ($ach['description']): ?>
            <p style="font-size:0.85rem;color:rgba(255,255,255,0.6);margin-bottom:1.5rem;flex-grow: 1;">
              <?= sanitize_output($ach['description']) ?>
            </p>
            <?php endif; ?>

            <!-- Meta info & actions -->
            <div class="mt-auto pt-3 border-top border-light-subtle" style="border-top-color:rgba(196,181,253,0.1)!important;">
              <div class="d-flex justify-content-between align-items-center mb-3" style="font-size:0.78rem;color:rgba(196,181,253,0.5);">
                <span>
                  <i class="bi bi-calendar3"></i> 
                  <?= $ach['date_awarded'] ? format_date($ach['date_awarded'], 'M Y') : 'N/A' ?>
                </span>
                <?php if ($ach['credential_id']): ?>
                <span class="text-truncate" style="max-width:120px;" title="Credential ID: <?= sanitize_output($ach['credential_id']) ?>">
                  ID: <?= sanitize_output($ach['credential_id']) ?>
                </span>
                <?php endif; ?>
              </div>

              <div class="d-flex gap-2">
                <?php if ($ach['credential_url']): ?>
                <a href="<?= sanitize_output($ach['credential_url']) ?>" target="_blank" class="btn-galaxy btn-sm-galaxy btn-primary-galaxy w-100">
                  <i class="bi bi-box-arrow-up-right"></i> Verify Credential
                </a>
                <?php endif; ?>
                
                <?php if ($ach['file_path'] && file_exists(__DIR__ . '/uploads/' . $ach['file_path'])): ?>
                <a href="/portfolio/uploads/<?= sanitize_output($ach['file_path']) ?>" target="_blank" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy <?= !$ach['credential_url'] ? 'w-100' : '' ?>">
                  <i class="bi bi-eye"></i> View File
                </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <div class="glass-panel py-5" style="color:rgba(196,181,253,0.4);">
            <i class="bi bi-award" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
            No achievements recorded yet.
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
