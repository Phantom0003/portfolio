/**
 * particles.js — Canvas Star Field
 * Galaxy Portfolio CMS
 */

class StarField {
  constructor(canvasId = 'star-canvas') {
    this.canvas = document.getElementById(canvasId);
    if (!this.canvas) return;
    this.ctx = this.canvas.getContext('2d');
    this.stars = [];
    this.shootingStars = [];
    this.animFrame = null;
    this.mouseX = 0;
    this.mouseY = 0;
    this.parallaxStrength = 0.03;
    this.enabled = true;

    this.resize();
    this.init();
    this.animate();

    window.addEventListener('resize', () => this.resize());
    document.addEventListener('mousemove', (e) => {
      this.mouseX = (e.clientX - window.innerWidth / 2) * this.parallaxStrength;
      this.mouseY = (e.clientY - window.innerHeight / 2) * this.parallaxStrength;
    });

    // Shoot a star every 4-8 seconds
    setInterval(() => this.addShootingStar(), 4000 + Math.random() * 4000);
  }

  resize() {
    if (!this.canvas) return;
    this.canvas.width = window.innerWidth;
    this.canvas.height = window.innerHeight;
    if (this.stars.length) this.init();
  }

  init() {
    this.stars = [];
    const count = Math.floor((this.canvas.width * this.canvas.height) / 3000);
    for (let i = 0; i < count; i++) {
      this.stars.push(this.createStar());
    }
  }

  createStar() {
    const size = Math.random();
    const layer = size < 0.4 ? 1 : size < 0.7 ? 2 : 3; // depth layer
    return {
      x: Math.random() * this.canvas.width,
      y: Math.random() * this.canvas.height,
      size: 0.3 + Math.random() * (layer === 3 ? 2.5 : layer === 2 ? 1.5 : 0.8),
      opacity: 0.2 + Math.random() * 0.8,
      twinkleSpeed: 0.003 + Math.random() * 0.01,
      twinkleOffset: Math.random() * Math.PI * 2,
      layer,
      color: this.randomStarColor(),
      dx: (Math.random() - 0.5) * 0.015 * layer,
      dy: (Math.random() - 0.5) * 0.01 * layer,
    };
  }

  randomStarColor() {
    const colors = [
      'rgba(255,255,255,',
      'rgba(196,181,253,',
      'rgba(129,140,248,',
      'rgba(244,114,182,',
      'rgba(167,139,250,',
    ];
    return colors[Math.floor(Math.random() * colors.length)];
  }

  addShootingStar() {
    this.shootingStars.push({
      x: Math.random() * this.canvas.width * 0.6,
      y: Math.random() * this.canvas.height * 0.4,
      length: 80 + Math.random() * 120,
      speed: 8 + Math.random() * 8,
      opacity: 1,
      angle: Math.PI / 4 + (Math.random() - 0.5) * 0.3,
      tail: [],
    });
  }

  animate() {
    if (!this.enabled) return;
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

    const t = Date.now() * 0.001;

    // Draw stars
    for (const star of this.stars) {
      // Slow drift
      star.x += star.dx + this.mouseX * 0.001 * star.layer;
      star.y += star.dy + this.mouseY * 0.001 * star.layer;

      // Wrap edges
      if (star.x < 0) star.x = this.canvas.width;
      if (star.x > this.canvas.width) star.x = 0;
      if (star.y < 0) star.y = this.canvas.height;
      if (star.y > this.canvas.height) star.y = 0;

      // Twinkle
      const twinkle = 0.4 + 0.6 * Math.abs(Math.sin(t * star.twinkleSpeed * 100 + star.twinkleOffset));
      const alpha = star.opacity * twinkle;

      // Parallax offset by mouse
      const px = star.x - this.mouseX * star.layer * 0.3;
      const py = star.y - this.mouseY * star.layer * 0.3;

      this.ctx.beginPath();
      this.ctx.arc(px, py, star.size, 0, Math.PI * 2);
      this.ctx.fillStyle = star.color + alpha + ')';
      this.ctx.fill();

      // Glow for larger stars
      if (star.size > 1.5 && star.layer === 3) {
        const grad = this.ctx.createRadialGradient(px, py, 0, px, py, star.size * 3);
        grad.addColorStop(0, star.color + alpha * 0.4 + ')');
        grad.addColorStop(1, 'transparent');
        this.ctx.beginPath();
        this.ctx.arc(px, py, star.size * 3, 0, Math.PI * 2);
        this.ctx.fillStyle = grad;
        this.ctx.fill();
      }
    }

    // Shooting stars
    for (let i = this.shootingStars.length - 1; i >= 0; i--) {
      const s = this.shootingStars[i];
      s.x += Math.cos(s.angle) * s.speed;
      s.y += Math.sin(s.angle) * s.speed;
      s.opacity -= 0.015;

      if (s.opacity <= 0) { this.shootingStars.splice(i, 1); continue; }

      const tailX = s.x - Math.cos(s.angle) * s.length;
      const tailY = s.y - Math.sin(s.angle) * s.length;

      const grad = this.ctx.createLinearGradient(tailX, tailY, s.x, s.y);
      grad.addColorStop(0, 'transparent');
      grad.addColorStop(0.5, `rgba(196,181,253,${s.opacity * 0.3})`);
      grad.addColorStop(1, `rgba(255,255,255,${s.opacity})`);

      this.ctx.beginPath();
      this.ctx.moveTo(tailX, tailY);
      this.ctx.lineTo(s.x, s.y);
      this.ctx.strokeStyle = grad;
      this.ctx.lineWidth = 2;
      this.ctx.stroke();
    }

    this.animFrame = requestAnimationFrame(() => this.animate());
  }

  destroy() {
    this.enabled = false;
    if (this.animFrame) cancelAnimationFrame(this.animFrame);
  }
}

// Auto-init
document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('star-canvas')) {
    window.starField = new StarField('star-canvas');
  }
});
