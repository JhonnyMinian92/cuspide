<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/ActividadRepository.php';
require_once __DIR__ . '/../persistence/repository/AsistenciaRepository.php';
require_once __DIR__ . '/../persistence/entitys/Actividad.php';
require_once __DIR__ . '/../persistence/entitys/Asistencia.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Repositories\ActividadRepository;
use Repositories\AsistenciaRepository;
use Entitys\Actividad;
use Entitys\Asistencia;
use Utils\Token;

class ActividadServices
{
    private ActividadRepository $actividadRepo;
    private AsistenciaRepository $asistenciaRepo;
    private array $security;

    public function __construct()
    {
        $this->actividadRepo  = new ActividadRepository();
        $this->asistenciaRepo = new AsistenciaRepository();
        $this->security       = require __DIR__ . '/../../properties/security.php';
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
                return $this->fetchActividades($queryParams);

            case $method === 'GET' && $resource === 'capacity':
                return $this->fetchCapacity((int)($queryParams['id'] ?? 0));

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                return $this->saveActividad(isset($queryParams['id']) ? (int)$queryParams['id'] : null);

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
    private function fetchActividades(array $queryParams): array
    {
        $institucionId = (int)($queryParams['institucion'] ?? 0);
        if ($institucionId <= 0) {
            throw new \RuntimeException('El parámetro institucion es requerido.', 400);
        }

        $db  = Database::getInstance();
        $sql = "SELECT ac.id,
                       ac.nombre,
                       d.nombre                                                AS deporte,
                       n.nombre                                                AS nivel,
                       ac.precio,
                       ac.estado,
                       COUNT(DISTINCT am.id)                                   AS matriculados,
                       SUM(CASE WHEN asis.estado = 1 THEN 1 ELSE 0 END)       AS asistencias,
                       SUM(CASE WHEN asis.estado = 2 THEN 1 ELSE 0 END)       AS faltas
                FROM actividades ac
                JOIN deportes d              ON d.id    = ac.deporte
                LEFT JOIN nivel n ON n.id    = ac.nivel
                LEFT JOIN actividades_matricula am  ON am.actividad = ac.id AND am.estado = 1
                LEFT JOIN asistencias asis        ON asis.actividad = ac.id
                WHERE ac.institucion = :inst
                GROUP BY ac.id
                ORDER BY ac.nombre ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':inst', $institucionId, \PDO::PARAM_INT);
        $stmt->execute();

        $rows   = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'           => (int)$row['id'],
                'nombre'       => $row['nombre'],
                'deporte'      => $row['deporte'],
                'nivel'        => $row['nivel']   ?? null,
                'precio'       => (float)$row['precio'],
                'estado'       => (int)$row['estado'],
                'matriculados' => (int)$row['matriculados'],
                'asistencias'  => (int)$row['asistencias'],
                'faltas'       => (int)$row['faltas'],
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

        $db  = Database::getInstance();
        $sql = "SELECT h.cupo_maximo,
                       COUNT(am.id)                        AS ocupados,
                       (h.cupo_maximo - COUNT(am.id))      AS disponible
                FROM horarios h
                LEFT JOIN actividades_matricula am ON am.actividad = h.actividad AND am.estado = 1
                WHERE h.actividad = :id AND h.estado = 1
                GROUP BY h.id
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $actividadId, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
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
    // saveActividad
    // -------------------------------------------------------------------------
    private function saveActividad(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['nombre', 'deporte', 'precio', 'institucion'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $nombre      = trim($data['nombre']);
        $deporte     = (int)$data['deporte'];
        $precio      = (float)$data['precio'];
        $nivel       = isset($data['nivel']) && $data['nivel'] !== '' ? (int)$data['nivel'] : null;
        $observacion = trim($data['observacion'] ?? '');
        $institucion = (int)$data['institucion'];
        $estado      = isset($data['estado']) ? (int)$data['estado'] : 1;

        // Resolve id: prefer URL param, then body
        $entityId = ($id !== null && $id > 0) ? $id : (isset($data['id']) && (int)$data['id'] > 0 ? (int)$data['id'] : null);

        if ($entityId !== null) {
            $existing = $this->actividadRepo->findById($entityId);
            if (!$existing) {
                throw new \RuntimeException('Actividad no encontrada para actualizar.', 404);
            }
        }

        $actividad = new Actividad($entityId, $deporte, $precio, $nivel, $observacion, $institucion, $nombre, $estado);
        $saved     = $this->actividadRepo->save($actividad);

        return [
            'id'     => (int)$saved->getId(),
            'nombre' => $saved->getNombre(),
            'precio' => $saved->getPrecio(),
            'estado' => (int)$saved->getEstado()?->getId(),
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

        $db   = Database::getInstance();
        $stmt = $db->prepare('UPDATE actividades SET estado = 2 WHERE id = :id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

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
        $estado      = (int)$data['estado']; // 1=Presente, 2=Ausente, 3=Justificado

        $asistencia = new Asistencia(
            null,
            $fecha,
            $matricula,
            null,       // clase
            $actividad,
            $observacion,
            $estado,
            null,       // calendario
            $horario,
            null,       // plan_entrenamiento_sesion
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
}
