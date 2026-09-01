function injectLayout() {
  const headerSlot = document.getElementById('site-header-slot');
  const footerSlot = document.getElementById('site-footer-slot');

  if (headerSlot) {
    headerSlot.innerHTML = `
      <header class="site-header">
        <a class="brand" href="home.html" aria-label="Ir al dashboard">
          <span class="logo-text">SIGAH</span>
          <span class="logo-sub">EET N°3100</span>
        </a>

        <button class="hamburger" aria-label="Abrir menú" type="button">☰</button>

        <nav id="main-nav" aria-label="Navegación principal">
          <a href="home.html">Dashboard</a>
          <a href="asist.html">Asistencia</a>
          <a href="registry-al.html">Alumnos</a>
          <a href="report.html">Reportes</a>
          <a href="notif.html">Notificaciones</a>
          <a href="faq.html">FAQ</a>
          <a href="config.html">Configuración</a>
        </nav>

        <div class="header-actions">
          <button class="theme-toggle" id="themeToggle" type="button" aria-label="Cambiar tema">
            <i class="fas fa-moon"></i>
          </button>
          <button class="btn btn-outline btn-small" id="logoutBtn" type="button">Salir</button>
        </div>
      </header>
    `;

    const nav = document.getElementById('main-nav');
    const hamburger = document.querySelector('.hamburger');
    const toggleBtn = document.getElementById('themeToggle');
    const logoutBtn = document.getElementById('logoutBtn');

    if (hamburger && nav) {
      hamburger.addEventListener('click', () => nav.classList.toggle('open'));
    }

    if (toggleBtn) {
      const savedTheme = localStorage.getItem('sigah-theme') || 'light';
      document.body.classList.toggle('dark-mode', savedTheme === 'dark');
      const syncIcon = () => {
        const icon = toggleBtn.querySelector('i');
        if (!icon) return;
        icon.className = document.body.classList.contains('dark-mode') ? 'fas fa-sun' : 'fas fa-moon';
      };
      syncIcon();

      toggleBtn.addEventListener('click', () => {
        const isDark = document.body.classList.toggle('dark-mode');
        localStorage.setItem('sigah-theme', isDark ? 'dark' : 'light');
        syncIcon();
      });
    }

    if (logoutBtn) {
      logoutBtn.addEventListener('click', () => {
        localStorage.removeItem('usuarioActual');
        window.location.href = 'index.html';
      });
    }

    const currentPage = window.location.pathname.split('/').pop() || 'home.html';
    nav.querySelectorAll('a').forEach(link => {
      const href = link.getAttribute('href');
      if (href === currentPage) {
        link.classList.add('active');
      }
    });
  }

  if (footerSlot) {
    footerSlot.innerHTML = `
      <footer>
        <div class="footer-inner">
          <div class="footer-brand">
            <h4>SIGAH</h4>
            <p>Sistema Integral de Gestión de Asistencias y Horarios de la EET N°3100 "Rep. de la India".</p>
          </div>
          <div class="footer-links">
            <h5>Secciones</h5>
            <a href="home.html">Dashboard</a>
            <a href="asist.html">Asistencia</a>
            <a href="report.html">Reportes</a>
          </div>
          <div class="footer-contact">
            <h5>Contacto</h5>
            <p><i class="fas fa-envelope"></i> sigah@eet3100.edu.ar</p>
            <p><i class="fas fa-phone"></i> +54 387 000-0000</p>
          </div>
        </div>
        <div class="footer-copy">© 2026 SIGAH - Diseño UI/UX</div>
      </footer>
    `;
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', injectLayout);
} else {
  injectLayout();
}
