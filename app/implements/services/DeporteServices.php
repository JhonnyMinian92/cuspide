<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/repository/DeporteRepository.php';
require_once __DIR__ . '/../persistence/entitys/Deporte.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\DeporteRepository;
use Entitys\Deporte;
use Utils\Token;

class DeporteServices
{
    private DeporteRepository $deporteRepo;
    private array $security;

    public function __construct()
    {
        $this->deporteRepo = new DeporteRepository();
        $this->security    = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        // Detect resource from ?api= param first, then URL path
        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'list':
                return $this->fetchDeportes();

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
    private function fetchDeportes(): array
    {
        $deportes = $this->deporteRepo->findAllActive();
        $result   = [];
        foreach ($deportes as $deporte) {
            $result[] = [
                'id'          => (int)$deporte->getId(),
                'nombre'      => $deporte->getNombre(),
                'descripcion' => $deporte->getDescripcion(),
                'estado'      => (int)$deporte->getEstado()?->getId(),
            ];
        }
        return $result;
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
