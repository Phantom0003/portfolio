<?php
/**
 * Shared Layout Footer
 * Galaxy Portfolio CMS
 */
$github    = get_setting('social_github');
$linkedin  = get_setting('social_linkedin');
$twitter   = get_setting('social_twitter');
$instagram = get_setting('social_instagram');
$youtube   = get_setting('social_youtube');
?>

<!-- ── Footer ──────────────────────────────────────────────── -->
<footer class="galaxy-footer" style="position:relative;z-index:10;">
  <div class="container">
    <!-- Decorative orbit -->
    <div style="position:relative;display:inline-block;margin-bottom:1.5rem;">
      <div class="footer-brand"><?= sanitize_output($ownerName ?? 'Portfolio') ?></div>
      <div style="font-size:0.8rem;color:rgba(196,181,253,0.5);letter-spacing:3px;text-transform:uppercase;">
        <?= sanitize_output(get_setting('owner_title', 'Developer')) ?>
      </div>
    </div>

    <!-- Social Links -->
    <div class="mb-3">
      <?php if ($github):    ?><a href="<?= sanitize_output($github) ?>"    class="social-link" target="_blank" title="GitHub"><i class="bi bi-github"></i></a><?php endif; ?>
      <?php if ($linkedin):  ?><a href="<?= sanitize_output($linkedin) ?>"  class="social-link" target="_blank" title="LinkedIn"><i class="bi bi-linkedin"></i></a><?php endif; ?>
      <?php if ($twitter):   ?><a href="<?= sanitize_output($twitter) ?>"   class="social-link" target="_blank" title="Twitter"><i class="bi bi-twitter-x"></i></a><?php endif; ?>
      <?php if ($instagram): ?><a href="<?= sanitize_output($instagram) ?>" class="social-link" target="_blank" title="Instagram"><i class="bi bi-instagram"></i></a><?php endif; ?>
      <?php if ($youtube):   ?><a href="<?= sanitize_output($youtube) ?>"   class="social-link" target="_blank" title="YouTube"><i class="bi bi-youtube"></i></a><?php endif; ?>
      <a href="mailto:<?= sanitize_output(get_setting('owner_email')) ?>" class="social-link" title="Email"><i class="bi bi-envelope-fill"></i></a>
    </div>

    <!-- Nav Links -->
    <div class="d-flex flex-wrap justify-content-center gap-3 mb-3" style="font-size:0.82rem;">
      <a href="/portfolio/" class="text-decoration-none" style="color:rgba(196,181,253,0.5);">Home</a>
      <a href="/portfolio/#about" class="text-decoration-none" style="color:rgba(196,181,253,0.5);">About</a>
      <a href="/portfolio/#projects" class="text-decoration-none" style="color:rgba(196,181,253,0.5);">Projects</a>
      <a href="/portfolio/blog.php" class="text-decoration-none" style="color:rgba(196,181,253,0.5);">Blog</a>
      <a href="/portfolio/#contact" class="text-decoration-none" style="color:rgba(196,181,253,0.5);">Contact</a>
    </div>

    <p style="font-size:0.78rem;color:rgba(196,181,253,0.3);margin:0;">
      &copy; <?= date('Y') ?> <?= sanitize_output($ownerName ?? 'Portfolio') ?>. Crafted with
      <i class="bi bi-heart-fill" style="color:#D946EF;font-size:0.7rem;"></i> from the Galaxy.
    </p>
  </div>
</footer>

<!-- Back to Top -->
<button id="back-top" title="Back to top"><i class="bi bi-arrow-up"></i></button>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Galaxy JS -->
<script src="/portfolio/assets/js/particles.js"></script>
<script src="/portfolio/assets/js/main.js"></script>

<!-- Mobile Nav toggle -->
<script>
(function(){
  const btn = document.getElementById('mobile-menu-btn');
  const nav = document.getElementById('mobile-nav');
  if(btn && nav){
    btn.addEventListener('click', () => {
      nav.style.setProperty('display', nav.style.display === 'block' ? 'none' : 'block', 'important');
    });
  }
})();
</script>
</body>
</html>
