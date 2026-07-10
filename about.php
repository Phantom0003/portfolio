<?php
/**
 * about.php — Standalone About Page
 * Galaxy Portfolio
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$currentPage = 'about';
$pageTitle   = 'About Me — ' . get_setting('site_name', 'Galaxy Portfolio');
$pageDesc    = get_setting('owner_bio', '');

require_once __DIR__ . '/includes/header.php';

// Stats
$yearsExp   = get_setting('years_experience', '5');
$clientsCnt = get_setting('clients_count', '20+');
$projCount  = Database::fetchOne("SELECT COUNT(*) as c FROM projects WHERE status != 'archived'")['c'];
$skillsCnt  = Database::fetchOne("SELECT COUNT(*) as c FROM skills")['c'];
?>

<section class="section" style="padding-top:120px;position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">My Story</span>
      <h2 class="section-title text-gradient">About Me</h2>
      <p class="section-subtitle mx-auto">Exploring my digital universe and professional timeline</p>
    </div>

    <div class="row g-5 align-items-center">
      <!-- Left column: Profile Visual -->
      <div class="col-lg-5 text-center reveal-left">
        <div class="astronaut-wrapper d-inline-block position-relative">
          <?php
          $avatar = get_setting('owner_avatar');
          if ($avatar && file_exists(__DIR__ . '/uploads/' . $avatar)):
          ?>
            <img src="/portfolio/uploads/<?= sanitize_output($avatar) ?>" alt="Profile" class="rounded-circle"
                 style="width:300px;height:300px;object-fit:cover;border:4px solid rgba(147,51,234,0.5);box-shadow:0 0 60px rgba(147,51,234,0.5);">
          <?php else: ?>
            <!-- 3D Astronaut fallback -->
            <img src="/portfolio/assets/images/astronaut.png" alt="3D Astronaut" class="astronaut-image mx-auto img-fluid"
                 style="max-width:300px;width:100%;height:auto;object-fit:contain;animation:float 5s ease-in-out infinite;filter:drop-shadow(0 0 40px rgba(147,51,234,0.5));">
          <?php endif; ?>

          <div style="position:absolute;inset:-20px;border:2px solid rgba(147,51,234,0.3);border-radius:50%;animation:orbit-ring 8s linear infinite;"></div>
          <div style="position:absolute;inset:-40px;border:1px dashed rgba(196,181,253,0.15);border-radius:50%;animation:orbit-ring-rev 12s linear infinite;"></div>
        </div>

        <div class="glass-panel mt-4 text-start">
          <h4 style="font-size:0.9rem;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;margin-bottom:1rem;">Coordinates</h4>
          <?php foreach ([
            ['bi-envelope', 'Email', get_setting('owner_email')],
            ['bi-geo-alt', 'Location', get_setting('owner_location')],
            ['bi-telephone', 'Phone', get_setting('owner_phone')],
          ] as [$icon, $label, $value]): ?>
            <?php if ($value): ?>
            <div class="d-flex align-items-center gap-3 mb-2" style="font-size:0.88rem;">
              <i class="bi <?= $icon ?>" style="color:#9333EA;width:20px;"></i>
              <span style="color:rgba(196,181,253,0.6);"><?= $label ?>:</span>
              <span style="color:rgba(255,255,255,0.85);"><?= sanitize_output($value) ?></span>
            </div>
            <?php endif; ?>
          <?php endforeach; ?>

          <div class="d-flex gap-2 mt-4">
            <?php if (get_setting('resume_url')): ?>
            <a href="<?= sanitize_output(get_setting('resume_url')) ?>" target="_blank" class="btn-galaxy btn-primary-galaxy w-100 text-center">
              <i class="bi bi-file-earmark-pdf"></i> Download CV
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Right column: Bio Details -->
      <div class="col-lg-7 reveal-right">
        <h3 class="text-gradient mb-1"><?= sanitize_output(get_setting('owner_name', 'Your Name')) ?></h3>
        <p style="color:var(--purple-light);font-size:1.05rem;margin-bottom:1.5rem;font-family:'Space Grotesk',sans-serif;"><?= sanitize_output(get_setting('owner_title', 'Full Stack Developer')) ?></p>
        
        <div style="color:rgba(255,255,255,0.75);line-height:1.9;font-size:0.98rem;margin-bottom:2rem;">
          <?= nl2br(sanitize_output(get_setting('owner_long_bio', get_setting('owner_bio', '')))) ?>
        </div>

        <!-- Stats Counter -->
        <div class="row g-3">
          <?php foreach ([
            ['bi-code-slash', $projCount, 'Projects Done'],
            ['bi-clock-history', $yearsExp . '+', 'Years Experience'],
            ['bi-people', sanitize_output($clientsCnt), 'Clients Served'],
            ['bi-cpu', $skillsCnt, 'Technologies Mastered'],
          ] as [$icon, $num, $lbl]): ?>
          <div class="col-6 col-sm-3">
            <div class="stat-glass-card text-center p-3">
              <i class="bi <?= $icon ?>" style="font-size:1.5rem;color:#9333EA;margin-bottom:0.5rem;display:inline-block;"></i>
              <div style="font-family:'Space Grotesk',sans-serif;font-size:1.4rem;font-weight:900;color:#fff;"><?= $num ?></div>
              <div style="font-size:0.75rem;color:rgba(196,181,253,0.5);text-transform:uppercase;letter-spacing:1px;margin-top:0.2rem;"><?= $lbl ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
