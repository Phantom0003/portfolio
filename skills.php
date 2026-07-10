<?php
/**
 * skills.php — Standalone Skills Page
 * Galaxy Portfolio
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$currentPage = 'skills';
$pageTitle   = 'My Skills — ' . get_setting('site_name', 'Galaxy Portfolio');
$pageDesc    = 'Explore my technology stack, programming languages, libraries, and design tools.';

require_once __DIR__ . '/includes/header.php';

// Fetch all skills
$allSkills = Database::fetchAll("SELECT * FROM skills ORDER BY category, sort_order");
$skillsByCategory = [];
foreach ($allSkills as $skill) {
    $skillsByCategory[$skill['category']][] = $skill;
}
$catLabels = [
    'frontend'  => 'Frontend Development',
    'backend'   => 'Backend Development',
    'database'  => 'Database & Storage',
    'tools'     => 'Tools & Version Control',
    'languages' => 'Languages',
    'devops'    => 'DevOps & Cloud',
    'design'    => 'UI/UX & Design',
    'other'     => 'Other Skills'
];
?>

<section class="section" style="padding-top:120px;position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Technology Stack</span>
      <h2 class="section-title text-gradient">Skill Galaxy</h2>
      <p class="section-subtitle mx-auto">Technologies, frameworks, and tools I use to build digital universes</p>
    </div>

    <!-- Search / Filter bar -->
    <div class="row justify-content-center mb-5 reveal">
      <div class="col-md-6">
        <div class="glass-panel p-2 d-flex align-items-center">
          <i class="bi bi-search ms-3 me-2" style="color:rgba(196,181,253,0.5);"></i>
          <input type="text" id="skill-search" class="form-control bg-transparent border-0 text-white" placeholder="Search skills (e.g. PHP, JavaScript, Docker)..." style="box-shadow:none;">
        </div>
      </div>
    </div>

    <div class="row g-4" id="skills-container">
      <?php if ($skillsByCategory): ?>
        <?php foreach ($skillsByCategory as $cat => $catSkills): ?>
        <div class="col-lg-6 skill-category-group" data-category="<?= sanitize_output($cat) ?>">
          <div class="glass-card p-4 h-100">
            <h4 class="category-header mb-4" style="font-size:0.95rem;color:var(--purple-light);text-transform:uppercase;letter-spacing:3px;font-family:'Space Grotesk',sans-serif;border-bottom:1px solid rgba(196,181,253,0.1);padding-bottom:0.8rem;">
              <?= $catLabels[$cat] ?? ucfirst($cat) ?>
            </h4>
            
            <div class="skills-list">
              <?php foreach ($catSkills as $skill): ?>
              <div class="skill-item mb-3" data-name="<?= strtolower(sanitize_output($skill['name'])) ?>">
                <div class="skill-header d-flex justify-content-between mb-1" style="font-size:0.9rem;">
                  <div class="skill-name" style="color:#fff;font-weight:500;">
                    <i class="bi <?= sanitize_output($skill['icon'] ?? 'bi-circle') ?>" style="color:<?= sanitize_output($skill['color'] ?? '#9333EA') ?>;margin-right:6px;"></i>
                    <?= sanitize_output($skill['name']) ?>
                  </div>
                  <div class="skill-percent" style="color:rgba(196,181,253,0.7);"><?= (int)$skill['level'] ?>%</div>
                </div>
                <div class="skill-bar-track" style="height:6px;background:rgba(255,255,255,0.06);border-radius:3px;overflow:hidden;">
                  <div class="skill-bar-fill" data-level="<?= (int)$skill['level'] ?>" style="height:100%;width:<?= (int)$skill['level'] ?>%;background:linear-gradient(90deg,<?= sanitize_output($skill['color'] ?? '#9333EA') ?>,#D946EF);transition:width 1s ease-in-out;border-radius:3px;"></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <div class="glass-panel py-5" style="color:rgba(196,181,253,0.4);">
            <i class="bi bi-cpu" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
            No skills seeded in the database.
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('skill-search');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const q = this.value.toLowerCase().trim();
      document.querySelectorAll('.skill-category-group').forEach(group => {
        let matchCount = 0;
        group.querySelectorAll('.skill-item').forEach(item => {
          const name = item.dataset.name;
          if (name.includes(q)) {
            item.style.display = '';
            matchCount++;
          } else {
            item.style.display = 'none';
          }
        });
        if (matchCount > 0) {
          group.style.display = '';
        } else {
          group.style.display = 'none';
        }
      });
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
