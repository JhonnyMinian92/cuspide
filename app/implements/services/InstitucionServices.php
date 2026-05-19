<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/InstitucionServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/InstitucionRepository.php';
require_once __DIR__ . '/../persistence/entitys/Institucion.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\InstitucionRepository;
use Entitys\Institucion;
use Utils\Token;
use Interfaces\InstitucionServiceInterface;

class InstitucionServices implements InstitucionServiceInterface
{
    private InstitucionRepository $institucionRepo;
    private array $security;

    public function __construct()
    {
        $this->institucionRepo = new InstitucionRepository();
        $this->security        = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        // Detect resource
        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'data':
                return $this->fetchData((int)($queryParams['id'] ?? 0));

            case $method === 'GET' && $resource === 'list':
                return $this->fetchAll();

            case $method === 'POST':
                return $this->saveInstitucion(null);

            case $method === 'PUT':
                return $this->saveInstitucion((int)($queryParams['id'] ?? 0));

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchData
    // -------------------------------------------------------------------------
    private function fetchData(int $id): array
    {
        if ($id <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $inst = $this->institucionRepo->findById($id);
        if (!$inst) {
            throw new \RuntimeException('Institución no encontrada.', 404);
        }

        return [
            'id'           => (int)$inst->getId(),
            'ruc'          => $inst->getRuc(),
            'razon_social' => $inst->getRazonSocial(),
            'nombre'       => $inst->getNombre(),
            'logotipo'     => $inst->getLogotipo(),
            'estado'       => (int)$inst->getEstado()?->getId(),
        ];
    }

    // -------------------------------------------------------------------------
    // fetchAll
    // -------------------------------------------------------------------------
    private function fetchAll(): array
    {
        $instituciones = $this->institucionRepo->findAll();
        $result        = [];
        foreach ($instituciones as $inst) {
            $result[] = [
                'id'           => (int)$inst->getId(),
                'ruc'          => $inst->getRuc(),
                'razon_social' => $inst->getRazonSocial(),
                'nombre'       => $inst->getNombre(),
                'logotipo'     => $inst->getLogotipo(),
                'estado'       => (int)$inst->getEstado()?->getId(),
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // saveInstitucion
    // -------------------------------------------------------------------------
    private function saveInstitucion(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['razon_social', 'nombre'] as $field) {
            if (empty($data[$field])) {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $entityId    = ($id !== null && $id > 0) ? $id : null;
        $ruc         = $data['ruc']          ?? null;
        $razonSocial = trim($data['razon_social']);
        $nombre      = trim($data['nombre']);
        $logotipo    = $data['logotipo']     ?? null;
        $estado      = isset($data['estado']) ? (int)$data['estado'] : 1;

        // If updating, load existing to merge
        if ($entityId !== null) {
            $existing = $this->institucionRepo->findById($entityId);
            if (!$existing) {
                throw new \RuntimeException('Institución no encontrada para actualizar.', 404);
            }
            $ruc      = $ruc      ?? $existing->getRuc();
            $logotipo = $logotipo ?? $existing->getLogotipo();
        }

        $inst  = new Institucion($entityId, $ruc, $razonSocial, $nombre, $logotipo, $estado);
        $saved = $this->institucionRepo->save($inst);

        return [
            'id'           => (int)$saved->getId(),
            'ruc'          => $saved->getRuc(),
            'razon_social' => $saved->getRazonSocial(),
            'nombre'       => $saved->getNombre(),
            'logotipo'     => $saved->getLogotipo(),
            'estado'       => (int)$saved->getEstado()?->getId(),
        ];
    }
}
