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
      ajustes:       '/configuracion',
      perfil:        '/configuracion',

      persona:       '/personas',
      personas:      '/personas',

      matricula:     '/matriculas',
      matriculas:    '/matriculas',

      pago:          '/pagos',
      pagos:         '/pagos',
      cobros:        '/pagos',

      factura:       '/facturas',
      facturas:      '/facturas',

      cuentascobrar:        '/cuentascobrar',
      cuenta:               '/cuentascobrar',
      'cuentas-cobrar':     '/cuentascobrar',
      'cuentas-por-cobrar': '/cuentascobrar',
      'cuentas-x-cobrar':   '/cuentascobrar',
      cuentasxcobrar:       '/cuentascobrar',
      cxp:                  '/cuentascobrar',
      cxc:                  '/cuentascobrar',

      asignatura:    '/asignaturas',
      asignaturas:   '/asignaturas',
      clase:         '/asignaturas',
      clases:        '/asignaturas',

      actividad:     '/actividades',
      actividades:   '/actividades',
      deporte:       '/actividades',
      deportes:      '/actividades',
      evento:        '/actividades',
      eventos:       '/actividades',
      'eventos-deportivos': '/actividades',

      asistencia:    '/asistencias',
      asistencias:   '/asistencias',
      permiso:       '/asistencias',
      permisos:      '/asistencias',

      producto:      '/productos',
      productos:     '/productos',
      mercancia:     '/productos',
      mercancias:    '/productos',
      mercadería:    '/productos',
      uniformes:     '/productos',
      equipos:       '/productos',

      venta:         '/ventas',
      ventas:        '/ventas',

      horario:       '/horarios',
      horarios:      '/horarios',

      institucion:   '/instituciones',
      instituciones: '/instituciones',

      usuario:       '/usuarios',
      usuarios:      '/usuarios',

      gasto:         '/gastos',
      gastos:        '/gastos',
      egreso:        '/gastos',
      egresos:       '/gastos',

      contrato:      '/contratos',
      contratos:     '/contratos',
      empleado:      '/empleados',
      empleados:     '/empleados',

      reporte:       '/reportes',
      reportes:      '/reportes',
      informe:       '/reportes',
      informes:      '/reportes',
      estadistica:   '/reportes',
      estadisticas:  '/reportes',
    }),
    STORAGE_KEYS: Object.freeze({
      token: 'token',
      theme: 'theme',
    }),
    ENDPOINTS: Object.freeze({
      auth:          '/app/controllers/api/auth/',
      users:         '/app/controllers/api/users/',
      company:       '/app/controllers/api/company/',
      buttom:        '/app/controllers/api/buttom/',
      personas:      '/app/controllers/api/personas/',
      instituciones: '/app/controllers/api/instituciones/',
      matriculas:    '/app/controllers/api/matriculas/',
      membresias:    '/app/controllers/api/membresias/',
      pagos:         '/app/controllers/api/pagos/',
      facturas:      '/app/controllers/api/facturas/',
      cuentascobrar: '/app/controllers/api/cuentascobrar/',
      asignaturas:   '/app/controllers/api/asignaturas/',
      clases:        '/app/controllers/api/clases/',
      actividades:   '/app/controllers/api/actividades/',
      deportes:      '/app/controllers/api/deportes/',
      horarios:      '/app/controllers/api/horarios/',
      productos:     '/app/controllers/api/productos/',
      inventario:    '/app/controllers/api/inventario/',
      ventas:        '/app/controllers/api/ventas/',
      usuarios:      '/app/controllers/api/usuarios/',
      dashboard:     '/app/controllers/api/dashboard/',
      gastos:        '/app/controllers/api/gastos/',
      contratos:     '/app/controllers/api/contratos/',
      periodos:      '/app/controllers/api/periodos/',
      permisos:      '/app/controllers/api/permisos/',
    }),
  });
})(window);
