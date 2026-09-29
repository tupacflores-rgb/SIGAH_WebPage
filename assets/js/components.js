/* ============================================================
   SIGAH — components.js
   El header y el footer ahora los arma PHP (partials/).
   Acá solo queda el comportamiento del lado del cliente.
   ============================================================ */

(function () {
  'use strict';

  var basePath = (window.SIGAH && window.SIGAH.basePath) || '';

  /* --------------------------------------------------------
     Menú hamburguesa
     -------------------------------------------------------- */
  function initHamburger() {
    var nav = document.getElementById('main-nav');
    var hamburger = document.querySelector('.hamburger');

    if (!nav || !hamburger) return;

    hamburger.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  /* --------------------------------------------------------
     Tema claro / oscuro
     El servidor ya pinta <body class="dark-mode"> leyendo la
     cookie, así que no hay parpadeo al cargar la página.
     -------------------------------------------------------- */
  function saveTheme(theme) {
    var oneYear = 60 * 60 * 24 * 365;
    document.cookie = 'sigah_theme=' + theme + '; path=' + (basePath || '') + '/; max-age=' + oneYear + '; SameSite=Lax';
  }

  function initThemeToggle() {
    var toggle = document.getElementById('themeToggle');
    if (!toggle) return;

    toggle.addEventListener('click', function () {
      var isDark = document.body.classList.toggle('dark-mode');
      var icon = toggle.querySelector('i');

      if (icon) icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
      toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');

      saveTheme(isDark ? 'dark' : 'light');
    });
  }

  /* --------------------------------------------------------
     Cerrar modales con Escape y con clic en el fondo
     -------------------------------------------------------- */
  function initModals() {
    document.addEventListener('click', function (event) {
      if (event.target.classList && event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') return;

      document.querySelectorAll('.modal.show').forEach(function (modal) {
        modal.classList.remove('show');
      });
    });
  }

  function init() {
    initHamburger();
    initThemeToggle();
    initModals();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
