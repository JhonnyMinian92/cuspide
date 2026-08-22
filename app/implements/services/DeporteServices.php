<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/DeporteRepository.php';
require_once __DIR__ . '/../persistence/entitys/Deporte.php';
require_once __DIR__ . '/../utils/Token.php';
require_once __DIR__ . '/traits/TAuthenticable.php';

use Core\Database;
use Repositories\DeporteRepository;
use Entitys\Deporte;
use Utils\Token;
use Services\Traits\TAuthenticable;

class DeporteServices
{
    use TAuthenticable;

    private DeporteRepository $deporteRepo;
    private array $security;
    private \PDO $db;

    public function __construct()
    {
        $this->db          = Database::getInstance();
        $this->deporteRepo = new DeporteRepository();
        $this->security    = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        $userId = (int) ($payload['sub'] ?? 0);

        // Detect resource from ?api= param first, then URL path
        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'list':
                return $this->fetchDeportes($queryParams, $userId);

            case $method === 'POST' && $resource === 'save':
                return $this->saveDeporte(null);

            case $method === 'PUT' && $resource === 'save':
                return $this->saveDeporte(isset($queryParams['id']) ? (int)$queryParams['id'] : null);

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchDeportes
    // -------------------------------------------------------------------------
    private function fetchDeportes(array $queryParams, int $userId): array
    {
        // Prioriza la institución del parámetro, si no, usa la del usuario logueado.
        $instId = !empty($queryParams['institucion'])
            ? (int)$queryParams['institucion']
            : $this->getInstitucionByUserId($userId);

        $stmt = $this->db->prepare(
            "SELECT id, nombre, descripcion, estado
             FROM deportes
             WHERE institucion = :inst AND estado = 1"
        );
        $stmt->execute([':inst' => $instId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => [
            'id'          => (int)$row['id'],
            'nombre'      => $row['nombre'],
            'descripcion' => $row['descripcion'],
            'estado'      => (int)$row['estado'],
        ], $rows ?: []);
    }

    // -------------------------------------------------------------------------
    // saveDeporte
    // -------------------------------------------------------------------------
    private function saveDeporte(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        if (empty($data['nombre'])) {
            throw new \RuntimeException('El campo nombre es requerido.', 400);
        }

        // Resolve id: prefer URL param, then body
        $entityId = ($id !== null && $id > 0) ? $id : (isset($data['id']) && (int)$data['id'] > 0 ? (int)$data['id'] : null);

        $nombre      = trim($data['nombre']);
        $descripcion = trim($data['descripcion'] ?? '');
        $estado      = isset($data['estado']) ? (int)$data['estado'] : 1;
        $institucion = isset($data['institucion']) ? (int)$data['institucion'] : 0;

        if ($entityId !== null) {
            $existing = $this->deporteRepo->findById($entityId);
            if (!$existing) {
                throw new \RuntimeException('Deporte no encontrado para actualizar.', 404);
            }
        }

        $deporte = new Deporte($entityId, $nombre, $descripcion, $institucion, $estado);
        $saved   = $this->deporteRepo->save($deporte);

        return [
            'id'     => (int)$saved->getId(),
            'nombre' => $saved->getNombre(),
        ];
    }
}