<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/PermisoServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/PermisoRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/entitys/Permiso.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\PermisoServiceInterface;
use Repositories\PermisoRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Entitys\Permiso;
use RuntimeException;
use Utils\Token;

final class PermisoServices implements PermisoServiceInterface
{
    private string $jwtSecret;
    private PermisoRepository $permisoRepo;
    private PersonaRepository $personaRepo;
    private PersonaInstitucionRepository $piRepo;

    public function __construct()
    {
        $config = require __DIR__ . '/../../properties/security.php';
        $this->jwtSecret   = (string) ($config['jwt_secret'] ?? '');
        if ($this->jwtSecret === '') {
            throw new RuntimeException('No existe configuración de seguridad para JWT.', 500);
        }
        $this->permisoRepo = new PermisoRepository();
        $this->personaRepo = new PersonaRepository();
        $this->piRepo      = new PersonaInstitucionRepository();
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->jwtSecret);
        $userId  = (int) ($payload['sub'] ?? 0);
        $instId  = $this->getInstitucionByUserId($userId);

        $resource = strtolower(trim((string) ($queryParams['api'] ?? '')));

        return match (true) {
            $method === 'GET'  && $resource === 'list'       => $this->fetchList($instId, $queryParams),
            $method === 'GET'  && $resource === 'bypersona'  => $this->fetchByPersona($queryParams),
            $method === 'POST' && $resource === 'save'       => $this->savePermiso($instId, $userId, $queryParams),
            $method === 'POST' && $resource === 'cancel'     => $this->cancelPermiso($queryParams),
            default => throw new RuntimeException('Ruta no soportada.', 404),
        };
    }

    private function fetchList(int $instId, array $queryParams): array
    {
        $rows = $this->permisoRepo->findByInstitucion($instId, $queryParams['desde'] ?? null);

        return array_map(fn(array $r) => [
            'id'             => (int) $r['id'],
            'persona'        => (int) $r['persona'],
            'identificacion' => $r['identificacion']  ?? null,
            'primer_nombre'  => $r['primer_nombre']   ?? null,
            'primer_apellido'=> $r['primer_apellido']  ?? null,
            'matricula'      => isset($r['matricula'])  ? (int) $r['matricula']  : null,
            'clase'          => isset($r['clase'])      ? (int) $r['clase']      : null,
            'actividad'      => isset($r['actividad'])  ? (int) $r['actividad']  : null,
            'fecha_inicio'   => $r['fecha_inicio'],
            'fecha_fin'      => $r['fecha_fin'],
            'horas_por_dia'  => isset($r['horas_por_dia']) ? (float) $r['horas_por_dia'] : null,
            'motivo'         => $r['motivo'],
            'observacion'    => $r['observacion']     ?? null,
            'estado'         => (int) $r['estado'],
            'fecha_registro' => $r['fecha_registro']  ?? null,
        ], $rows);
    }

    private function fetchByPersona(array $queryParams): array
    {
        $personaId = (int) ($queryParams['persona_id'] ?? 0);
        if ($personaId <= 0) {
            throw new RuntimeException('El parámetro persona_id es requerido.', 400);
        }

        $entities = $this->permisoRepo->findByPersonaId($personaId);

        return array_map(fn(Permiso $p) => [
            'id'             => $p->getId(),
            'persona'        => $p->getPersona(),
            'matricula'      => $p->getMatricula(),
            'clase'          => $p->getClase(),
            'actividad'      => $p->getActividad(),
            'fecha_inicio'   => $p->getFechaInicio(),
            'fecha_fin'      => $p->getFechaFin(),
            'horas_por_dia'  => $p->getHorasPorDia(),
            'motivo'         => $p->getMotivo(),
            'observacion'    => $p->getObservacion(),
            'estado'         => $p->getEstado(),
            'fecha_registro' => $p->getFechaRegistro(),
        ], $entities);
    }

    private function savePermiso(int $instId, int $userId, array $queryParams): array
    {
        $body = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($body)) {
            throw new RuntimeException('Cuerpo de la solicitud inválido o vacío.', 400);
        }

        $persona     = (int) ($body['persona']     ?? 0);
        $fechaInicio = (string) ($body['fecha_inicio'] ?? '');
        $fechaFin    = (string) ($body['fecha_fin']    ?? '');
        $motivo      = (string) ($body['motivo']       ?? '');

        if ($persona <= 0 || $fechaInicio === '' || $fechaFin === '' || $motivo === '') {
            throw new RuntimeException('Faltan campos obligatorios: persona, fecha_inicio, fecha_fin, motivo.', 400);
        }

        $matricula   = isset($body['matricula'])    && $body['matricula']    !== '' ? (int) $body['matricula']           : null;
        $clase       = isset($body['clase'])        && $body['clase']        !== '' ? (int) $body['clase']               : null;
        $actividad   = isset($body['actividad'])    && $body['actividad']    !== '' ? (int) $body['actividad']           : null;
        $horasPorDia = isset($body['horas_por_dia']) && $body['horas_por_dia'] !== '' ? (float) $body['horas_por_dia']  : null;
        $observacion = isset($body['observacion'])  && $body['observacion']  !== '' ? (string) $body['observacion']     : null;

        $entity = new Permiso(
            null,
            $persona,
            $matricula,
            $clase,
            $actividad,
            $fechaInicio,
            $fechaFin,
            $horasPorDia,
            $motivo,
            $observacion,
            1
        );

        $saved = $this->permisoRepo->save($entity);

        return [
            'id'             => $saved->getId(),
            'persona'        => $saved->getPersona(),
            'matricula'      => $saved->getMatricula(),
            'clase'          => $saved->getClase(),
            'actividad'      => $saved->getActividad(),
            'fecha_inicio'   => $saved->getFechaInicio(),
            'fecha_fin'      => $saved->getFechaFin(),
            'horas_por_dia'  => $saved->getHorasPorDia(),
            'motivo'         => $saved->getMotivo(),
            'observacion'    => $saved->getObservacion(),
            'estado'         => $saved->getEstado(),
            'fecha_registro' => $saved->getFechaRegistro(),
        ];
    }

    private function cancelPermiso(array $queryParams): array
    {
        $id = (int) ($queryParams['id'] ?? 0);
        if ($id <= 0) {
            throw new RuntimeException('El parámetro id es requerido.', 400);
        }

        $permiso = $this->permisoRepo->findById($id);
        if ($permiso === null) {
            throw new RuntimeException('Permiso no encontrado.', 404);
        }

        $db   = \Core\Database::getInstance();
        $stmt = $db->prepare('UPDATE permisos SET estado = 0 WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return ['id' => $id, 'estado' => 0];
    }

    private function getInstitucionByUserId(int $userId): int
    {
        $persona = $this->personaRepo->findByUserId($userId);
        if ($persona === null) {
            throw new RuntimeException('No se encontró persona asociada al usuario.', 404);
        }
        $pi = $this->piRepo->findByPersonaId((int) $persona->getId());
        if ($pi === null) {
            throw new RuntimeException('No se encontró institución asociada al usuario.', 404);
        }
        return (int) $pi->getIdInstitucion()->getId();
    }
}
