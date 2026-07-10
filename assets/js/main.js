/**
 * main.js — Core Interactions
 * Galaxy Portfolio CMS
 */

document.addEventListener('DOMContentLoaded', () => {

  // ── Page Loader ──────────────────────────────────────────────
  const loader = document.getElementById('galaxy-loader');
  if (loader) {
    window.addEventListener('load', () => {
      setTimeout(() => loader.classList.add('hide'), 600);
    });
    setTimeout(() => loader.classList.add('hide'), 3000);
  }

  // ── Progress Bar ─────────────────────────────────────────────
  const progress = document.getElementById('page-progress');
  if (progress) {
    window.addEventListener('scroll', () => {
      const scrollTop = window.scrollY;
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
      progress.style.width = pct + '%';
    });
  }

  // ── Sticky Nav ───────────────────────────────────────────────
  const nav = document.querySelector('.galaxy-nav');
  if (nav) {
    const updateNav = () => nav.classList.toggle('scrolled', window.scrollY > 50);
    window.addEventListener('scroll', updateNav, { passive: true });
    updateNav();
  }

  // ── Active Nav Link ──────────────────────────────────────────
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link[href*="#"]');

  const activateLink = () => {
    let current = '';
    sections.forEach(sec => {
      if (window.scrollY >= sec.offsetTop - 120) current = sec.id;
    });
    navLinks.forEach(link => {
      link.classList.toggle('active', link.getAttribute('href').includes(current));
    });
  };
  window.addEventListener('scroll', activateLink, { passive: true });

  // ── Back to Top ──────────────────────────────────────────────
  const backTop = document.getElementById('back-top');
  if (backTop) {
    window.addEventListener('scroll', () => {
      backTop.classList.toggle('show', window.scrollY > 400);
    }, { passive: true });
    backTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // ── Scroll Reveal ────────────────────────────────────────────
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('revealed');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });

  document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale')
    .forEach(el => revealObserver.observe(el));

  // ── Skill Bar Animation ──────────────────────────────────────
  const skillObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const bar = entry.target.querySelector('.skill-bar-fill');
        if (bar) {
          const level = bar.dataset.level || '80';
          setTimeout(() => bar.style.width = level + '%', 200);
        }
        skillObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  document.querySelectorAll('.skill-item').forEach(item => skillObserver.observe(item));

  // ── Counter Animation ────────────────────────────────────────
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        counterObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

  function animateCounter(el) {
    const target = parseFloat(el.dataset.count);
    const suffix = el.dataset.suffix || '';
    const duration = 2000;
    const start = performance.now();
    const isFloat = String(target).includes('.');

    const update = (now) => {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const value = target * eased;
      el.textContent = (isFloat ? value.toFixed(1) : Math.floor(value)) + suffix;
      if (progress < 1) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
  }

  // ── Typing Effect ────────────────────────────────────────────
  const typingEls = document.querySelectorAll('[data-typing]');
  typingEls.forEach(el => {
    const words = JSON.parse(el.dataset.typing || '[]');
    if (!words.length) return;
    let wordIndex = 0, charIndex = 0, deleting = false;
    const speed = parseInt(el.dataset.speed) || 100;

    const type = () => {
      const word = words[wordIndex];
      if (!deleting) {
        el.textContent = word.slice(0, ++charIndex);
        if (charIndex === word.length) {
          deleting = true;
          setTimeout(type, 2000);
          return;
        }
      } else {
        el.textContent = word.slice(0, --charIndex);
        if (charIndex === 0) {
          deleting = false;
          wordIndex = (wordIndex + 1) % words.length;
        }
      }
      setTimeout(type, deleting ? speed / 2 : speed);
    };
    type();
  });

  // ── Smooth hover ripple ──────────────────────────────────────
  document.querySelectorAll('.btn-galaxy').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      const rect = this.getBoundingClientRect();
      ripple.style.cssText = `
        position:absolute;width:10px;height:10px;border-radius:50%;
        background:rgba(255,255,255,0.3);
        left:${e.clientX-rect.left-5}px;top:${e.clientY-rect.top-5}px;
        transform:scale(0);animation:ripple 0.6s ease forwards;pointer-events:none;
      `;
      this.appendChild(ripple);
      setTimeout(() => ripple.remove(), 700);
    });
  });

  // ── Galaxy Alert auto-dismiss ────────────────────────────────
  document.querySelectorAll('.galaxy-alert').forEach(alert => {
    setTimeout(() => alert.style.opacity = '0', 5000);
    setTimeout(() => alert.remove(), 5400);
  });

  // ── Mobile Nav Toggle ────────────────────────────────────────
  const menuBtn = document.getElementById('mobile-menu-btn');
  const navMenu = document.getElementById('nav-menu');
  if (menuBtn && navMenu) {
    menuBtn.addEventListener('click', () => {
      navMenu.classList.toggle('show');
      menuBtn.classList.toggle('open');
    });
  }

  // ── Project Category Filter ──────────────────────────────────
  const filterBtns = document.querySelectorAll('[data-filter]');
  const projectCards = document.querySelectorAll('[data-category]');

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const filter = btn.dataset.filter;
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      projectCards.forEach(card => {
        const show = filter === 'all' || card.dataset.category === filter;
        card.style.display = show ? '' : 'none';
        if (show) card.classList.add('animate-fade-up');
      });
    });
  });

  // ── Save Project (AJAX) ──────────────────────────────────────
  document.querySelectorAll('.save-project-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
      const projectId = this.dataset.projectId;
      try {
        const res = await fetch('/portfolio/visitor/save_project.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `project_id=${projectId}&csrf_token=${document.querySelector('meta[name=csrf]')?.content}`
        });
        const data = await res.json();
        this.innerHTML = data.saved
          ? '<i class="bi bi-bookmark-fill"></i>'
          : '<i class="bi bi-bookmark"></i>';
        showToast(data.message, data.saved ? 'success' : 'info');
      } catch(e) { showToast('Please login to save projects', 'warning'); }
    });
  });

  // ── Like Post (AJAX) ────────────────────────────────────────
  const likeBtn = document.getElementById('like-btn');
  if (likeBtn) {
    likeBtn.addEventListener('click', async function() {
      const postId = this.dataset.postId;
      try {
        const res = await fetch('/portfolio/blog_like.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `post_id=${postId}&csrf_token=${document.querySelector('meta[name=csrf]')?.content}`
        });
        const data = await res.json();
        const count = document.getElementById('like-count');
        if (count) count.textContent = data.likes;
        this.classList.toggle('liked', data.liked);
        this.querySelector('i').className = data.liked ? 'bi bi-heart-fill' : 'bi bi-heart';
      } catch(e) {}
    });
  }

  // ── Toast Notification ───────────────────────────────────────
  window.showToast = (message, type = 'info', duration = 3500) => {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;';
      document.body.appendChild(container);
    }
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    const toast = document.createElement('div');
    toast.className = `galaxy-alert galaxy-alert-${type} animate-fade-up`;
    toast.style.cssText = 'min-width:260px;max-width:360px;margin:0;';
    toast.innerHTML = `<i class="bi ${icons[type]||icons.info}"></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.4s'; }, duration);
    setTimeout(() => toast.remove(), duration + 500);
  };

  // ── Theme vars from settings ─────────────────────────────────
  const root = document.documentElement;
  const meta = document.querySelector('meta[name="theme-data"]');
  if (meta) {
    try {
      const theme = JSON.parse(meta.content);
      if (theme.primary)   root.style.setProperty('--purple-bright', theme.primary);
      if (theme.secondary) root.style.setProperty('--indigo-glow', theme.secondary);
      if (theme.accent)    root.style.setProperty('--neon-purple', theme.accent);
    } catch(e) {}
  }

});
