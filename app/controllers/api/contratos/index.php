<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../../implements/services/ContratoServices.php';

$method = $_SERVER['REQUEST_METHOD'];
$queryParams = $_GET;
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

try {
    $service = new \Services\ContratoServices();
    $result = $service->processRequest($method, $queryParams, $authHeader);
    http_response_code(200);
    echo json_encode($result);
} catch (\RuntimeException $e) {
    http_response_code($e->getCode() > 0 ? $e->getCode() : 400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ocurrió un error inesperado en el servidor.']);
}
