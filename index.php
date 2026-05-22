<?php

require_once __DIR__ . '/vendor/autoload.php';

// ── Twig setup ──────────────────────────────────────────────────────────────
$cacheDir = __DIR__ . '/var/cache/twig';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/client/presentation');
$twig   = new \Twig\Environment($loader, [
    'cache' => $cacheDir,
    'auto_reload' => true,
]);

// ── Routing ─────────────────────────────────────────────────────────────────
// Strip query string, decode URL chars and normalize leading slash.
$uri = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$uri = '/' . trim(rawurldecode($uri), '/');

$routes = [
    '/'             => 'login.html.twig',
    '/dashboard'    => 'pages/dashboard.html.twig',
    '/panel'        => 'pages/dashboard.html.twig',
    '/inicio'       => 'pages/dashboard.html.twig',
    '/alumnos'      => 'pages/alumnos.html.twig',
    '/alumno'       => 'pages/alumnos.html.twig',
    '/estudiantes'  => 'pages/alumnos.html.twig',
    '/estudiante'   => 'pages/alumnos.html.twig',
    '/membresias'   => 'pages/membresias.html.twig',
    '/membresia'    => 'pages/membresias.html.twig',
    '/membresías'   => 'pages/membresias.html.twig',
    '/membresía'    => 'pages/membresias.html.twig',
    '/socios'       => 'pages/socios.html.twig',
    '/socio'        => 'pages/socios.html.twig',
    '/inventario'   => 'pages/inventario.html.twig',
    '/categorias'   => 'pages/categorias.html.twig',
    '/categoria'    => 'pages/categorias.html.twig',
    '/categorías'   => 'pages/categorias.html.twig',
    '/categoría'    => 'pages/categorias.html.twig',
    '/configuracion'   => 'pages/configuracion.html.twig',
    '/configuración'   => 'pages/configuracion.html.twig',
    '/ajustes'         => 'pages/configuracion.html.twig',
    '/perfil'          => 'pages/configuracion.html.twig',

    '/personas'        => 'pages/personas.html.twig',
    '/persona'         => 'pages/personas.html.twig',

    '/matriculas'      => 'pages/matriculas.html.twig',
    '/matricula'       => 'pages/matriculas.html.twig',
    '/matrículas'      => 'pages/matriculas.html.twig',
    '/matrícula'       => 'pages/matriculas.html.twig',

    '/pagos'           => 'pages/pagos.html.twig',
    '/pago'            => 'pages/pagos.html.twig',
    '/cobros'          => 'pages/pagos.html.twig',

    '/facturas'        => 'pages/facturas.html.twig',
    '/factura'         => 'pages/facturas.html.twig',

    '/cuentascobrar'   => 'pages/cuentascobrar.html.twig',
    '/cuentas-cobrar'  => 'pages/cuentascobrar.html.twig',
    '/cuentas-por-cobrar' => 'pages/cuentascobrar.html.twig',
    '/cuentas-x-cobrar'   => 'pages/cuentascobrar.html.twig',
    '/cuentasxcobrar'     => 'pages/cuentascobrar.html.twig',
    '/cuenta-cobrar'      => 'pages/cuentascobrar.html.twig',
    '/cuenta-por-cobrar'  => 'pages/cuentascobrar.html.twig',

    '/asignaturas'     => 'pages/asignaturas.html.twig',
    '/asignatura'      => 'pages/asignaturas.html.twig',
    '/clases'          => 'pages/asignaturas.html.twig',
    '/clase'           => 'pages/asignaturas.html.twig',

    '/actividades'     => 'pages/actividades.html.twig',
    '/actividad'       => 'pages/actividades.html.twig',
    '/deportes'        => 'pages/actividades.html.twig',
    '/deporte'         => 'pages/actividades.html.twig',
    '/eventos'         => 'pages/actividades.html.twig',
    '/eventos-deportivos' => 'pages/actividades.html.twig',

    '/asistencias'     => 'pages/asistencias.html.twig',
    '/asistencia'      => 'pages/asistencias.html.twig',
    '/permisos'        => 'pages/asistencias.html.twig',
    '/permiso'         => 'pages/asistencias.html.twig',

    '/horarios'        => 'pages/horarios.html.twig',
    '/horario'         => 'pages/horarios.html.twig',

    '/productos'       => 'pages/productos.html.twig',
    '/producto'        => 'pages/productos.html.twig',
    '/mercancia'       => 'pages/productos.html.twig',
    '/mercancía'       => 'pages/productos.html.twig',
    '/mercancias'      => 'pages/productos.html.twig',
    '/mercancías'      => 'pages/productos.html.twig',
    '/uniformes'       => 'pages/productos.html.twig',
    '/equipos'         => 'pages/productos.html.twig',

    '/ventas'          => 'pages/ventas.html.twig',
    '/venta'           => 'pages/ventas.html.twig',

    '/instituciones'   => 'pages/instituciones.html.twig',
    '/institucion'     => 'pages/instituciones.html.twig',
    '/institución'     => 'pages/instituciones.html.twig',

    '/usuarios'        => 'pages/usuarios.html.twig',
    '/usuario'         => 'pages/usuarios.html.twig',

    '/gastos'          => 'pages/gastos.html.twig',
    '/gasto'           => 'pages/gastos.html.twig',
    '/egresos'         => 'pages/gastos.html.twig',
    '/egreso'          => 'pages/gastos.html.twig',

    '/contratos'       => 'pages/contratos.html.twig',
    '/contrato'        => 'pages/contratos.html.twig',
    '/empleados'       => 'pages/personas.html.twig',
    '/empleado'        => 'pages/personas.html.twig',

    '/reportes'        => 'pages/reportes.html.twig',
    '/reporte'         => 'pages/reportes.html.twig',
    '/informes'        => 'pages/reportes.html.twig',
    '/estadisticas'    => 'pages/reportes.html.twig',
];

if (isset($routes[$uri])) {
    echo $twig->render($routes[$uri]);
} else {
    http_response_code(404);
    echo $twig->render('pages/404.html.twig');
}
