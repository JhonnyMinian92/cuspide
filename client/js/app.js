/**
 * app.js — Lógica JS global del panel Huaquillas F.C.
 *
 * Gestiona la autenticación via JWT (localStorage) y las llamadas fetch
 * a los endpoints del backend usando CONFIG (config.js):
 *   GET CONFIG.ENDPOINTS.users   ?api=find     → datos básicos del usuario (incluye tipo)
 *   GET CONFIG.ENDPOINTS.users   ?api=profile&tipo=XXXX → perfil completo
 *   GET CONFIG.ENDPOINTS.company ?api=find     → datos de institución (sin auth)
 *   GET CONFIG.ENDPOINTS.buttom  ?api=menu      → menú dinámico por rol
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
 * Normaliza la URL para evitar doble slash y extrae payload.data automáticamente.
 */
async function authGet(endpoint, apiParam) {
  const token = localStorage.getItem(CONFIG.STORAGE_KEYS.token);
  const base = (CONFIG.API_BASE_URL + endpoint).replace(/\/+$/, '');
  const url = base + '/?api=' + encodeURIComponent(apiParam);

  const res = await fetch(url, {
    headers: { 'Authorization': 'Bearer ' + token }
  });

  const json = await res.json();

  if (!res.ok || json.status === 'error') {
    throw new Error(json.message || 'Error en la solicitud.');
  }

  return json.data !== undefined ? json.data : json;
}

/* ─────────────────────────────────────────────────────────────
   USER PROFILE — 2 pasos: users ?api=find → users ?api=profile&tipo=XXXX
   Respuesta esperada paso 1: { tipo, ... }
   Respuesta esperada paso 2: { primer_nombre, primer_apellido, ... }
───────────────────────────────────────────────────────────── */

async function loadUserProfile() {
  try {
    const data = await window.Cuspide.api.users('find');

    if (!data) {
      throw new Error('No se recibieron datos del usuario.');
    }

    const fullName = [
      data.primer_nombre,
      data.primer_apellido
    ]
      .filter(Boolean)
      .join(' ')
      .trim() || 'Usuario';

    const initial = (fullName.charAt(0) || 'U').toUpperCase();

    const sidebarUsername = document.getElementById('sidebar-username');
    const sidebarAvatar = document.getElementById('sidebar-avatar');
    const topbarUsername = document.getElementById('topbar-username');
    const topbarAvatar = document.getElementById('topbar-avatar');

    if (sidebarUsername) sidebarUsername.textContent = fullName;
    if (sidebarAvatar) sidebarAvatar.textContent = initial;

    if (topbarUsername) topbarUsername.textContent = fullName;
    if (topbarAvatar) topbarAvatar.textContent = initial;

  } catch (err) {
    console.error('Error cargando usuario:', err);

    const sidebarUsername = document.getElementById('sidebar-username');
    const sidebarAvatar = document.getElementById('sidebar-avatar');
    const topbarUsername = document.getElementById('topbar-username');
    const topbarAvatar = document.getElementById('topbar-avatar');

    if (sidebarUsername) sidebarUsername.textContent = 'Usuario';
    if (sidebarAvatar) sidebarAvatar.textContent = 'U';

    if (topbarUsername) topbarUsername.textContent = 'Usuario';
    if (topbarAvatar) topbarAvatar.textContent = 'U';
  }
}

/* ─────────────────────────────────────────────────────────────
   INSTITUCIÓN — company ?api=find (sin Authorization header)
   Respuesta esperada: { nombre, logo }
───────────────────────────────────────────────────────────── */

async function loadInstitucion() {
  try {
    // company/?api=find no requiere auth según el back
    const base = (CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.company).replace(/\/+$/, '');
    const url = base + '/?api=find';
    const res = await fetch(url);
    const json = await res.json();
    const data = json.data !== undefined ? json.data : json;

    if (!data || !data.nombre) return;

    var sidebarNombre = document.getElementById('sidebar-institucion-nombre');
    if (sidebarNombre) sidebarNombre.innerHTML = data.nombre;

    var sidebarLogoBox = document.getElementById('sidebar-logo-box');
    if (data.logo && sidebarLogoBox) {
      sidebarLogoBox.innerHTML = '<img src="' + data.logo + '" alt="Logo" style="max-height:36px;max-width:36px;border-radius:6px;">';
    }

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
   MENÚ DINÁMICO — buttom ?api=menu
   Respuesta esperada: Array de { orden, nombre, icon, tipo }
───────────────────────────────────────────────────────────── */

function normalizeRouteKey(value) {
  return String(value || '')
    .trim()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '') // quita tildes
    .replace(/\s+/g, '-')            // espacios a guiones
    .replace(/[^a-z0-9-]/g, '');     // limpia caracteres raros
}

function resolveMenuIcon(icon, routeKey) {
  var iconsByRoute = {
    dashboard: 'house-fill',
    alumnos: 'people-fill',
    membresias: 'credit-card-fill',
    inventario: 'box-seam-fill',
    categorias: 'tags-fill',
    configuracion: 'gear-fill'
  };

  return iconsByRoute[routeKey] || 'circle';
}

async function loadMenu() {
  try {
    const items = await authGet(CONFIG.ENDPOINTS.buttom, 'menu');

    if (!Array.isArray(items) || items.length === 0) return;

    var menuEl = document.getElementById('sidebar-menu');
    if (!menuEl) return;

    var currentPath = window.location.pathname;

    menuEl.innerHTML = items
      .sort(function (a, b) { return (a.orden || 0) - (b.orden || 0); })
      .map(function (item) {
        var rawRoute = normalizeRouteKey(item.nombre);
        var href = CONFIG.ROUTE_ALIASES[rawRoute] || ('/' + rawRoute);
        var icon = resolveMenuIcon(item.icon, rawRoute);
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
