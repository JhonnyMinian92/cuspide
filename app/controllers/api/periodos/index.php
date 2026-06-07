<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../implements/persistence/conexion/Database.php';
require_once __DIR__ . '/../../../implements/persistence/repository/PeriodoAcademicoRepository.php';
require_once __DIR__ . '/../../../implements/persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../../../implements/persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../../../implements/utils/Token.php';

use Core\Database;
use Repositories\PeriodoAcademicoRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Utils\Token;

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!in_array($method, ['GET', 'POST', 'PUT'], true)) {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($auth === '' && function_exists('getallheaders')) {
        $headers = getallheaders();
        $auth    = (string) ($headers['Authorization'] ?? $headers['authorization'] ?? '');
    }

    $config    = require __DIR__ . '/../../../properties/security.php';
    $jwtSecret = (string) ($config['jwt_secret'] ?? '');
    $payload   = Token::validateBearerToken($auth, $jwtSecret);
    $userId    = (int) ($payload['sub'] ?? 0);

    $persona = (new PersonaRepository())->findByUserId($userId);
    if ($persona === null) throw new \RuntimeException('Persona no encontrada.', 404);
    $pi = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());
    if ($pi === null) throw new \RuntimeException('Institución no encontrada.', 404);
    $instId = (int) $pi->getIdInstitucion()->getId();

    $api  = strtolower(trim($_GET['api'] ?? ''));
    $repo = new PeriodoAcademicoRepository();

    if ($method === 'GET' && $api === 'list') {
        $items = $repo->findByInstitucion($instId);
        $data  = array_map(fn($p) => [
            'id'           => $p->getId(),
            'nombre'       => $p->getNombre(),
            'fecha_inicio' => $p->getFechaInicio(),
            'fecha_fin'    => $p->getFechaFin(),
            'estado'       => $p->getEstado()?->getId() ?? 1,
        ], $items);
        echo json_encode(['status' => 'success', 'data' => $data, 'message' => 'OK.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'GET' && $api === 'current') {
        $p    = $repo->findActualByInstitucion($instId);
        $data = $p ? [
            'id'           => $p->getId(),
            'nombre'       => $p->getNombre(),
            'fecha_inicio' => $p->getFechaInicio(),
            'fecha_fin'    => $p->getFechaFin(),
        ] : null;
        echo json_encode(['status' => 'success', 'data' => $data, 'message' => 'OK.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (in_array($method, ['POST', 'PUT'], true) && $api === 'save') {
        $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $id   = isset($body['id']) ? (int) $body['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : null);
        $nombre     = (string) ($body['nombre'] ?? '');
        $fechaIni   = (string) ($body['fecha_inicio'] ?? '');
        $fechaFin   = (string) ($body['fecha_fin']    ?? '');
        if ($nombre === '' || $fechaIni === '' || $fechaFin === '') {
            throw new \RuntimeException('Faltan campos: nombre, fecha_inicio, fecha_fin.', 400);
        }
        $entity = new \Entitys\PeriodoAcademico($id, $nombre, $fechaIni, $fechaFin, $instId, 1);
        $saved  = $repo->save($entity);
        echo json_encode(['status' => 'success', 'data' => ['id' => $saved->getId()], 'message' => 'OK.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(404);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Ruta no soportada.'], JSON_UNESCAPED_UNICODE);

} catch (\RuntimeException $e) {
    $code = in_array($e->getCode(), [400, 401, 403, 404, 405, 500], true) ? $e->getCode() : 400;
    http_response_code($code);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'data' => null, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
