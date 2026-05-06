/**
 * app.js — Lógica JS global del panel Huaquillas F.C.
 *
 * Gestiona la autenticación via JWT (localStorage) y las llamadas fetch
 * a los endpoints del backend:
 *   GET /app/controllers/api/users/index.php?api=profile    → perfil de usuario
 *   GET /app/controllers/api/users/index.php?api=institucion → datos de institución
 *   GET /app/controllers/api/users/index.php?api=menu        → menú dinámico
 *
 * El token JWT se gestiona 100% en el cliente (localStorage + fetch headers).
 * El backend NO almacena sesiones PHP.
 */

/* ─────────────────────────────────────────────────────────────
   AUTH
───────────────────────────────────────────────────────────── */

/**
 * checkAuth: verifica que existe un token en localStorage.
 * Si no hay token, redirige a la raíz (/) para que el router PHP
 * sirva el login.html.twig.
 */
function checkAuth() {
  if (!localStorage.getItem('token')) {
    window.location.href = '/';
  }
}

/**
 * logout: elimina el token del localStorage y redirige al login.
 */
function logout() {
  localStorage.removeItem('token');
  window.location.href = '/';
}

/* ─────────────────────────────────────────────────────────────
   FETCH HELPERS
───────────────────────────────────────────────────────────── */

/**
 * authFetch: wrapper sobre fetch que añade el header Authorization: Bearer <token>.
 * @param {string} url
 * @returns {Promise<Response>}
 */
function authFetch(url) {
  const token = localStorage.getItem('token');
  return fetch(url, {
    headers: { 'Authorization': 'Bearer ' + token }
  });
}

/* ─────────────────────────────────────────────────────────────
   USER PROFILE — ?api=profile
   Respuesta esperada: { identificacion, primer_nombre, primer_apellido, tipo }
───────────────────────────────────────────────────────────── */

/**
 * loadUserProfile: obtiene el perfil del usuario autenticado y actualiza
 * los elementos del sidebar (avatar, nombre) y del topbar.
 */
async function loadUserProfile() {
  try {
    const res  = await authFetch(CONFIG.API_BASE_URL+'/app/controllers/api/users/index.php?api=profile');
    const json = await res.json();
    const data = json.data;

    if (!data || !data.primer_nombre) return;

    const fullName = (data.primer_nombre + ' ' + data.primer_apellido).trim();
    const initial  = (data.primer_nombre[0] || 'U').toUpperCase();

    // Sidebar footer
    var sbUsername = document.getElementById('sidebar-username');
    var sbAvatar   = document.getElementById('sidebar-avatar');
    if (sbUsername) sbUsername.textContent = fullName;
    if (sbAvatar)   sbAvatar.textContent   = initial;

    // Topbar dropdown
    var tbUsername = document.getElementById('topbar-username');
    var tbAvatar   = document.getElementById('topbar-avatar');
    if (tbUsername) tbUsername.textContent = fullName;
    if (tbAvatar)   tbAvatar.textContent   = initial;

  } catch (err) {
    // Si el token expiró o el endpoint falla, ignorar silenciosamente
  }
}

/* ─────────────────────────────────────────────────────────────
   INSTITUCIÓN — ?api=institucion
   Respuesta esperada: { nombre, logo }
───────────────────────────────────────────────────────────── */

/**
 * loadInstitucion: obtiene los datos de la institución y actualiza
 * el sidebar (logo + nombre) y la badge del topbar.
 */
async function loadInstitucion() {
  try {
    const res  = await authFetch(CONFIG.API_BASE_URL+'/app/controllers/api/users/index.php?api=institucion');
    const json = await res.json();
    const data = json.data;

    if (!data || !data.nombre) return;

    // Sidebar: actualizar nombre
    var sidebarNombre = document.getElementById('sidebar-institucion-nombre');
    if (sidebarNombre) sidebarNombre.innerHTML = data.nombre;

    // Sidebar: actualizar logo (imagen o iniciales)
    var sidebarLogoText = document.getElementById('sidebar-logo-text');
    var sidebarLogoBox  = document.getElementById('sidebar-logo-box');
    if (data.logo && sidebarLogoBox) {
      sidebarLogoBox.innerHTML = '<img src="' + data.logo + '" alt="Logo" style="max-height:36px;max-width:36px;border-radius:6px;">';
    } else if (sidebarLogoText) {
      // Usar primeras letras del nombre como iniciales
      var iniciales = data.nombre
        .split(' ')
        .slice(0, 2)
        .map(function(w) { return w[0]; })
        .join('')
        .toUpperCase();
      sidebarLogoText.textContent = iniciales;
    }

    // Topbar: mostrar badge con el nombre de la institución
    var badge = document.getElementById('topbar-institucion-badge');
    if (badge) {
      badge.textContent = data.nombre;
      badge.style.display = '';
    }

  } catch (err) {
    // Silencioso
  }
}

/* ─────────────────────────────────────────────────────────────
   MENÚ DINÁMICO — ?api=menu
   Respuesta esperada: Array de { orden, nombre, icon, tipo }
───────────────────────────────────────────────────────────── */

/**
 * loadMenu: obtiene el menú dinámico del servidor y reemplaza el menú
 * estático del sidebar con los ítems devueltos por la API.
 * El campo `icon` debe ser un nombre de Bootstrap Icons (ej: "house-fill").
 * El campo `tipo` puede usarse como href para el enrutador PHP.
 */
async function loadMenu() {
  try {
    const res   = await authFetch(CONFIG.API_BASE_URL+'/app/controllers/api/users/index.php?api=menu');
    const json  = await res.json();
    const items = json.data;

    if (!Array.isArray(items) || items.length === 0) return;

    var menuEl = document.getElementById('sidebar-menu');
    if (!menuEl) return;

    // Determinar la ruta actual para marcar el ítem activo
    var currentPath = window.location.pathname;

    menuEl.innerHTML = items
      .sort(function(a, b) { return a.orden - b.orden; })
      .map(function(item) {
        var href  = '/' + (item.tipo || '').toLowerCase();
        var icon  = item.icon || 'circle';
        var isActive = currentPath === href ? 'active' : '';
        return '<li class="nav-item">' +
          '<a class="nav-link ' + isActive + '" href="' + href + '">' +
          '<i class="bi bi-' + icon + ' me-2"></i>' + item.nombre +
          '</a></li>';
      })
      .join('');

  } catch (err) {
    // Si falla, se mantiene el menú estático del Twig
  }
}

/* ─────────────────────────────────────────────────────────────
   INICIALIZACIÓN
───────────────────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {
  // Verificar autenticación antes de cargar cualquier dato
  checkAuth();

  // Cargar datos del usuario, institución y menú en paralelo
  Promise.all([
    loadUserProfile(),
    loadInstitucion(),
    loadMenu(),
  ]);
});
