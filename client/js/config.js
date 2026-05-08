(function (window) {
  'use strict';

  const sameOrigin = window.location.origin || `${window.location.protocol}//${window.location.host}`;
  const runtimeConfig = window.CUSPIDE_CONFIG || {};
  const apiBaseUrl = (runtimeConfig.API_BASE_URL || sameOrigin).replace(/\/+$/, '');

  window.CONFIG = Object.freeze({
    APP_NAME: 'Huaquillas F.C.',
    API_BASE_URL: apiBaseUrl,
    ROUTES: Object.freeze({
      login: '/',
      dashboard: '/dashboard',
    }),
    ROUTE_ALIASES: Object.freeze({
      dashboard: '/dashboard',
      panel: '/dashboard',
      inicio: '/dashboard',
      alumno: '/alumnos',
      alumnos: '/alumnos',
      estudiante: '/alumnos',
      estudiantes: '/alumnos',
      membresia: '/membresias',
      membresias: '/membresias',
      socio: '/membresias',
      socios: '/membresias',
      inventario: '/inventario',
      item: '/inventario',
      items: '/inventario',
      categoria: '/categorias',
      categorias: '/categorias',
      configuracion: '/configuracion',
      ajustes: '/configuracion',
      perfil: '/configuracion',
    }),
    STORAGE_KEYS: Object.freeze({
      token: 'token',
      theme: 'theme',
    }),
    ENDPOINTS: Object.freeze({
      auth:       '/app/controllers/api/auth/index.php',
      users:      '/app/controllers/api/users/index.php',
      company:    '/app/controllers/api/company/index.php',
      buttom:     '/app/controllers/api/buttom/index.php',
    }),
  });
})(window);
