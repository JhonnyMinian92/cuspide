<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/ActividadRepository.php';
require_once __DIR__ . '/../persistence/repository/AsistenciaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/entitys/Actividad.php';
require_once __DIR__ . '/../../controllers/request/ActividadRequest.php';
require_once __DIR__ . '/../persistence/entitys/Asistencia.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use PDO;
use Repositories\ActividadRepository;
use Repositories\AsistenciaRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Entitys\Actividad;
use Entitys\Asistencia;
use Request\ActividadRequest;
use Utils\Token;

class ActividadServices
{
    private ActividadRepository $actividadRepo;
    private AsistenciaRepository $asistenciaRepo;
    private PersonaRepository $personaRepo;
    private array $security;
    private PDO $db;

    public function __construct()
    {
        $this->actividadRepo  = new ActividadRepository();
        $this->asistenciaRepo = new AsistenciaRepository();
        $this->personaRepo    = new PersonaRepository();
        $this->security       = require __DIR__ . '/../../properties/security.php';
        $this->db             = Database::getInstance();
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        $userId   = (int) ($payload['sub'] ?? 0);
        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'list':
                return $this->fetchActividades($userId);

            case $method === 'GET' && $resource === 'niveles':
                return $this->fetchNiveles($userId);

            case $method === 'GET' && $resource === 'capacity':
                return $this->fetchCapacity((int)($queryParams['id'] ?? 0));

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                return $this->saveActividad(isset($queryParams['id']) ? (int)$queryParams['id'] : null, $userId);

            case $method === 'PUT' && $resource === 'delete':
                return $this->deleteActividad((int)($queryParams['id'] ?? 0));

            case $method === 'POST' && $resource === 'attendance':
                return $this->registerAttendance();

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchActividades
    // -------------------------------------------------------------------------
    private function fetchActividades(int $userId): array
    {
        $institucionId = $this->getInstitucionByUserId($userId);
        $rows   = $this->actividadRepo->findByInstitucionWithStats($institucionId);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'                     => (int)$row['id'],
                'nombre'                 => $row['nombre'],
                'observacion'            => $row['observacion'],
                'categoria_deportiva_id' => isset($row['categoria_deportiva_id']) ? (int)$row['categoria_deportiva_id'] : null,
                'categoria_deportiva_nombre' => $row['categoria_deportiva_nombre'] ?? null,
                'deporte_nombre'         => $row['deporte_nombre'] ?? null,
                'nivel_id'               => isset($row['nivel_id']) ? (int)$row['nivel_id'] : null,
                'nivel_nombre'           => $row['nivel_nombre'] ?? null,
                'docente_id'             => isset($row['docente_id']) ? (int)$row['docente_id'] : null,
                'periodo_academico_id'   => isset($row['periodo_academico_id']) ? (int)$row['periodo_academico_id'] : null,
                'precio'                 => (float)$row['precio'],
                'cupo_maximo'            => (int)($row['cupo_maximo'] ?? 0),
                'estado'                 => (int)$row['estado'],
                'matriculados'           => (int)$row['matriculados'],
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // fetchCapacity
    // -------------------------------------------------------------------------
    private function fetchCapacity(int $actividadId): array
    {
        if ($actividadId <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $row = $this->actividadRepo->findCapacity($actividadId);
        if ($row === null) {
            return [
                'cupo_maximo' => null,
                'ocupados'    => null,
                'disponible'  => null,
            ];
        }

        return [
            'cupo_maximo' => (int)$row['cupo_maximo'],
            'ocupados'    => (int)$row['ocupados'],
            'disponible'  => (int)$row['disponible'],
        ];
    }

    // -------------------------------------------------------------------------
    // fetchNiveles
    // -------------------------------------------------------------------------
    private function fetchNiveles(int $userId): array
    {
        $institucionId = $this->getInstitucionByUserId($userId);
        $stmt = $this->db->prepare(
            "SELECT id, nombre FROM nivel_asignatura WHERE institucion = :inst AND estado = 1 ORDER BY nombre ASC"
        );
        $stmt->execute([':inst' => $institucionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // -------------------------------------------------------------------------
    // saveActividad
    // -------------------------------------------------------------------------
    private function saveActividad(?int $id, int $userId): array
    {
        $req      = ActividadRequest::fromJson();
        $entityId = ($id !== null && $id > 0) ? $id : null;
        $instId   = $this->getInstitucionByUserId($userId);

        if ($entityId !== null) {
            $existing = $this->actividadRepo->findById($entityId);
            if (!$existing) {
                throw new \RuntimeException('Actividad no encontrada para actualizar.', 404);
            }
            if ($existing->getInstitucion()?->getId() !== $instId) {
                throw new \RuntimeException('No tiene permisos para modificar esta actividad.', 403);
            }
        }

        $actividad = new Actividad(
            $entityId,
            $req->getCategoriaDeportiva(),
            $req->getPrecio(),
            $req->getNivel(),
            $req->getObservacion(),
            $instId,
            $req->getNombre(),
            $req->getEstado(),
            $req->getDocente(),
            $req->getCupoMaximo(),
            $req->getPeriodoAcademico()
        );
        $saved = $this->actividadRepo->save($actividad);

        return [
            'id'     => (int)$saved->getId(),
            'nombre' => $saved->getNombre(),
        ];
    }

    // -------------------------------------------------------------------------
    // deleteActividad
    // -------------------------------------------------------------------------
    private function deleteActividad(int $id): array
    {
        if ($id <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $existing = $this->actividadRepo->findById($id);
        if (!$existing) {
            throw new \RuntimeException('Actividad no encontrada.', 404);
        }

        $this->actividadRepo->disable($id);

        return ['id' => $id, 'estado' => 2];
    }

    // -------------------------------------------------------------------------
    // registerAttendance
    // -------------------------------------------------------------------------
    private function registerAttendance(): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['matricula', 'actividad', 'fecha', 'estado'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $matricula   = (int)$data['matricula'];
        $actividad   = (int)$data['actividad'];
        $fecha       = trim($data['fecha']);
        $horario     = isset($data['horario'])      && $data['horario']      !== '' ? (int)$data['horario']      : null;
        $horaEntrada = isset($data['hora_entrada'])  && $data['hora_entrada']  !== '' ? $data['hora_entrada']  : null;
        $horaSalida  = isset($data['hora_salida'])   && $data['hora_salida']   !== '' ? $data['hora_salida']   : null;
        $observacion = trim($data['observacion'] ?? '');
        $estado      = (int)$data['estado'];

        $asistencia = new Asistencia(
            null,
            $fecha,
            $matricula,
            null,
            $actividad,
            $observacion,
            $estado,
            null,
            $horario,
            null,
            $horaEntrada,
            $horaSalida
        );

        $saved = $this->asistenciaRepo->save($asistencia);

        return [
            'id'     => (int)$saved->getId(),
            'fecha'  => $saved->getFecha(),
            'estado' => (int)$saved->getEstado()?->getId(),
        ];
    }

    // -------------------------------------------------------------------------
    // getInstitucionByUserId
    // -------------------------------------------------------------------------
    private function getInstitucionByUserId(int $userId): int
    {
        $persona = $this->personaRepo->findByUserId($userId);
        if ($persona === null) {
            throw new \RuntimeException('No se encontró persona asociada al usuario.', 404);
        }
        $pi = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());
        if ($pi === null) {
            throw new \RuntimeException('No se encontró institución asociada al usuario.', 404);
        }
        return (int) $pi->getIdInstitucion()->getId();
    }
}
