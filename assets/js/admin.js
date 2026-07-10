/**
 * admin.js — Admin Dashboard Logic
 * Galaxy Portfolio CMS
 */

document.addEventListener('DOMContentLoaded', () => {

  // ── Sidebar Toggle (Mobile) ──────────────────────────────────
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebar = document.querySelector('.admin-sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', (e) => {
      if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  // ── Animate Stats Counter ────────────────────────────────────
  document.querySelectorAll('.stat-card-value[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count) || 0;
    const duration = 1200;
    const start = performance.now();
    const update = (now) => {
      const t = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - t, 3);
      el.textContent = Math.floor(target * eased);
      if (t < 1) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
  });

  // ── Image Preview on Upload ──────────────────────────────────
  document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
    const previewId = input.dataset.preview;
    const preview = document.getElementById(previewId);
    if (!preview) return;
    input.addEventListener('change', function() {
      const file = this.files[0];
      if (!file || !file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });
  });

  // ── Delete Confirmation ──────────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function(e) {
      const msg = this.dataset.confirm || 'Are you sure you want to delete this?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // ── Auto-generate Slug ───────────────────────────────────────
  const titleInput = document.getElementById('input-title');
  const slugInput  = document.getElementById('input-slug');
  if (titleInput && slugInput && !slugInput.value) {
    titleInput.addEventListener('input', function() {
      slugInput.value = this.value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s]+/g, '-')
        .replace(/-+/g, '-');
    });
  }

  // ── Tag Input ────────────────────────────────────────────────
  const tagContainer = document.getElementById('tag-container');
  const tagInput = document.getElementById('tag-input');
  const tagHidden = document.getElementById('tags-hidden');
  if (tagContainer && tagInput && tagHidden) {
    let tags = tagHidden.value ? JSON.parse(tagHidden.value) : [];
    const renderTags = () => {
      const existingTags = tagContainer.querySelectorAll('.admin-tag');
      existingTags.forEach(t => t.remove());
      tags.forEach((tag, i) => {
        const span = document.createElement('span');
        span.className = 'admin-tag glass-tag';
        span.innerHTML = `${tag} <button type="button" onclick="removeTag(${i})" style="background:none;border:none;color:inherit;cursor:pointer;padding:0 0 0 4px;">&times;</button>`;
        tagContainer.insertBefore(span, tagInput);
      });
      tagHidden.value = JSON.stringify(tags);
    };
    tagInput.addEventListener('keydown', function(e) {
      if ((e.key === 'Enter' || e.key === ',') && this.value.trim()) {
        e.preventDefault();
        tags.push(this.value.trim());
        this.value = '';
        renderTags();
      } else if (e.key === 'Backspace' && !this.value && tags.length) {
        tags.pop();
        renderTags();
      }
    });
    window.removeTag = (i) => { tags.splice(i, 1); renderTags(); };
    renderTags();
  }

  // ── Skill Level Slider ───────────────────────────────────────
  const levelSlider = document.getElementById('skill-level');
  const levelDisplay = document.getElementById('skill-level-display');
  if (levelSlider && levelDisplay) {
    const update = () => {
      levelDisplay.textContent = levelSlider.value + '%';
      levelSlider.style.background = `linear-gradient(90deg, #9333EA ${levelSlider.value}%, rgba(255,255,255,0.1) ${levelSlider.value}%)`;
    };
    levelSlider.addEventListener('input', update);
    update();
  }

  // ── Tech multi-select (projects) ─────────────────────────────
  const techInput = document.getElementById('tech-input');
  const techContainer = document.getElementById('tech-container');
  const techHidden = document.getElementById('techs-hidden');
  if (techInput && techContainer && techHidden) {
    let techs = techHidden.value ? JSON.parse(techHidden.value) : [];
    const renderTechs = () => {
      techContainer.querySelectorAll('.admin-tag').forEach(t => t.remove());
      techs.forEach((tech, i) => {
        const span = document.createElement('span');
        span.className = 'admin-tag tech-tag';
        span.innerHTML = `${tech} <button type="button" onclick="removeTech(${i})" style="background:none;border:none;color:inherit;cursor:pointer;">&times;</button>`;
        techContainer.insertBefore(span, techInput);
      });
      techHidden.value = JSON.stringify(techs);
    };
    techInput.addEventListener('keydown', function(e) {
      if ((e.key === 'Enter' || e.key === ',') && this.value.trim()) {
        e.preventDefault();
        techs.push(this.value.trim());
        this.value = '';
        renderTechs();
      }
    });
    window.removeTech = (i) => { techs.splice(i, 1); renderTechs(); };
    renderTechs();
  }

  // ── Color Picker Live Preview ─────────────────────────────────
  document.querySelectorAll('input[type="color"][data-var]').forEach(input => {
    const update = () => document.documentElement.style.setProperty(input.dataset.var, input.value);
    input.addEventListener('input', update);
  });

  // ── AJAX Delete ───────────────────────────────────────────────
  document.querySelectorAll('[data-ajax-delete]').forEach(btn => {
    btn.addEventListener('click', async function() {
      const msg = this.dataset.confirm || 'Delete this item?';
      if (!confirm(msg)) return;
      const url = this.dataset.ajaxDelete;
      try {
        const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `csrf_token=${document.querySelector('meta[name=csrf]')?.content}&_method=DELETE` });
        const data = await res.json();
        if (data.success) {
          const row = this.closest('tr') || this.closest('.card-item');
          if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
          showAdminToast(data.message || 'Deleted!', 'success');
        } else { showAdminToast(data.message || 'Error', 'error'); }
      } catch(e) { showAdminToast('Request failed', 'error'); }
    });
  });

  // ── Admin Toast ───────────────────────────────────────────────
  window.showAdminToast = (message, type = 'info') => {
    let container = document.getElementById('admin-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'admin-toast-container';
      container.style.cssText = 'position:fixed;top:80px;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;';
      document.body.appendChild(container);
    }
    const icons = { success:'bi-check-circle-fill', error:'bi-x-circle-fill', warning:'bi-exclamation-triangle-fill', info:'bi-info-circle-fill' };
    const toast = document.createElement('div');
    toast.className = `galaxy-alert galaxy-alert-${type}`;
    toast.style.cssText = 'min-width:260px;max-width:360px;margin:0;animation:fade-in-right 0.3s ease;';
    toast.innerHTML = `<i class="bi ${icons[type]||icons.info}"></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity='0'; toast.style.transition='0.3s'; }, 3000);
    setTimeout(() => toast.remove(), 3500);
  };

  // ── Media Library Selection ──────────────────────────────────
  window.selectMedia = (url, targetId) => {
    const target = document.getElementById(targetId);
    if (target) {
      if (target.tagName === 'IMG') target.src = url;
      else target.value = url;
    }
    const modal = document.getElementById('mediaModal');
    if (modal) bootstrap.Modal.getInstance(modal)?.hide();
  };

  // ── Backup Download ──────────────────────────────────────────
  const backupBtn = document.getElementById('start-backup');
  if (backupBtn) {
    backupBtn.addEventListener('click', async function() {
      this.disabled = true;
      this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating...';
      try {
        const res = await fetch('/portfolio/admin/backup_action.php', { method: 'POST', body: new FormData(document.getElementById('backup-form')) });
        if (res.ok) {
          const blob = await res.blob();
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url; a.download = 'portfolio_backup_' + Date.now() + '.sql'; a.click();
          showAdminToast('Backup created!', 'success');
        }
      } catch(e) { showAdminToast('Backup failed', 'error'); }
      this.disabled = false;
      this.innerHTML = '<i class="bi bi-download"></i> Create Backup';
    });
  }

});

// Tag input style
const style = document.createElement('style');
style.textContent = `
  #tag-container, #tech-container {
    display: flex; flex-wrap: wrap; gap: 0.3rem;
    background: rgba(255,255,255,0.04); border: 1px solid rgba(196,181,253,0.2);
    border-radius: 12px; padding: 0.5rem; min-height: 46px; align-items: center;
    cursor: text;
  }
  #tag-container input, #tech-container input {
    background: none; border: none; outline: none;
    color: rgba(255,255,255,0.9); font-family: inherit; font-size: 0.88rem;
    flex: 1; min-width: 120px; padding: 0.1rem 0.3rem;
  }
  .admin-tag { cursor: default; }
  input[type="range"] { -webkit-appearance: none; height: 6px; border-radius: 10px; cursor: pointer; }
  input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; width: 18px; height: 18px; border-radius: 50%; background: #9333EA; cursor: pointer; box-shadow: 0 0 8px rgba(147,51,234,0.6); }
`;
document.head.appendChild(style);
