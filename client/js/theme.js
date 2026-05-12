// Cambio de tema: alterna clase 'theme-light' en el body
function toggleTheme() {
  document.body.classList.toggle('theme-light');
  const icon = document.getElementById('theme-icon');
  if (document.body.classList.contains('theme-light')) {
    icon.className = 'bi bi-sun-fill';
    localStorage.setItem('theme', 'light');
  } else {
    icon.className = 'bi bi-moon-fill';
    localStorage.setItem('theme', 'dark');
  }
}

// Toggle sidebar en móviles
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  if (sidebar) sidebar.classList.toggle('show');
  if (overlay) overlay.classList.toggle('show');
}

// Cerrar sidebar al hacer clic en overlay
document.addEventListener('DOMContentLoaded', function () {
  const overlay = document.getElementById('sidebar-overlay');
  if (overlay) {
    overlay.addEventListener('click', function () {
      const sidebar = document.getElementById('sidebar');
      if (sidebar) sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });
  }

  // Cargar tema guardado al iniciar
  if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('theme-light');
    const icon = document.getElementById('theme-icon');
    if (icon) icon.className = 'bi bi-sun-fill';
  }
});
