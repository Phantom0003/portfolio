<?php
/**
 * admin/settings.php — Site Customization Panel
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

    // Handle avatar upload
    if (!empty($_FILES['owner_avatar']['name'])) {
        $uploader = new FileUploader();
        $up = $uploader->upload('owner_avatar', 'profile', 'image');
        if ($up['success']) {
            update_setting('owner_avatar', $up['path']);
        }
    }

    // Save all text/color settings
    $keys = [
        'site_name','site_tagline','owner_name','owner_title','owner_bio','owner_long_bio',
        'owner_email','owner_phone','owner_location','social_github','social_linkedin',
        'social_twitter','social_instagram','social_youtube','resume_url',
        'theme_primary','theme_secondary','theme_accent','theme_bg_from','theme_bg_to',
        'theme_font','card_blur','card_radius','years_experience','clients_count',
        'hero_cta_text','hero_cta_url',
    ];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            update_setting($key, strip_tags($_POST[$key]));
        }
    }

    // Boolean settings
    foreach (['enable_animations','enable_particles','enable_parallax','maintenance_mode'] as $key) {
        update_setting($key, isset($_POST[$key]) ? '1' : '0');
    }

    $msg = 'success|Settings saved!';
}

[$msgType, $msgText] = $msg ? explode('|', $msg, 2) : [null, null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>Settings — Admin</title>
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
        <div><h1 class="admin-page-title">Settings & Theme</h1><div class="admin-breadcrumb"><i class="bi bi-gear"></i> System &rsaquo; Settings</div></div>
      </div>

      <?php if ($msgText): ?>
      <div class="galaxy-alert galaxy-alert-<?= $msgType ?> mb-3">
        <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'x-circle-fill'?>"></i> <?= sanitize_output($msgText) ?>
      </div>
      <?php endif; ?>

      <!-- Nav Tabs -->
      <ul class="nav mb-4 gap-2" id="settingsTabs">
        <?php foreach (['profile'=>'bi-person Profile','site'=>'bi-globe Site','social'=>'bi-share Social','theme'=>'bi-palette Theme','stats'=>'bi-bar-chart Stats'] as $tab => $lbl): ?>
        <?php [$icon, $text] = explode(' ', $lbl, 2); ?>
        <li class="nav-item">
          <button class="btn-galaxy btn-sm-galaxy btn-<?= $tab==='profile'?'primary':'outline' ?>-galaxy settings-tab-btn" data-tab="tab-<?= $tab ?>">
            <i class="bi <?= $icon ?>"></i> <?= $text ?>
          </button>
        </li>
        <?php endforeach; ?>
      </ul>

      <form method="POST" enctype="multipart/form-data" class="galaxy-form">
        <?= csrf_field() ?>

        <!-- Profile Tab -->
        <div id="tab-profile">
          <div class="row g-4">
            <div class="col-lg-4">
              <div class="glass-panel text-center">
                <div style="position:relative;display:inline-block;margin-bottom:1rem;">
                  <?php $av = get_setting('owner_avatar'); ?>
                  <img src="<?= get_avatar_url($av, get_setting('owner_name','Admin')) ?>" id="avatar-preview" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid rgba(147,51,234,0.5);" alt="">
                </div>
                <div>
                  <label class="form-label">Profile Photo</label>
                  <input type="file" name="owner_avatar" class="galaxy-input form-control" accept="image/*" data-preview="avatar-preview">
                </div>
              </div>
            </div>
            <div class="col-lg-8">
              <div class="admin-form-section">
                <div class="admin-form-section-title"><i class="bi bi-person-badge"></i> Personal Info</div>
                <div class="row g-3">
                  <?php foreach ([
                    ['owner_name','Your Full Name','text'],
                    ['owner_title','Career Title','text'],
                    ['owner_email','Contact Email','email'],
                    ['owner_phone','Phone Number','text'],
                    ['owner_location','Location','text'],
                    ['resume_url','Resume PDF URL','url'],
                  ] as [$key, $label, $type]): ?>
                  <div class="col-md-6">
                    <label class="form-label"><?= $label ?></label>
                    <input type="<?= $type ?>" name="<?= $key ?>" class="galaxy-input form-control" value="<?= sanitize_output(get_setting($key)) ?>">
                  </div>
                  <?php endforeach; ?>
                  <div class="col-12">
                    <label class="form-label">Short Bio</label>
                    <textarea name="owner_bio" rows="3" class="galaxy-input form-control"><?= sanitize_output(get_setting('owner_bio')) ?></textarea>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Full Bio (About page)</label>
                    <textarea name="owner_long_bio" rows="6" class="galaxy-input form-control"><?= sanitize_output(get_setting('owner_long_bio')) ?></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Site Tab -->
        <div id="tab-site" style="display:none;">
          <div class="admin-form-section">
            <div class="admin-form-section-title"><i class="bi bi-globe2"></i> Site Configuration</div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="galaxy-input form-control" value="<?= sanitize_output(get_setting('site_name')) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Site Tagline</label>
                <input type="text" name="site_tagline" class="galaxy-input form-control" value="<?= sanitize_output(get_setting('site_tagline')) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Hero CTA Button Text</label>
                <input type="text" name="hero_cta_text" class="galaxy-input form-control" value="<?= sanitize_output(get_setting('hero_cta_text')) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Hero CTA Button URL</label>
                <input type="text" name="hero_cta_url" class="galaxy-input form-control" value="<?= sanitize_output(get_setting('hero_cta_url')) ?>">
              </div>
              <div class="col-12 d-flex align-items-center gap-3">
                <label class="galaxy-toggle">
                  <input type="checkbox" name="maintenance_mode" <?= get_setting('maintenance_mode')?'checked':'' ?>>
                  <span class="galaxy-toggle-slider"></span>
                </label>
                <span style="color:rgba(196,181,253,0.7);">Maintenance Mode</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Social Tab -->
        <div id="tab-social" style="display:none;">
          <div class="admin-form-section">
            <div class="admin-form-section-title"><i class="bi bi-share"></i> Social Links</div>
            <div class="row g-3">
              <?php foreach (['social_github'=>'bi-github GitHub','social_linkedin'=>'bi-linkedin LinkedIn','social_twitter'=>'bi-twitter-x Twitter/X','social_instagram'=>'bi-instagram Instagram','social_youtube'=>'bi-youtube YouTube'] as $key => $info): ?>
              <?php [$icon, $label] = explode(' ', $info, 2); ?>
              <div class="col-md-6">
                <label class="form-label"><i class="bi <?= $icon ?>"></i> <?= $label ?></label>
                <input type="url" name="<?= $key ?>" class="galaxy-input form-control" placeholder="https://..." value="<?= sanitize_output(get_setting($key)) ?>">
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Theme Tab -->
        <div id="tab-theme" style="display:none;">
          <div class="row g-4">
            <div class="col-lg-8">
              <div class="admin-form-section">
                <div class="admin-form-section-title"><i class="bi bi-palette"></i> Color Palette</div>
                <div class="row g-3">
                  <?php foreach ([
                    ['theme_primary','Primary Color','--purple-bright'],
                    ['theme_secondary','Secondary Color','--indigo-glow'],
                    ['theme_accent','Accent/Neon Color','--neon-purple'],
                    ['theme_bg_from','Background Start','--galaxy-deep'],
                    ['theme_bg_to','Background End','--galaxy-mid'],
                  ] as [$key, $label, $var]): ?>
                  <div class="col-md-4">
                    <label class="form-label"><?= $label ?></label>
                    <div class="d-flex gap-2 align-items-center">
                      <input type="color" name="<?= $key ?>" data-var="<?= $var ?>" class="form-control form-control-color" value="<?= sanitize_output(get_setting($key)) ?>" style="width:48px;height:38px;border-radius:8px;cursor:pointer;border:none;background:none;padding:0;">
                      <input type="text" style="flex:1;" class="galaxy-input form-control" value="<?= sanitize_output(get_setting($key)) ?>" readonly>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="admin-form-section mt-3">
                <div class="admin-form-section-title"><i class="bi bi-type"></i> Typography & Style</div>
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Glass Blur (px)</label>
                    <input type="number" name="card_blur" class="galaxy-input form-control" value="<?= sanitize_output(get_setting('card_blur','20')) ?>" min="0" max="40">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Card Radius (px)</label>
                    <input type="number" name="card_radius" class="galaxy-input form-control" value="<?= sanitize_output(get_setting('card_radius','20')) ?>" min="0" max="40">
                  </div>
                </div>
              </div>

              <div class="admin-form-section mt-3">
                <div class="admin-form-section-title"><i class="bi bi-stars"></i> Animations</div>
                <div class="d-flex flex-wrap gap-4">
                  <?php foreach (['enable_animations'=>'Animations','enable_particles'=>'Star Particles','enable_parallax'=>'Parallax'] as $k => $l): ?>
                  <div class="d-flex align-items-center gap-2">
                    <label class="galaxy-toggle">
                      <input type="checkbox" name="<?= $k ?>" <?= get_setting($k,'1')?'checked':'' ?>>
                      <span class="galaxy-toggle-slider"></span>
                    </label>
                    <span style="color:rgba(196,181,253,0.7);font-size:0.88rem;"><?= $l ?></span>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <!-- Live Preview -->
            <div class="col-lg-4">
              <div class="glass-panel h-100">
                <h4 style="font-size:0.85rem;color:#C4B5FD;text-transform:uppercase;letter-spacing:2px;margin-bottom:1rem;">Live Preview</h4>
                <div id="theme-preview" style="border-radius:16px;padding:1.5rem;border:1px solid rgba(196,181,253,0.2);background:rgba(147,51,234,0.1);">
                  <div style="font-family:'Orbitron',monospace;font-size:1.2rem;font-weight:900;background:linear-gradient(135deg,var(--lavender),var(--neon-purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:0.5rem;">Your Name</div>
                  <div style="font-size:0.8rem;color:rgba(196,181,253,0.6);margin-bottom:1rem;">Full Stack Developer</div>
                  <div class="btn-galaxy btn-sm-galaxy btn-primary-galaxy d-inline-flex">Explore</div>
                </div>
                <div class="glass-card mt-3 p-3" style="font-size:0.8rem;color:rgba(196,181,253,0.6);">
                  <i class="bi bi-info-circle" style="color:#9333EA;"></i> Color changes preview live
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Tab -->
        <div id="tab-stats" style="display:none;">
          <div class="admin-form-section">
            <div class="admin-form-section-title"><i class="bi bi-bar-chart"></i> Homepage Stats</div>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Years of Experience</label>
                <input type="text" name="years_experience" class="galaxy-input form-control" value="<?= sanitize_output(get_setting('years_experience','5')) ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Happy Clients (text)</label>
                <input type="text" name="clients_count" class="galaxy-input form-control" value="<?= sanitize_output(get_setting('clients_count','20+')) ?>">
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn-galaxy btn-lg-galaxy btn-primary-galaxy">
            <i class="bi bi-save"></i> Save All Settings
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/admin.js"></script>
<script>
// Tab switching
document.querySelectorAll('.settings-tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display='none');
    document.querySelectorAll('.settings-tab-btn').forEach(b => b.classList.replace('btn-primary-galaxy','btn-outline-galaxy'));
    document.getElementById(this.dataset.tab).style.display = '';
    this.classList.replace('btn-outline-galaxy','btn-primary-galaxy');
  });
});
// Sync color pickers with text inputs
document.querySelectorAll('input[type="color"]').forEach(picker => {
  picker.addEventListener('input', function() {
    const sibling = this.nextElementSibling;
    if(sibling) sibling.value = this.value;
  });
});
</script>
</body>
</html>
