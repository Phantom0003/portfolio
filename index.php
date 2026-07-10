<?php
/**
 * index.php — Galaxy Portfolio Homepage
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$currentPage = 'home';
$pageTitle   = get_setting('site_name', 'Galaxy Portfolio') . ' — ' . get_setting('owner_title', 'Full Stack Developer');
$pageDesc    = get_setting('owner_bio', '');

// Fetch data
$pinnedProjects = Database::fetchAll("SELECT * FROM projects WHERE is_pinned = 1 AND status = 'active' ORDER BY sort_order LIMIT 6");
$featuredSkills = Database::fetchAll("SELECT * FROM skills WHERE is_featured = 1 ORDER BY sort_order LIMIT 12");
$experiences    = Database::fetchAll("SELECT * FROM experience ORDER BY sort_order DESC LIMIT 6");
$latestPosts    = Database::fetchAll("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 3");
$achievements   = Database::fetchAll("SELECT * FROM achievements WHERE is_featured = 1 ORDER BY sort_order LIMIT 4");

// Stats
$yearsExp   = get_setting('years_experience', '5');
$clientsCnt = get_setting('clients_count', '20+');
$projCount  = Database::fetchOne("SELECT COUNT(*) as c FROM projects WHERE status != 'archived'")['c'];
$skillsCnt  = Database::fetchOne("SELECT COUNT(*) as c FROM skills")['c'];

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── HERO ──────────────────────────────────────────────────── -->
<section class="hero-section" id="hero">
  <!-- Floating space elements -->
  <div class="orbit-system d-none d-xl-block">
    <div class="orbit-ring orbit-ring-1"><div class="orbit-planet orbit-planet-1"></div></div>
    <div class="orbit-ring orbit-ring-2"><div class="orbit-planet orbit-planet-2"></div></div>
    <div class="orbit-ring orbit-ring-3"><div class="orbit-planet orbit-planet-3"></div></div>
  </div>

  <!-- Planets -->
  <div class="planet-decor planet-1"></div>
  <div class="planet-decor planet-2"></div>
  <div class="planet-decor planet-3"></div>

  <!-- Geo shapes -->
  <div class="geo-shape geo-triangle geo-1"></div>
  <div class="geo-shape geo-diamond geo-2"></div>
  <div class="geo-shape geo-circle geo-3"></div>
  <div class="geo-shape geo-triangle geo-4" style="transform:rotate(180deg);"></div>
  <div class="geo-shape geo-diamond geo-5"></div>

  <div class="container" style="position:relative;z-index:10;">
    <div class="row align-items-center gy-5">
      <!-- Left: Content -->
      <div class="col-lg-6">
        <div class="reveal">
          <span class="section-label">Welcome to my universe</span>
          <h1 class="hero-title">
            <span class="text-gradient"><?= sanitize_output(get_setting('owner_name', 'Your Name')) ?></span>
          </h1>
          <div class="hero-subtitle">
            <span data-typing='["<?= get_setting('owner_title','Full Stack Developer') ?>","Problem Solver","Digital Creator","Code Architect"]' data-speed="80" class="typing-text"></span>
          </div>
          <p class="hero-desc"><?= sanitize_output(get_setting('owner_bio', '')) ?></p>

          <!-- Stats row -->
          <div class="d-flex gap-4 mb-4 flex-wrap">
            <div class="stat-item reveal delay-1">
              <div class="stat-number" data-count="<?= $yearsExp ?>" data-suffix="+"><?= $yearsExp ?>+</div>
              <div class="stat-label">Years Exp.</div>
            </div>
            <div class="stat-item reveal delay-2">
              <div class="stat-number" data-count="<?= $projCount ?>"><?= $projCount ?></div>
              <div class="stat-label">Projects</div>
            </div>
            <div class="stat-item reveal delay-3">
              <div class="stat-number"><?= sanitize_output($clientsCnt) ?></div>
              <div class="stat-label">Happy Clients</div>
            </div>
            <div class="stat-item reveal delay-4">
              <div class="stat-number" data-count="<?= $skillsCnt ?>"><?= $skillsCnt ?></div>
              <div class="stat-label">Skills</div>
            </div>
          </div>

          <!-- CTA Buttons -->
          <div class="hero-cta-group reveal delay-5">
            <a href="<?= sanitize_output(get_setting('hero_cta_url','#projects')) ?>" class="btn-galaxy btn-lg-galaxy btn-primary-galaxy">
              <i class="bi bi-rocket-takeoff-fill"></i> <?= sanitize_output(get_setting('hero_cta_text','Explore My Universe')) ?>
            </a>
            <?php if (get_setting('resume_url')): ?>
            <a href="<?= sanitize_output(get_setting('resume_url')) ?>" target="_blank" class="btn-galaxy btn-lg-galaxy btn-outline-galaxy">
              <i class="bi bi-file-earmark-pdf"></i> Download CV
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Right: Astronaut Visual -->
      <div class="col-lg-6 text-center reveal-right">
        <div class="astronaut-wrapper d-inline-block position-relative">
          <?php
          $avatar = get_setting('owner_avatar');
          if ($avatar && file_exists(__DIR__ . '/uploads/' . $avatar)):
          ?>
            <img src="/portfolio/uploads/<?= sanitize_output($avatar) ?>" alt="Profile" class="rounded-circle"
                 style="width:300px;height:300px;object-fit:cover;border:4px solid rgba(147,51,234,0.5);box-shadow:0 0 60px rgba(147,51,234,0.5),0 0 120px rgba(107,33,168,0.2);">
          <?php else: ?>
            <!-- 3D Astronaut fallback -->
            <img src="/portfolio/assets/images/astronaut.png" alt="3D Astronaut" class="astronaut-image mx-auto img-fluid"
                 style="max-width:320px;width:100%;height:auto;object-fit:contain;animation:float 6s ease-in-out infinite;filter:drop-shadow(0 0 40px rgba(147,51,234,0.5));">
          <?php endif; ?>

          <!-- Glowing ring around avatar -->
          <div style="position:absolute;inset:-20px;border:2px solid rgba(147,51,234,0.3);border-radius:50%;animation:orbit-ring 8s linear infinite;"></div>
          <div style="position:absolute;inset:-40px;border:1px dashed rgba(196,181,253,0.15);border-radius:50%;animation:orbit-ring-rev 12s linear infinite;"></div>

          <!-- Floating badges -->
          <div class="glass-card" style="position:absolute;top:-10px;right:-20px;padding:0.6rem 1rem;font-size:0.78rem;font-family:var(--font-primary);color:var(--lavender);white-space:nowrap;animation:float 4s ease-in-out infinite;">
            <i class="bi bi-lightning-fill" style="color:#D946EF;"></i> Open to Work
          </div>
          <div class="glass-card" style="position:absolute;bottom:10px;left:-30px;padding:0.6rem 1rem;font-size:0.78rem;font-family:var(--font-primary);color:var(--lavender);white-space:nowrap;animation:float 5s ease-in-out infinite 1s;">
            <i class="bi bi-stars" style="color:#9333EA;"></i> Full Stack Dev
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll indicator -->
    <div class="text-center mt-5" style="animation:float-slow 3s ease-in-out infinite;">
      <a href="#about" style="color:rgba(196,181,253,0.4);font-size:0.8rem;letter-spacing:3px;text-transform:uppercase;text-decoration:none;">
        <div>Scroll Down</div>
        <i class="bi bi-chevron-double-down" style="font-size:1.2rem;"></i>
      </a>
    </div>
  </div>
</section>

<!-- ── ABOUT ─────────────────────────────────────────────────── -->
<section class="section" id="about" style="position:relative;z-index:10;">
  <div class="container">
    <div class="row align-items-center gy-5">
      <div class="col-lg-5 reveal-left">
        <div class="glass-panel h-100">
          <!-- About visual: mini orbit -->
          <div class="text-center mb-4">
            <div style="position:relative;display:inline-block;">
              <div style="width:140px;height:140px;border-radius:50%;background:linear-gradient(135deg,rgba(147,51,234,0.3),rgba(79,70,229,0.2));display:flex;align-items:center;justify-content:center;border:2px solid rgba(147,51,234,0.3);margin:0 auto;">
                <i class="bi bi-person-bounding-box" style="font-size:3.5rem;color:#C4B5FD;"></i>
              </div>
              <div style="position:absolute;inset:-15px;border:1px dashed rgba(196,181,253,0.2);border-radius:50%;animation:orbit-ring 10s linear infinite;"></div>
            </div>
          </div>
          <div class="text-center">
            <h3 class="text-gradient mb-1"><?= sanitize_output(get_setting('owner_name','Your Name')) ?></h3>
            <p style="color:var(--purple-light);font-size:0.9rem;margin-bottom:1.5rem;"><?= sanitize_output(get_setting('owner_title','Full Stack Developer')) ?></p>
            <p style="color:rgba(255,255,255,0.6);font-size:0.88rem;line-height:1.8;"><?= nl2br(sanitize_output(get_setting('owner_bio',''))) ?></p>

            <!-- Contact info -->
            <div class="mt-4 text-start">
              <?php foreach ([
                ['bi-envelope','Email',get_setting('owner_email')],
                ['bi-geo-alt','Location',get_setting('owner_location')],
              ] as [$icon, $label, $value]): ?>
              <?php if($value): ?>
              <div class="d-flex align-items-center gap-2 mb-2" style="font-size:0.85rem;">
                <i class="bi <?= $icon ?>" style="color:#9333EA;width:20px;"></i>
                <span style="color:rgba(196,181,253,0.6);"><?= $label ?>:</span>
                <span style="color:rgba(255,255,255,0.8);"><?= sanitize_output($value) ?></span>
              </div>
              <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7 reveal-right">
        <span class="section-label">About Me</span>
        <h2 class="section-title text-gradient mb-3">My Digital Journey</h2>
        <div style="color:rgba(255,255,255,0.7);line-height:1.9;font-size:0.95rem;">
          <?= nl2br(sanitize_output(get_setting('owner_long_bio', get_setting('owner_bio','')))) ?>
        </div>

        <!-- Achievement quick stats -->
        <div class="row g-3 mt-4">
          <?php foreach ([
            ['bi-code-slash','<?= $projCount ?> Projects','Completed'],
            ['bi-award','Certificates','Earned'],
            ['bi-people','Clients','Satisfied'],
          ] as [$icon, $val, $sub]): ?>
          <div class="col-4">
            <div class="stat-glass-card">
              <i class="bi <?= $icon ?>" style="font-size:1.5rem;color:#9333EA;display:block;margin-bottom:0.5rem;"></i>
              <div style="font-family:var(--font-display);font-size:1.3rem;color:#fff;font-weight:900;"><?= $val ?></div>
              <div style="font-size:0.72rem;color:rgba(196,181,253,0.5);text-transform:uppercase;letter-spacing:1px;"><?= $sub ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── SKILLS ─────────────────────────────────────────────────── -->
<section class="section" id="skills" style="position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">What I Know</span>
      <h2 class="section-title text-gradient">My Skill Galaxy</h2>
      <p class="section-subtitle mx-auto">Technologies and tools I use to craft digital experiences</p>
    </div>

    <?php
    $skillsByCategory = [];
    foreach ($featuredSkills as $skill) {
      $skillsByCategory[$skill['category']][] = $skill;
    }
    $catLabels = ['frontend'=>'Frontend','backend'=>'Backend','database'=>'Database','tools'=>'Tools','languages'=>'Languages','devops'=>'DevOps','design'=>'Design','other'=>'Other'];
    ?>

    <div class="row g-4">
      <?php foreach ($skillsByCategory as $cat => $catSkills): ?>
      <div class="col-lg-6 reveal delay-<?= (array_search($cat, array_keys($skillsByCategory)) % 4) + 1 ?>">
        <div class="glass-card p-4 h-100">
          <h4 style="font-size:0.85rem;color:var(--purple-light);text-transform:uppercase;letter-spacing:3px;margin-bottom:1.5rem;font-family:var(--font-display);">
            <?= $catLabels[$cat] ?? ucfirst($cat) ?>
          </h4>
          <?php foreach ($catSkills as $skill): ?>
          <div class="skill-item">
            <div class="skill-header">
              <div class="skill-name">
                <i class="bi <?= sanitize_output($skill['icon'] ?? 'bi-circle') ?>" style="color:<?= sanitize_output($skill['color'] ?? '#9333EA') ?>;margin-right:6px;"></i>
                <?= sanitize_output($skill['name']) ?>
              </div>
              <div class="skill-percent"><?= (int)$skill['level'] ?>%</div>
            </div>
            <div class="skill-bar-track">
              <div class="skill-bar-fill" data-level="<?= (int)$skill['level'] ?>" style="background:linear-gradient(90deg,<?= sanitize_output($skill['color']??'#9333EA') ?>,#D946EF);"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4 reveal">
      <a href="/portfolio/skills.php" class="btn-galaxy btn-outline-galaxy">View All Skills <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</section>

<!-- ── PROJECTS ───────────────────────────────────────────────── -->
<section class="section" id="projects" style="position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">What I've Built</span>
      <h2 class="section-title text-gradient">Featured Projects</h2>
      <p class="section-subtitle mx-auto">A collection of my finest work from across the universe</p>
    </div>

    <div class="row g-4">
      <?php foreach ($pinnedProjects as $i => $project):
        $techs = json_decode_safe($project['technologies']);
      ?>
      <div class="col-lg-4 col-md-6 reveal delay-<?= ($i % 3) + 1 ?>">
        <div class="project-card h-100">
          <?php if ($project['thumbnail'] && file_exists(__DIR__.'/uploads/'.$project['thumbnail'])): ?>
            <img src="/portfolio/uploads/<?= sanitize_output($project['thumbnail']) ?>" class="project-thumb" alt="<?= sanitize_output($project['title']) ?>">
          <?php else: ?>
            <!-- Placeholder gradient thumbnail -->
            <div class="project-thumb d-flex align-items-center justify-content-center"
                 style="background:linear-gradient(135deg,rgba(107,33,168,0.3),rgba(79,70,229,0.2));">
              <i class="bi bi-code-slash" style="font-size:3rem;color:rgba(196,181,253,0.3);"></i>
            </div>
          <?php endif; ?>

          <div class="card-body">
            <?php if ($project['is_pinned']): ?>
            <span class="galaxy-badge galaxy-badge-pinned mb-2"><i class="bi bi-pin-fill"></i> Featured</span>
            <?php endif; ?>
            <h3 class="project-title"><?= sanitize_output($project['title']) ?></h3>
            <p class="project-desc"><?= sanitize_output($project['description']) ?></p>

            <div class="mb-3"><?= render_tech_tags($project['technologies']) ?></div>

            <div class="d-flex gap-2 flex-wrap">
              <?php if ($project['demo_url'] && $project['demo_url'] !== '#'): ?>
              <a href="<?= sanitize_output($project['demo_url']) ?>" target="_blank" class="btn-galaxy btn-sm-galaxy btn-primary-galaxy">
                <i class="bi bi-box-arrow-up-right"></i> Demo
              </a>
              <?php endif; ?>
              <?php if ($project['github_url']): ?>
              <a href="<?= sanitize_output($project['github_url']) ?>" target="_blank" class="btn-galaxy btn-sm-galaxy btn-outline-galaxy">
                <i class="bi bi-github"></i> Code
              </a>
              <?php endif; ?>
              <?php if (is_visitor()): ?>
              <button class="btn-galaxy btn-sm-galaxy btn-outline-galaxy save-project-btn ms-auto" data-project-id="<?= $project['id'] ?>">
                <i class="bi bi-bookmark"></i>
              </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-5 reveal">
      <a href="/portfolio/projects.php" class="btn-galaxy btn-lg-galaxy btn-outline-galaxy">
        <i class="bi bi-grid-3x3-gap"></i> All Projects
      </a>
    </div>
  </div>
</section>

<!-- ── EXPERIENCE ─────────────────────────────────────────────── -->
<section class="section" id="experience" style="position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">My Journey</span>
      <h2 class="section-title text-gradient">Experience Timeline</h2>
      <p class="section-subtitle mx-auto">My path through the digital cosmos</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="timeline reveal">
          <?php foreach ($experiences as $exp): ?>
          <div class="timeline-item">
            <div class="timeline-dot" style="background:<?= sanitize_output($exp['color'] ?? '#9333EA') ?>;"></div>
            <div class="timeline-content">
              <div class="timeline-date">
                <?= format_date($exp['start_date'], 'M Y') ?> &mdash;
                <?= $exp['is_current'] ? 'Present' : format_date($exp['end_date'], 'M Y') ?>
              </div>
              <h4 class="timeline-title"><?= sanitize_output($exp['title']) ?></h4>
              <div class="timeline-org">
                <i class="bi <?= sanitize_output($exp['icon'] ?? 'bi-briefcase') ?>" style="color:<?= sanitize_output($exp['color']??'#9333EA') ?>;"></i>
                <?= sanitize_output($exp['organization']) ?>
                <?php if ($exp['location']): ?>
                  <span style="color:rgba(255,255,255,0.3);margin-left:0.5rem;"><i class="bi bi-geo-alt"></i> <?= sanitize_output($exp['location']) ?></span>
                <?php endif; ?>
              </div>
              <?php if ($exp['description']): ?>
              <p style="font-size:0.88rem;color:rgba(255,255,255,0.6);margin:0;"><?= sanitize_output($exp['description']) ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="text-center mt-4 reveal">
          <a href="/portfolio/experience.php" class="btn-galaxy btn-outline-galaxy">Full Timeline <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── ACHIEVEMENTS ───────────────────────────────────────────── -->
<?php if ($achievements): ?>
<section class="section" id="achievements" style="position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Recognition</span>
      <h2 class="section-title text-gradient">Achievements & Certs</h2>
    </div>
    <div class="row g-4">
      <?php foreach ($achievements as $i => $ach): ?>
      <div class="col-lg-3 col-md-6 reveal delay-<?= ($i%4)+1 ?>">
        <div class="achievement-card text-center">
          <div style="width:60px;height:60px;border-radius:16px;background:linear-gradient(135deg,rgba(147,51,234,0.3),rgba(79,70,229,0.2));display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
            <i class="bi bi-award-fill" style="font-size:1.5rem;color:#C4B5FD;"></i>
          </div>
          <h5 style="font-size:0.95rem;color:#fff;font-weight:700;margin-bottom:0.3rem;"><?= sanitize_output($ach['title']) ?></h5>
          <?php if ($ach['issuer']): ?>
          <p style="font-size:0.8rem;color:var(--purple-light);margin-bottom:0.5rem;"><?= sanitize_output($ach['issuer']) ?></p>
          <?php endif; ?>
          <?php if ($ach['date_awarded']): ?>
          <span style="font-size:0.72rem;color:rgba(196,181,253,0.5);"><?= format_date($ach['date_awarded'], 'M Y') ?></span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4 reveal">
      <a href="/portfolio/achievements.php" class="btn-galaxy btn-outline-galaxy">All Achievements <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── BLOG ───────────────────────────────────────────────────── -->
<?php if ($latestPosts): ?>
<section class="section" id="blog" style="position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Thoughts & Updates</span>
      <h2 class="section-title text-gradient">From the Blog</h2>
    </div>
    <div class="row g-4">
      <?php foreach ($latestPosts as $i => $post): ?>
      <div class="col-lg-4 col-md-6 reveal delay-<?= $i+1 ?>">
        <div class="blog-card h-100">
          <?php if ($post['cover_image'] && file_exists(__DIR__.'/uploads/'.$post['cover_image'])): ?>
          <img src="/portfolio/uploads/<?= sanitize_output($post['cover_image']) ?>" class="blog-cover" alt="">
          <?php else: ?>
          <div class="blog-cover d-flex align-items-center justify-content-center"
               style="background:linear-gradient(135deg,rgba(107,33,168,0.3),rgba(217,70,239,0.1));">
            <i class="bi bi-newspaper" style="font-size:2.5rem;color:rgba(196,181,253,0.3);"></i>
          </div>
          <?php endif; ?>
          <div class="blog-body">
            <div class="blog-category"><?= sanitize_output($post['category'] ?? 'General') ?></div>
            <h3 class="blog-title"><?= sanitize_output($post['title']) ?></h3>
            <?php if ($post['excerpt']): ?>
            <p style="font-size:0.84rem;color:rgba(255,255,255,0.55);margin-bottom:1rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= sanitize_output($post['excerpt']) ?></p>
            <?php endif; ?>
            <div class="blog-meta">
              <span><i class="bi bi-clock"></i> <?= (int)$post['reading_time'] ?> min</span>
              <span><i class="bi bi-heart"></i> <?= (int)$post['likes'] ?></span>
              <a href="/portfolio/blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="btn-galaxy btn-sm-galaxy btn-primary-galaxy ms-auto">Read</a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4 reveal">
      <a href="/portfolio/blog.php" class="btn-galaxy btn-lg-galaxy btn-outline-galaxy">All Posts <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── CONTACT ────────────────────────────────────────────────── -->
<section class="section" id="contact" style="position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Get in Touch</span>
      <h2 class="section-title text-gradient">Send a Signal</h2>
      <p class="section-subtitle mx-auto">Have a project in mind or just want to connect? Reach across the galaxy.</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-7 reveal">
        <div class="glass-panel">
          <?php
          // Handle contact form
          $contactSuccess = false;
          if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
            if (!verify_csrf($_POST['csrf_token'] ?? '')) {
              $contactError = 'Security token error. Please try again.';
            } else {
              $name    = strip_and_trim($_POST['name'] ?? '');
              $email   = strip_and_trim($_POST['email'] ?? '');
              $subject = strip_and_trim($_POST['subject'] ?? '');
              $msg     = strip_and_trim($_POST['message'] ?? '');

              if (!$name || !$email || !$msg) {
                $contactError = 'Please fill in all required fields.';
              } elseif (!is_valid_email($email)) {
                $contactError = 'Please enter a valid email address.';
              } else {
                Database::insert('messages', [
                  'user_id'    => is_visitor() ? current_user_id() : null,
                  'name'       => $name,
                  'email'      => $email,
                  'subject'    => $subject,
                  'message'    => $msg,
                  'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                ]);
                create_notification('admin', 1, 'new_message', '💬 New Contact Message',
                  "From: $name — $subject", '/portfolio/admin/messages.php');
                $contactSuccess = true;
              }
            }
          }
          ?>

          <?php if ($contactSuccess): ?>
          <div class="galaxy-alert galaxy-alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>Message sent across the galaxy! I'll reply soon.</span>
          </div>
          <?php endif; ?>
          <?php if (isset($contactError)): ?>
          <div class="galaxy-alert galaxy-alert-error">
            <i class="bi bi-x-circle-fill"></i>
            <span><?= sanitize_output($contactError) ?></span>
          </div>
          <?php endif; ?>

          <form method="POST" class="galaxy-form">
            <?= csrf_field() ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Name <span style="color:#D946EF;">*</span></label>
                <input type="text" name="name" class="galaxy-input form-control" placeholder="Your Name" required value="<?= sanitize_output($_POST['name'] ?? (current_user()['name'] ?? '')) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Email <span style="color:#D946EF;">*</span></label>
                <input type="email" name="email" class="galaxy-input form-control" placeholder="your@email.com" required value="<?= sanitize_output($_POST['email'] ?? (current_user()['email'] ?? '')) ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="galaxy-input form-control" placeholder="What's it about?">
              </div>
              <div class="col-12">
                <label class="form-label">Message <span style="color:#D946EF;">*</span></label>
                <textarea name="message" rows="5" class="galaxy-input form-control" placeholder="Tell me about your project or just say hello..." required></textarea>
              </div>
              <div class="col-12">
                <button type="submit" name="send_message" class="btn-galaxy btn-primary-galaxy w-100">
                  <i class="bi bi-send-fill"></i> Send Message
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
