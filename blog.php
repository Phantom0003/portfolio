<?php
/**
 * blog.php — Standalone Blog List Page
 * Galaxy Portfolio
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$currentPage = 'blog';
$pageTitle   = 'Career Blog — ' . get_setting('site_name', 'Galaxy Portfolio');
$pageDesc    = 'Read my latest thoughts, tutorials, and career logs from across the universe.';

require_once __DIR__ . '/includes/header.php';

// Get parameters
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$search = strip_and_trim($_GET['search'] ?? '');
$category = strip_and_trim($_GET['category'] ?? 'all');

// Fetch unique categories for filter sidebar/list
$categories = Database::fetchAll("SELECT DISTINCT category FROM blog_posts WHERE status = 'published' AND category IS NOT NULL AND category != ''");

// Build SQL
$sql = "SELECT * FROM blog_posts WHERE status = 'published'";
$params = [];

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY published_at DESC, id DESC";

// Paginate
$paged = paginate($sql, $params, $page, 6);
$posts = $paged['items'];
?>

<section class="section" style="padding-top:120px;position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Thoughts & Logs</span>
      <h2 class="section-title text-gradient">Star Chronicles</h2>
      <p class="section-subtitle mx-auto">Tutorials, thoughts, and career reflections from the coding vacuum</p>
    </div>

    <!-- Search & Filter Controls -->
    <div class="row g-3 justify-content-center mb-5 reveal">
      <div class="col-md-5">
        <form method="GET" action="/portfolio/blog.php" class="glass-panel p-2 d-flex align-items-center">
          <i class="bi bi-search ms-3 me-2" style="color:rgba(196,181,253,0.5);"></i>
          <input type="text" name="search" class="form-control bg-transparent border-0 text-white me-2" 
                 placeholder="Search posts..." value="<?= sanitize_output($search) ?>" style="box-shadow:none;">
          <?php if ($category !== 'all'): ?>
            <input type="hidden" name="category" value="<?= sanitize_output($category) ?>">
          <?php endif; ?>
          <button type="submit" class="btn-galaxy btn-sm-galaxy btn-primary-galaxy">Search</button>
        </form>
      </div>

      <div class="col-md-3">
        <div class="glass-panel p-2">
          <select class="form-select bg-transparent border-0 text-white" style="box-shadow:none;cursor:pointer;" 
                  onchange="location = this.value;">
            <option value="/portfolio/blog.php?category=all<?= $search ? '&search='.urlencode($search) : '' ?>" style="background:var(--galaxy-deep);color:#fff;">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="/portfolio/blog.php?category=<?= urlencode($cat['category']) ?><?= $search ? '&search='.urlencode($search) : '' ?>" 
                      <?= $category === $cat['category'] ? 'selected' : '' ?> style="background:var(--galaxy-deep);color:#fff;">
                <?= sanitize_output($cat['category']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- Blog Grid -->
    <div class="row g-4 mb-5">
      <?php if ($posts): ?>
        <?php foreach ($posts as $i => $post): ?>
        <div class="col-lg-4 col-md-6 reveal delay-<?= ($i % 3) + 1 ?>">
          <div class="blog-card h-100">
            <?php if ($post['cover_image'] && file_exists(__DIR__ . '/uploads/' . $post['cover_image'])): ?>
              <img src="/portfolio/uploads/<?= sanitize_output($post['cover_image']) ?>" class="blog-cover" alt="<?= sanitize_output($post['title']) ?>">
            <?php else: ?>
              <div class="blog-cover d-flex align-items-center justify-content-center"
                   style="background:linear-gradient(135deg,rgba(107,33,168,0.3),rgba(217,70,239,0.1));">
                <i class="bi bi-newspaper" style="font-size:3rem;color:rgba(196,181,253,0.3);"></i>
              </div>
            <?php endif; ?>

            <div class="blog-body d-flex flex-column">
              <div class="blog-category mb-2"><?= sanitize_output($post['category'] ?? 'General') ?></div>
              <h3 class="blog-title" style="font-size:1.2rem;margin-bottom:0.7rem;"><?= sanitize_output($post['title']) ?></h3>
              
              <?php if ($post['excerpt']): ?>
              <p style="font-size:0.85rem;color:rgba(255,255,255,0.6);margin-bottom:1.5rem;flex-grow:1;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                <?= sanitize_output($post['excerpt']) ?>
              </p>
              <?php endif; ?>

              <div class="blog-meta mt-auto pt-3 d-flex align-items-center justify-content-between border-top border-light-subtle" style="border-top-color:rgba(196,181,253,0.1)!important;">
                <span style="font-size:0.78rem;color:rgba(196,181,253,0.5);"><i class="bi bi-clock"></i> <?= (int)$post['reading_time'] ?> min</span>
                <span style="font-size:0.78rem;color:rgba(196,181,253,0.5);"><i class="bi bi-heart"></i> <?= (int)$post['likes'] ?></span>
                <a href="/portfolio/blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="btn-galaxy btn-sm-galaxy btn-primary-galaxy">Read</a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <div class="glass-panel py-5" style="color:rgba(196,181,253,0.4);">
            <i class="bi bi-journal-x" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
            No chronicles found in this sector.
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
             href="/portfolio/blog.php?category=<?= urlencode($category) ?><?= $search ? '&search='.urlencode($search) : '' ?>&page=<?= $i ?>">
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
