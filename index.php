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
// Strip query string and leading slash from the URI
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$uri = '/' . trim($uri, '/');

$routes = [
    '/'             => 'login.html.twig',
    '/dashboard'    => 'pages/dashboard.html.twig',
    '/alumnos'      => 'pages/alumnos.html.twig',
    '/membresias'   => 'pages/membresias.html.twig',
    '/inventario'   => 'pages/inventario.html.twig',
    '/categorias'   => 'pages/categorias.html.twig',
    '/configuracion'=> 'pages/configuracion.html.twig',
];

if (isset($routes[$uri])) {
    echo $twig->render($routes[$uri]);
} else {
    http_response_code(404);
    echo $twig->render('pages/404.html.twig');
}
