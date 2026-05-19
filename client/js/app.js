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

/* ─────────────────────────────────────────────────────────────
   GLOBAL STATE
───────────────────────────────────────────────────────────── */
window.CUSPIDE_STATE = window.CUSPIDE_STATE || {
  instituciones: [],
  currentInstitucionId: parseInt(localStorage.getItem('currentInstitucionId') || '0', 10) || null,
};

var FALLBACK_LOGO_URL = '/assets/logo/huaquillasfc.jpg';

function setCurrentInstitucion(id) {
  window.CUSPIDE_STATE.currentInstitucionId = id;
  localStorage.setItem('currentInstitucionId', id);
}

function getCurrentInstitucionId() {
  return window.CUSPIDE_STATE.currentInstitucionId;
}

function checkAuth() {
  if (!localStorage.getItem(CONFIG.STORAGE_KEYS.token)) {
    window.location.href = CONFIG.ROUTES.login;
    return false;
  }

  return true;
}

function logout() {
  localStorage.removeItem(CONFIG.STORAGE_KEYS.token);
  window.location.href = CONFIG.ROUTES.login;
}

function escapeHtml(value) {
  return String(value == null ? '' : value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function setLogoImage(elementOrId, url) {
  var img = typeof elementOrId === 'string'
    ? document.getElementById(elementOrId)
    : elementOrId;

  if (!img || img.tagName !== 'IMG') return;

  var cleanUrl = String(url || '').trim();
  img.onerror = function () {
    if (img.src.indexOf(FALLBACK_LOGO_URL) === -1) {
      img.src = FALLBACK_LOGO_URL;
    }
  };
  img.src = cleanUrl || FALLBACK_LOGO_URL;
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
    const data = await Cuspide.api.institucion();

    if (!data || !data.nombre) return;

    var sidebarNombre = document.getElementById('sidebar-institucion-nombre');
    if (sidebarNombre) sidebarNombre.textContent = data.nombre;

    setLogoImage('sidebar-logo-img', data.logo);
    setLogoImage('topbar-institucion-logo', data.logo);

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

function resolveRouteAlias(value) {
  var key = normalizeRouteKey(value);
  if (!key) return null;

  if (CONFIG.ROUTE_ALIASES[key]) {
    return CONFIG.ROUTE_ALIASES[key];
  }

  var compact = key.replace(/-/g, '');
  if (CONFIG.ROUTE_ALIASES[compact]) {
    return CONFIG.ROUTE_ALIASES[compact];
  }

  if (key.indexOf('cuenta') !== -1 && key.indexOf('cobrar') !== -1) return '/cuentascobrar';
  if (key.indexOf('asistencia') !== -1 || key.indexOf('permiso') !== -1) return '/asistencias';
  if (key.indexOf('matric') !== -1) return '/matriculas';
  if (key.indexOf('membres') !== -1 || key.indexOf('socio') !== -1) return '/membresias';
  if (key.indexOf('persona') !== -1 || key.indexOf('perfil') !== -1) return '/personas';
  if (key.indexOf('alumno') !== -1 || key.indexOf('estudiante') !== -1) return '/alumnos';
  if (key.indexOf('asign') !== -1 || key.indexOf('clase') !== -1) return '/asignaturas';
  if (key.indexOf('activ') !== -1 || key.indexOf('deporte') !== -1 || key.indexOf('evento') !== -1) return '/actividades';
  if (key.indexOf('producto') !== -1 || key.indexOf('mercancia') !== -1 || key.indexOf('uniforme') !== -1 || key.indexOf('equipo') !== -1) return '/productos';
  if (key.indexOf('inventario') !== -1 || key.indexOf('stock') !== -1) return '/inventario';
  if (key.indexOf('venta') !== -1) return '/ventas';
  if (key.indexOf('factura') !== -1) return '/facturas';
  if (key.indexOf('pago') !== -1 || key.indexOf('cobro') !== -1) return '/pagos';
  if (key.indexOf('horario') !== -1 || key.indexOf('calendario') !== -1) return '/horarios';
  if (key.indexOf('instituc') !== -1) return '/instituciones';
  if (key.indexOf('usuario') !== -1 || key.indexOf('permiso') !== -1 || key.indexOf('boton') !== -1) return '/usuarios';
  if (key.indexOf('config') !== -1 || key.indexOf('ajuste') !== -1) return '/configuracion';
  if (key.indexOf('dashboard') !== -1 || key.indexOf('panel') !== -1 || key.indexOf('inicio') !== -1) return '/dashboard';
  if (key.indexOf('gasto') !== -1 || key.indexOf('egreso') !== -1) return '/gastos';
  if (key.indexOf('contrato') !== -1) return '/contratos';
  if (key.indexOf('empleado') !== -1) return '/empleados';
  if (key.indexOf('reporte') !== -1 || key.indexOf('informe') !== -1 || key.indexOf('estadistica') !== -1) return '/reportes';

  return null;
}

function resolveMenuHref(item) {
  var candidates = [
    item && item.ruta,
    item && item.path,
    item && item.href,
    item && item.tipo,
    item && item.nemonico,
    item && item.nombre,
  ];

  for (var i = 0; i < candidates.length; i++) {
    var candidate = String(candidates[i] || '').trim();
    if (!candidate) continue;

    if (candidate.charAt(0) === '/') {
      var direct = resolveRouteAlias(candidate) || candidate;
      return direct;
    }

    var alias = resolveRouteAlias(candidate);
    if (alias) return alias;
  }

  return '#';
}

/**
 * buildMenuIconHtml — usa SIEMPRE el icono que devuelve el backend
 * (campo `icon`/`icono` en la respuesta de /buttom?api=menu).
 * Acepta los formatos comunes guardados en la tabla `botones`:
 *   - "bi-house-fill"        → <i class="bi bi-house-fill"></i>
 *   - "bi bi-house-fill"     → <i class="bi bi-house-fill"></i>
 *   - "fa-home" / "fas fa-x" → <i class="fa fa-home"></i>
 *   - "house-fill"           → <i class="bi bi-house-fill"></i>  (default a Bootstrap Icons)
 * Si la BD no envía icono se cae a "bi-circle" para no romper la fila.
 */
function buildMenuIconHtml(rawIcon) {
  var icon = String(rawIcon == null ? '' : rawIcon).trim();

  if (!icon) {
    return '<i class="bi bi-circle me-2"></i>';
  }

  // Ya viene con clases compuestas ("bi bi-x", "fas fa-x", etc.)
  if (/\s/.test(icon)) {
    return '<i class="' + escapeHtml(icon) + ' me-2"></i>';
  }

  // Prefijo Bootstrap Icons
  if (icon.indexOf('bi-') === 0) {
    return '<i class="bi ' + escapeHtml(icon) + ' me-2"></i>';
  }

  // Prefijo Font Awesome
  if (icon.indexOf('fa-') === 0) {
    return '<i class="fa ' + escapeHtml(icon) + ' me-2"></i>';
  }

  // Nombre suelto → asumir Bootstrap Icons
  return '<i class="bi bi-' + escapeHtml(icon) + ' me-2"></i>';
}

function clearMenuLoadingState(menuEl) {
  if (!menuEl) return;
  menuEl.classList.remove('sidebar-menu-loading');
  menuEl.removeAttribute('aria-busy');
}

async function loadMenu() {
  var menuEl = document.getElementById('sidebar-menu');

  try {
    const items = await Cuspide.api.menu();

    if (!menuEl) return;

    if (!Array.isArray(items) || items.length === 0) {
      menuEl.innerHTML = '<li class="nav-item"><span class="nav-link text-muted small">Sin opciones disponibles</span></li>';
      clearMenuLoadingState(menuEl);
      return;
    }

    var currentPath = resolveRouteAlias(window.location.pathname) || window.location.pathname;

    var rendered = items
      .slice()
      .sort(function (a, b) { return (a.orden || 0) - (b.orden || 0); })
      .map(function (item) {
        var href = resolveMenuHref(item);
        var iconHtml = buildMenuIconHtml(item.icon || item.icono);
        var isActive = currentPath === href ? 'active' : '';
        var label = escapeHtml(item.nombre || 'Opción');

        return '<li class="nav-item">' +
          '<a class="nav-link ' + isActive + '" href="' + escapeHtml(href) + '" title="' + label + '">' +
          iconHtml + '<span class="nav-link-text">' + label + '</span>' +
          '</a></li>';
      })
      .join('');

    menuEl.innerHTML = rendered;
    clearMenuLoadingState(menuEl);

  } catch (err) {
    if (menuEl) {
      menuEl.innerHTML = '<li class="nav-item"><span class="nav-link text-muted small">No se pudo cargar el menú</span></li>';
      clearMenuLoadingState(menuEl);
    }
  }
}

/* ─────────────────────────────────────────────────────────────
   INICIALIZACIÓN
───────────────────────────────────────────────────────────── */

async function loadInstituciones() {
  try {
    var items = await Cuspide.api.call('GET', CONFIG.ENDPOINTS.instituciones, 'list');
    if (Array.isArray(items)) {
      window.CUSPIDE_STATE.instituciones = items;
      if (!window.CUSPIDE_STATE.currentInstitucionId && items.length > 0) {
        setCurrentInstitucion(items[0].id);
      }
    }
  } catch (err) {
    // silencioso
  }
}

document.addEventListener('DOMContentLoaded', function () {
  if (!checkAuth()) return;

  Promise.all([
    loadUserProfile(),
    loadInstitucion(),
    loadMenu(),
    loadInstituciones(),
  ]);
});
