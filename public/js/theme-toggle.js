/**
 * POS Arepas Boyacenses — Theme Toggle
 * Maneja el switch claro/oscuro con persistencia en localStorage
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'pos-arepas-theme';
  const DARK        = 'dark';
  const LIGHT       = 'light';

  // Aplicar tema antes de que el DOM se pinte (evita flash)
  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.body && document.body.setAttribute('data-theme', theme);
  }

  function getSavedTheme() {
    try { return localStorage.getItem(STORAGE_KEY) || LIGHT; }
    catch (e) { return LIGHT; }
  }

  function saveTheme(theme) {
    try { localStorage.setItem(STORAGE_KEY, theme); } catch (e) {}
  }

  function getCurrentTheme() {
    return document.documentElement.getAttribute('data-theme') || LIGHT;
  }

  // Aplicar inmediatamente
  applyTheme(getSavedTheme());

  // Cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', function () {
    var checkbox = document.getElementById('theme-toggle-checkbox');
    if (!checkbox) return;

    var isDark = getSavedTheme() === DARK;
    checkbox.checked = isDark;
    applyTheme(isDark ? DARK : LIGHT);
    updateToggleIcon(isDark);

    checkbox.addEventListener('change', function () {
      var newTheme = this.checked ? DARK : LIGHT;
      applyTheme(newTheme);
      saveTheme(newTheme);
      updateToggleIcon(this.checked);
      updateChartColors(newTheme);
    });
  });

  function updateToggleIcon(isDark) {
    var icon = document.getElementById('theme-icon');
    if (!icon) return;
    icon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
  }

  /**
   * Si hay gráficas de Chart.js en la página, actualiza sus colores.
   * Esto es opcional — las gráficas se regeneran al hacer submit del filtro.
   */
  function updateChartColors(theme) {
    if (typeof Chart === 'undefined') return;
    var isDark = theme === DARK;
    var textColor   = isDark ? '#9CA3AF' : '#6b7280';
    var gridColor   = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    Chart.defaults.global.defaultFontColor = textColor;
  }

  // Exponer para uso externo si se necesita
  window.PosTheme = {
    toggle: function () {
      var curr   = getCurrentTheme();
      var next   = curr === DARK ? LIGHT : DARK;
      applyTheme(next);
      saveTheme(next);
      var cb = document.getElementById('theme-toggle-checkbox');
      if (cb) cb.checked = next === DARK;
      updateToggleIcon(next === DARK);
    },
    getTheme: getCurrentTheme
  };
}());
