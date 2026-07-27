/**
 * ASTPMS 2.0 — Senior Full Stack Enterprise JavaScript Application Module
 * Features: Sticky Header Shrink, Dark Mode Toggle, Back To Top, Animated Counters,
 * Particles Background, Ripple Buttons, AOS Animation Init, Global Live Search
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    // Hide Loader
    const loader = document.getElementById('page-loader');
    if (loader) setTimeout(() => loader.classList.add('hidden'), 350);

    initSidebar();
    initStickyHeader();
    initTheme();
    initBackToTop();
    initAnimatedCounters();
    initRippleButtons();
    initParticlesCanvas();
    initGlobalSearch();
    initAOS();
});

/* ─── 0. UNIFIED SIDEBAR DRAWER & COLLAPSE CONTROL ──────── */
function initSidebar() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar       = document.getElementById('sidebar') || document.querySelector('.sidebar');
    const overlay       = document.getElementById('sidebarOverlay');

    if (!sidebarToggle || !sidebar) return;

    sidebarToggle.addEventListener('click', () => {
        if (window.innerWidth <= 991) {
            sidebar.classList.toggle('mobile-open');
            if (overlay) overlay.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('astpms-sidebar-collapsed', isCollapsed);
        }
    });

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }

    // Restore desktop collapsed preference
    if (window.innerWidth > 991 && localStorage.getItem('astpms-sidebar-collapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }
}

/* ─── 1. STICKY HEADER SHRINK ON SCROLL ────────────────── */
function initStickyHeader() {
    const header = document.getElementById('mainHeader');
    if (!header) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 40) {
            header.classList.add('shrunken');
        } else {
            header.classList.remove('shrunken');
        }
    });
}

/* ─── 2. DARK / LIGHT THEME TOGGLE ─────────────────────── */
function initTheme() {
    const toggleBtn = document.getElementById('theme-toggle');
    const html      = document.documentElement;

    const saved = localStorage.getItem('astpms-theme') || 'light';
    html.setAttribute('data-theme', saved);
    updateThemeIcon(saved);

    toggleBtn?.addEventListener('click', () => {
        const current = html.getAttribute('data-theme');
        const next    = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('astpms-theme', next);
        updateThemeIcon(next);
        window.dispatchEvent(new Event('theme-changed'));
    });
}

function updateThemeIcon(theme) {
    const icon = document.getElementById('theme-icon');
    if (!icon) return;
    icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

/* ─── 3. BACK TO TOP BUTTON ────────────────────────────── */
function initBackToTop() {
    const btn = document.getElementById('backToTopBtn');
    if (!btn) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) btn.classList.add('show');
        else btn.classList.remove('show');
    });

    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

/* ─── 4. ANIMATED COUNTERS ─────────────────────────────── */
function initAnimatedCounters() {
    const counters = document.querySelectorAll('.counter[data-target]');
    if (!counters.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });

    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(el) {
    const target   = parseInt(el.dataset.target, 10) || 0;
    const duration = 1400;
    const start    = performance.now();

    function update(now) {
        const elapsed  = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const ease     = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.floor(target * ease);
        if (progress < 1) requestAnimationFrame(update);
        else el.textContent = target;
    }
    requestAnimationFrame(update);
}

/* ─── 5. RIPPLE EFFECT ON BUTTONS ──────────────────────── */
function initRippleButtons() {
    document.querySelectorAll('.btn-premium, .btn-premium-outline, .ripple').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x    = e.clientX - rect.left;
            const y    = e.clientY - rect.top;
            const size = Math.max(rect.width, rect.height) * 2;

            const ripple = document.createElement('span');
            ripple.className = 'ripple-wave';
            ripple.style.cssText = `width:${size}px;height:${size}px;left:${x - size/2}px;top:${y - size/2}px`;
            this.appendChild(ripple);

            ripple.addEventListener('animationend', () => ripple.remove());
        });
    });
}

/* ─── 6. PARTICLES BACKGROUND CANVAS ────────────────────── */
function initParticlesCanvas() {
    const canvas = document.getElementById('particles-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    function resize() {
        canvas.width  = canvas.parentElement.offsetWidth;
        canvas.height = canvas.parentElement.offsetHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    const particles = [];
    class Particle {
        constructor() { this.reset(); }
        reset() {
            this.x     = Math.random() * canvas.width;
            this.y     = Math.random() * canvas.height;
            this.vx    = (Math.random() - 0.5) * 0.4;
            this.vy    = (Math.random() - 0.5) * 0.4;
            this.alpha = Math.random() * 0.35 + 0.05;
            this.r     = Math.random() * 2 + 0.5;
        }
        update() {
            this.x += this.vx; this.y += this.vy;
            if (this.x < 0 || this.x > canvas.width || this.y < 0 || this.y > canvas.height) this.reset();
        }
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(56, 189, 248, ${this.alpha})`;
            ctx.fill();
        }
    }

    for (let i = 0; i < 70; i++) particles.push(new Particle());

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 90) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(6, 182, 212, ${0.1 * (1 - dist / 90)})`;
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
            particles[i].update();
            particles[i].draw();
        }
        requestAnimationFrame(animate);
    }
    animate();
}

/* ─── 7. GLOBAL SEARCH API ──────────────────────────────── */
function initGlobalSearch() {
    const input = document.getElementById('globalSearchInput');
    if (!input) return;
    let timer;

    input.addEventListener('input', function() {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) return;
        timer = setTimeout(() => {
            fetch(`api/search.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    // Search dropdown handling
                }).catch(() => {});
        }, 350);
    });
}

/* ─── 8. AOS INIT ───────────────────────────────────────── */
function initAOS() {
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 600, once: true, offset: 60, easing: 'ease-out-cubic' });
    }
}
