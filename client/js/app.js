/**
 * app.js — Lógica JS global del panel Huaquillas F.C.
 *
 * Gestiona la autenticación via JWT (localStorage) y las llamadas fetch
 * a los endpoints del backend usando CONFIG (config.js):
 *   GET CONFIG.ENDPOINTS.users   ?api=profile    → perfil de usuario
 *   GET CONFIG.ENDPOINTS.company ?api=institucion → datos de institución
 *   GET CONFIG.ENDPOINTS.buttom  ?api=menu        → menú dinámico por rol
 *
 * El token JWT se gestiona 100% en el cliente (localStorage + fetch headers).
 * El backend NO almacena sesiones PHP.
 */

/* ─────────────────────────────────────────────────────────────
   AUTH
───────────────────────────────────────────────────────────── */

function checkAuth() {
  if (!localStorage.getItem(CONFIG.STORAGE_KEYS.token)) {
    window.location.href = CONFIG.ROUTES.login;
  }
}

function logout() {
  localStorage.removeItem(CONFIG.STORAGE_KEYS.token);
  window.location.href = CONFIG.ROUTES.login;
}

/* ─────────────────────────────────────────────────────────────
   FETCH HELPERS
───────────────────────────────────────────────────────────── */

/**
 * authGet: realiza un GET autenticado a un endpoint con un parámetro ?api=
 * Extrae automáticamente payload.data del wrapper { status, data, message }.
 */
async function authGet(endpoint, apiParam) {
  const token = localStorage.getItem(CONFIG.STORAGE_KEYS.token);
  const url   = CONFIG.API_BASE_URL + endpoint + '?api=' + encodeURIComponent(apiParam);

  const res  = await fetch(url, {
    headers: { 'Authorization': 'Bearer ' + token }
  });

  const json = await res.json();

  if (!res.ok || json.status === 'error') {
    throw new Error(json.message || 'Error en la solicitud.');
  }

  return json.data !== undefined ? json.data : json;
}

/* ─────────────────────────────────────────────────────────────
   USER PROFILE — users ?api=profile
   Respuesta esperada: { identificacion, primer_nombre, primer_apellido, tipo }
───────────────────────────────────────────────────────────── */

async function loadUserProfile() {
  try {
    const data = await authGet(CONFIG.ENDPOINTS.users, 'profile');

    if (!data || !data.primer_nombre) return;

    const fullName = (data.primer_nombre + ' ' + (data.primer_apellido || '')).trim();
    const initial  = (data.primer_nombre[0] || 'U').toUpperCase();

    var sbUsername = document.getElementById('sidebar-username');
    var sbAvatar   = document.getElementById('sidebar-avatar');
    if (sbUsername) sbUsername.textContent = fullName;
    if (sbAvatar)   sbAvatar.textContent   = initial;

    var tbUsername = document.getElementById('topbar-username');
    var tbAvatar   = document.getElementById('topbar-avatar');
    if (tbUsername) tbUsername.textContent = fullName;
    if (tbAvatar)   tbAvatar.textContent   = initial;

  } catch (err) {
    // Silencioso — si el token expiró, api.js maneja el redirect
  }
}

/* ─────────────────────────────────────────────────────────────
   INSTITUCIÓN — company ?api=institucion
   Respuesta esperada: { nombre, logo }
───────────────────────────────────────────────────────────── */

async function loadInstitucion() {
  try {
    const data = await authGet(CONFIG.ENDPOINTS.company, 'institucion');

    if (!data || !data.nombre) return;

    var sidebarNombre = document.getElementById('sidebar-institucion-nombre');
    if (sidebarNombre) sidebarNombre.innerHTML = data.nombre;

    var sidebarLogoBox = document.getElementById('sidebar-logo-box');
    if (data.logo && sidebarLogoBox) {
      sidebarLogoBox.innerHTML = '<img src="' + data.logo + '" alt="Logo" style="max-height:36px;max-width:36px;border-radius:6px;">';
    }

    var badge = document.getElementById('topbar-institucion-badge');
    if (badge) {
      badge.textContent    = data.nombre;
      badge.style.display  = '';
    }

  } catch (err) {
    // Silencioso
  }
}

/* ─────────────────────────────────────────────────────────────
   MENÚ DINÁMICO — buttom ?api=menu
   Respuesta esperada: Array de { orden, nombre, icon, tipo }
───────────────────────────────────────────────────────────── */

async function loadMenu() {
  try {
    const items = await authGet(CONFIG.ENDPOINTS.buttom, 'menu');

    if (!Array.isArray(items) || items.length === 0) return;

    var menuEl = document.getElementById('sidebar-menu');
    if (!menuEl) return;

    var currentPath = window.location.pathname;

    menuEl.innerHTML = items
      .sort(function(a, b) { return (a.orden || 0) - (b.orden || 0); })
      .map(function(item) {
        var rawRoute = (item.tipo || '').toLowerCase();
        var href     = CONFIG.ROUTE_ALIASES[rawRoute] || ('/' + rawRoute);
        var icon     = item.icon || 'circle';
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
  checkAuth();

  Promise.all([
    loadUserProfile(),
    loadInstitucion(),
    loadMenu(),
  ]);
});
