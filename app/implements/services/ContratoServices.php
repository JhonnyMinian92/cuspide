<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/ContratoServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/ContratoRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/entitys/Contrato.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\ContratoServiceInterface;
use Repositories\ContratoRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Entitys\Contrato;
use RuntimeException;
use Utils\Token;

final class ContratoServices implements ContratoServiceInterface
{
    private string $jwtSecret;

    public function __construct()
    {
        $config = require __DIR__ . '/../../properties/security.php';
        $this->jwtSecret = (string) ($config['jwt_secret'] ?? '');
        if ($this->jwtSecret === '') {
            throw new RuntimeException('No existe configuración de seguridad para JWT.', 500);
        }
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $userId = (int) (Token::validateBearerToken($authHeader, $this->jwtSecret)['sub'] ?? 0);
        $instId = $this->getInstitucionByUserId($userId);

        $resource = strtolower(trim((string) ($queryParams['api'] ?? '')));

        return match (true) {
            $method === 'GET'                        && $resource === 'list'   => $this->fetchContratos($instId, $queryParams),
            $method === 'GET'                        && $resource === 'byPersona' => $this->fetchByPersona((int) ($queryParams['persona_id'] ?? 0)),
            in_array($method, ['POST', 'PUT'], true) && $resource === 'save'   => $this->saveContrato(isset($queryParams['id']) ? (int) $queryParams['id'] : null, $instId),
            default => throw new RuntimeException('Ruta de API no soportada: ' . $method . ' ' . $resource, 404),
        };
    }

    private function fetchContratos(int $instId, array $queryParams): array
    {
        $repo  = new ContratoRepository();
        $limit = (int) ($queryParams['limit'] ?? 50);
        $rows  = $repo->findByInstitucion($instId, $limit);

        return array_map(fn($r) => [
            'id'                => (int) $r['id'],
            'fecha_inscripcion' => $r['fecha_inscripcion'],
            'fecha_inicio'      => $r['fecha_inicio'],
            'fecha_fin'         => $r['fecha_fin'],
            'nivel'             => (int) $r['nivel'],
            'nivel_nombre'      => $r['nivel_nombre'] ?? null,
            'persona'           => (int) $r['persona'],
            'identificacion'    => $r['identificacion'] ?? null,
            'nombre_completo'   => trim($r['nombre_completo'] ?? ''),
            'tipo_persona'      => $r['tipo_persona'] ?? null,
        ], $rows);
    }

    private function fetchByPersona(int $personaId): array
    {
        if ($personaId <= 0) {
            throw new RuntimeException('ID de persona inválido.', 400);
        }
        $repo = new ContratoRepository();
        $rows = $repo->findByPersona($personaId);
        return array_map(fn($r) => [
            'id'                => (int) $r['id'],
            'fecha_inscripcion' => $r['fecha_inscripcion'],
            'fecha_inicio'      => $r['fecha_inicio'],
            'fecha_fin'         => $r['fecha_fin'],
            'nivel'             => (int) $r['nivel'],
            'nivel_nombre'      => $r['nivel_nombre'] ?? null,
            'institucion_nombre' => $r['institucion_nombre'] ?? null,
        ], $rows);
    }

    private function saveContrato(?int $id, int $instId): array
    {
        $body = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($body)) {
            throw new RuntimeException('Cuerpo de la solicitud inválido o vacío.', 400);
        }

        $fechaInscripcion = (string) ($body['fecha_inscripcion'] ?? '');
        $fechaInicio      = (string) ($body['fecha_inicio']      ?? '');
        $fechaFin         = (string) ($body['fecha_fin']         ?? '');
        $nivel            = (int)    ($body['nivel']             ?? 0);
        $personaId        = (int)    ($body['persona_id']        ?? 0);

        if ($fechaInicio === '' || $fechaFin === '' || $personaId <= 0) {
            throw new RuntimeException('Faltan campos obligatorios: fecha_inicio, fecha_fin, persona_id.', 400);
        }

        if ($fechaInscripcion === '') {
            $fechaInscripcion = date('Y-m-d');
        }

        $repo   = new ContratoRepository();
        $entity = new Contrato($id ?: null, $fechaInscripcion, $fechaInicio, $fechaFin, $nivel, $instId, $personaId);
        $saved  = $repo->save($entity);

        return [
            'id'                => $saved->getId(),
            'fecha_inscripcion' => $saved->getFechaInscripcion(),
            'fecha_inicio'      => $saved->getFechaInicio(),
            'fecha_fin'         => $saved->getFechaFin(),
            'nivel'             => $saved->getNivel()?->getId() ?? $nivel,
            'persona'           => $saved->getPersona()?->getId() ?? $personaId,
        ];
    }

    private function getInstitucionByUserId(int $userId): int
    {
        $persona = (new PersonaRepository())->findByUserId($userId);
        if ($persona === null) {
            throw new RuntimeException('No se encontró persona asociada al usuario.', 404);
        }
        $pi = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());
        if ($pi === null) {
            throw new RuntimeException('No se encontró institución asociada al usuario.', 404);
        }
        return (int) $pi->getIdInstitucion()->getId();
    }
}
