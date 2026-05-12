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
    '/socios'       => 'pages/membresias.html.twig',
    '/socio'        => 'pages/membresias.html.twig',
    '/inventario'   => 'pages/inventario.html.twig',
    '/categorias'   => 'pages/categorias.html.twig',
    '/categoria'    => 'pages/categorias.html.twig',
    '/categorías'   => 'pages/categorias.html.twig',
    '/categoría'    => 'pages/categorias.html.twig',
    '/configuracion'=> 'pages/configuracion.html.twig',
    '/configuración'=> 'pages/configuracion.html.twig',
    '/ajustes'      => 'pages/configuracion.html.twig',
    '/perfil'       => 'pages/configuracion.html.twig',
];

if (isset($routes[$uri])) {
    echo $twig->render($routes[$uri]);
} else {
    http_response_code(404);
    echo $twig->render('pages/404.html.twig');
}
