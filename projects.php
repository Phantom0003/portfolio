<?php
/**
 * projects.php — Standalone Projects Grid
 * Galaxy Portfolio
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$currentPage = 'projects';
$pageTitle   = 'My Projects — ' . get_setting('site_name', 'Galaxy Portfolio');
$pageDesc    = 'Explore my portfolio of web applications, AI models, and design work.';

require_once __DIR__ . '/includes/header.php';

// Get current page
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

// Get category filter
$selectedCat = strip_and_trim($_GET['category'] ?? 'all');

// Fetch categories for filter tabs
$categories = Database::fetchAll("SELECT DISTINCT category FROM projects WHERE status != 'archived' AND category IS NOT NULL AND category != ''");

// Build SQL
$sql = "SELECT * FROM projects WHERE status != 'archived'";
$params = [];

if ($selectedCat !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $selectedCat;
}

$sql .= " ORDER BY sort_order, id DESC";

// Paginate
$paged = paginate($sql, $params, $page, 6);
$projects = $paged['items'];
?>

<section class="section" style="padding-top:120px;position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">My Works</span>
      <h2 class="section-title text-gradient">Project Cosmos</h2>
      <p class="section-subtitle mx-auto">Explore the constellations of digital solutions I have built</p>
    </div>

    <!-- Category Filters -->
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-5 reveal">
      <a href="/portfolio/projects.php?category=all" class="btn-galaxy btn-sm-galaxy btn-<?= $selectedCat === 'all' ? 'primary' : 'outline' ?>-galaxy">
        All Constellations
      </a>
      <?php foreach ($categories as $cat): ?>
      <a href="/portfolio/projects.php?category=<?= urlencode($cat['category']) ?>" class="btn-galaxy btn-sm-galaxy btn-<?= $selectedCat === $cat['category'] ? 'primary' : 'outline' ?>-galaxy">
        <?= sanitize_output($cat['category']) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Grid -->
    <div class="row g-4 mb-5" id="projects-grid">
      <?php if ($projects): ?>
        <?php foreach ($projects as $i => $project): ?>
        <div class="col-lg-4 col-md-6 reveal delay-<?= ($i % 3) + 1 ?>" data-category="<?= sanitize_output($project['category']) ?>">
          <div class="project-card h-100">
            <?php if ($project['thumbnail'] && file_exists(__DIR__ . '/uploads/' . $project['thumbnail'])): ?>
              <img src="/portfolio/uploads/<?= sanitize_output($project['thumbnail']) ?>" class="project-thumb" alt="<?= sanitize_output($project['title']) ?>">
            <?php else: ?>
              <div class="project-thumb d-flex align-items-center justify-content-center"
                   style="background:linear-gradient(135deg,rgba(107,33,168,0.3),rgba(79,70,229,0.2));">
                <i class="bi bi-code-slash" style="font-size:3rem;color:rgba(196,181,253,0.3);"></i>
              </div>
            <?php endif; ?>

            <div class="card-body d-flex flex-column">
              <?php if ($project['is_pinned']): ?>
              <span class="galaxy-badge galaxy-badge-pinned mb-2"><i class="bi bi-pin-fill"></i> Featured</span>
              <?php endif; ?>
              <h3 class="project-title"><?= sanitize_output($project['title']) ?></h3>
              <p class="project-desc" style="flex-grow: 1;"><?= sanitize_output($project['description']) ?></p>

              <div class="mb-3"><?= render_tech_tags($project['technologies']) ?></div>

              <div class="d-flex gap-2 align-items-center mt-auto">
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
                  <?php
                  $userId = current_user_id();
                  $isSaved = Database::fetchOne("SELECT id FROM saved_projects WHERE user_id = ? AND project_id = ?", [$userId, $project['id']]);
                  ?>
                  <button class="btn-galaxy btn-sm-galaxy btn-outline-galaxy save-project-btn ms-auto" data-project-id="<?= $project['id'] ?>">
                    <i class="bi bi-bookmark<?= $isSaved ? '-fill' : '' ?>"></i>
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <div class="glass-panel py-5" style="color:rgba(196,181,253,0.4);">
            <i class="bi bi-rocket" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
            No projects found in this sector.
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($paged['total_pages'] > 1): ?>
    <nav class="d-flex justify-content-center mt-4 reveal">
      <ul class="pagination gap-2">
        <?php for ($i = 1; $i <= $paged['total_pages']; $i++): ?>
        <li class="page-item">
          <a class="btn-galaxy btn-sm-galaxy btn-<?= $page === $i ? 'primary' : 'outline' ?>-galaxy" 
             href="/portfolio/projects.php?category=<?= urlencode($selectedCat) ?>&page=<?= $i ?>">
            <?= $i ?>
          </a>
        </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
