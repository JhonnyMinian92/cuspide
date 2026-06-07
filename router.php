<?php
if (php_sapi_name() === 'cli-server') {
    $path = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // 1. Si es un directorio → buscar index.php dentro (soporta trailing slash)
    if (is_dir($path)) {
        $indexPath = rtrim($path, '/') . '/index.php';
        if (is_file($indexPath)) {
            require $indexPath;
            return;
        }
    }

    // 2. Archivos estáticos (css, js, imágenes) → servir directo
    if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
        return false;
    }

    // 3. Archivos PHP de la API que existen físicamente → ejecutarlos
    if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
        require $path;
        return;
    }
}

// 4. Todo lo demás (/, /dashboard, etc.) → router Twig
require __DIR__ . '/index.php';