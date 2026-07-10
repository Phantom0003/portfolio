<?php
/**
 * experience.php — Standalone Experience & Education Page
 * Galaxy Portfolio
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$currentPage = 'experience';
$pageTitle   = 'My Journey — ' . get_setting('site_name', 'Galaxy Portfolio');
$pageDesc    = 'Explore my academic milestones and professional experience timeline.';

require_once __DIR__ . '/includes/header.php';

// Fetch all experiences
$experiences = Database::fetchAll("SELECT * FROM experience ORDER BY start_date DESC, sort_order DESC");
?>

<section class="section" style="padding-top:120px;position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Timeline</span>
      <h2 class="section-title text-gradient">My Space Orbit</h2>
      <p class="section-subtitle mx-auto">A chronologically charted path of my career and academic adventures</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="timeline reveal">
          <?php if ($experiences): ?>
            <?php foreach ($experiences as $exp): ?>
            <div class="timeline-item">
              <div class="timeline-dot" style="background:<?= sanitize_output($exp['color'] ?? '#9333EA') ?>;box-shadow: 0 0 15px <?= sanitize_output($exp['color'] ?? '#9333EA') ?>;"></div>
              <div class="timeline-content">
                <div class="timeline-date d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <span>
                    <?= format_date($exp['start_date'], 'M Y') ?> &mdash; 
                    <?= $exp['is_current'] ? 'Present' : format_date($exp['end_date'], 'M Y') ?>
                  </span>
                  <span class="galaxy-badge" style="font-size:0.7rem;text-transform:uppercase;color:<?= sanitize_output($exp['color'] ?? '#9333EA') ?>;border-color:<?= sanitize_output($exp['color'] ?? '#9333EA') ?>33;background:<?= sanitize_output($exp['color'] ?? '#9333EA') ?>11;">
                    <?= ucfirst(sanitize_output($exp['type'] ?? 'work')) ?>
                  </span>
                </div>
                
                <h4 class="timeline-title"><?= sanitize_output($exp['title']) ?></h4>
                
                <div class="timeline-org mb-3">
                  <i class="bi <?= sanitize_output($exp['icon'] ?? 'bi-briefcase') ?>" style="color:<?= sanitize_output($exp['color'] ?? '#9333EA') ?>;"></i>
                  <?= sanitize_output($exp['organization']) ?>
                  <?php if ($exp['location']): ?>
                    <span style="color:rgba(255,255,255,0.4);margin-left:0.5rem;"><i class="bi bi-geo-alt"></i> <?= sanitize_output($exp['location']) ?></span>
                  <?php endif; ?>
                </div>

                <?php if ($exp['description']): ?>
                <p class="mb-0" style="font-size:0.9rem;color:rgba(255,255,255,0.65);line-height:1.7;"><?= nl2br(sanitize_output($exp['description'])) ?></p>
                <?php endif; ?>

                <?php if ($exp['url']): ?>
                <div class="mt-3">
                  <a href="<?= sanitize_output($exp['url']) ?>" target="_blank" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy" style="font-size:0.75rem;">
                    <i class="bi bi-link-45deg"></i> Visit Organization
                  </a>
                </div>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center py-5">
              <div class="glass-panel py-5" style="color:rgba(196,181,253,0.4);">
                <i class="bi bi-clock" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
                No journey data populated.
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
