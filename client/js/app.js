const FALLBACK_LOGO_URL = '/assets/logo/huaquillasfc.jpg';

/**
 * app.js - comportamiento global del panel.
 *
 * Este archivo coordina UI compartida. Las llamadas HTTP viven en api.js para
 * que las paginas no dupliquen rutas, headers ni manejo de token.
 */

function checkAuth() {
  return window.Cuspide.auth.requireAuth();
}

function logout() {
  window.Cuspide.auth.logout();
}

function setText(id, value) {
  const element = document.getElementById(id);
  if (element) {
    element.textContent = value;
  }
}

function safeInitial(value) {
  return value && value.length > 0 ? value.charAt(0).toUpperCase() : 'U';
}

function normalizeRouteKey(value) {
  return String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

function routeFromAlias(value) {
  const key = normalizeRouteKey(value);
  if (CONFIG.ROUTE_ALIASES[key]) {
    return CONFIG.ROUTE_ALIASES[key];
  }

  if (key.includes('alumno') || key.includes('estudiante')) return '/alumnos';
  if (key.includes('membres') || key.includes('socio')) return '/membresias';
  if (key.includes('inventario') || key.includes('item')) return '/inventario';
  if (key.includes('categoria')) return '/categorias';
  if (key.includes('config') || key.includes('ajuste') || key.includes('perfil')) return '/configuracion';
  if (key.includes('dashboard') || key.includes('panel') || key.includes('inicio')) return '/dashboard';

  return null;
}

function menuHref(item) {
  const candidates = [
    item.href,
    item.path,
    item.ruta,
    item.tipo,
    item.nemonico,
    item.nombre,
  ];

  for (const candidate of candidates) {
    const raw = String(candidate || '').trim();
    if (!raw) continue;

    if (raw.charAt(0) === '/') {
      return routeFromAlias(raw.slice(1)) || raw;
    }

    const route = routeFromAlias(raw);
    if (route) {
      return route;
    }
  }

  return '#';
}

function iconName(icon) {
  const fallback = 'circle';
  const value = String(icon || fallback).trim().toLowerCase().replace(/^bi-/, '');
  return /^[a-z0-9-]+$/.test(value) ? value : fallback;
}

function assetUrl(value) {
  const raw = String(value || '').trim();
  if (!raw) return FALLBACK_LOGO_URL;
  if (/^data:image\//i.test(raw)) return raw;
  if (/^(https?:)?\/\//i.test(raw) || raw.charAt(0) === '/') return raw;
  if (/^[\w.-]+\.(png|jpe?g|webp|gif|svg)$/i.test(raw)) return '/assets/logo/' + raw;
  if (/\.(png|jpe?g|webp|gif|svg)(\?.*)?$/i.test(raw)) return '/' + raw.replace(/^\/+/, '');
  return FALLBACK_LOGO_URL;
}

function setLogoImage(image, candidateUrl, altText) {
  if (!image) return;

  const nextUrl = assetUrl(candidateUrl);
  image.alt = altText || 'Huaquillas F.C.';
  image.onerror = function () {
    image.onerror = null;
    image.src = FALLBACK_LOGO_URL;
  };

  if (nextUrl === FALLBACK_LOGO_URL) {
    image.src = FALLBACK_LOGO_URL;
    return;
  }

  const probe = new Image();
  probe.onload = function () {
    image.src = nextUrl;
  };
  probe.onerror = function () {
    image.src = FALLBACK_LOGO_URL;
  };
  probe.src = nextUrl;
}

function renderMenu(items) {
  const menuEl = document.getElementById('sidebar-menu');
  if (!menuEl || !Array.isArray(items) || items.length === 0) return;

  const currentPath = routeFromAlias(window.location.pathname.slice(1)) || window.location.pathname;
  menuEl.replaceChildren();

  items
    .slice()
    .sort(function (a, b) {
      return Number(a.orden || 0) - Number(b.orden || 0);
    })
    .forEach(function (item) {
      const href = menuHref(item);
      const link = document.createElement('a');
      link.className = 'nav-link' + (currentPath === href ? ' active' : '');
      link.href = href;

      const icon = document.createElement('i');
      icon.className = 'bi bi-' + iconName(item.icon) + ' me-2';

      link.appendChild(icon);
      link.append(document.createTextNode(String(item.nombre || 'Sin nombre')));

      const li = document.createElement('li');
      li.className = 'nav-item';
      li.appendChild(link);
      menuEl.appendChild(li);
    });
}

async function loadUserProfile() {
  try {
    const data = await window.Cuspide.api.profile();
    if (!data || !data.primer_nombre) return;

    const fullName = [data.primer_nombre, data.primer_apellido].filter(Boolean).join(' ').trim();
    const initial = safeInitial(data.primer_nombre);

    setText('sidebar-username', fullName);
    setText('sidebar-avatar', initial);
    setText('topbar-username', fullName);
    setText('topbar-avatar', initial);
  } catch (error) {
    // api.js redirige si el token ya no es valido.
  }
}

async function loadInstitucion() {
  try {
    const data = await window.Cuspide.api.institucion();
    if (!data || !data.nombre) return;

    setText('sidebar-institucion-nombre', data.nombre);

    const sidebarLogo = document.getElementById('sidebar-logo-img');
    const topbarLogo = document.getElementById('topbar-institucion-logo');

    setLogoImage(sidebarLogo, data.logo, data.nombre);
    setLogoImage(topbarLogo, data.logo, data.nombre);

    const badge = document.getElementById('topbar-institucion-badge');
    if (badge) {
      badge.textContent = data.nombre;
      badge.style.display = '';
    }
  } catch (error) {
    // La UI mantiene los valores de respaldo renderizados por Twig.
  }
}

async function loadMenu() {
  try {
    renderMenu(await window.Cuspide.api.menu());
  } catch (error) {
    // Si falla, queda el menu estatico del partial Twig.
  }
}

document.addEventListener('DOMContentLoaded', function () {
  if (!checkAuth()) return;

  Promise.all([
    loadUserProfile(),
    loadInstitucion(),
    loadMenu(),
  ]);
});
