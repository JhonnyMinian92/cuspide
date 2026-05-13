<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/ClaseRepository.php';
require_once __DIR__ . '/../persistence/repository/AsistenciaRepository.php';
require_once __DIR__ . '/../persistence/entitys/Clase.php';
require_once __DIR__ . '/../persistence/entitys/Asistencia.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Repositories\ClaseRepository;
use Repositories\AsistenciaRepository;
use Entitys\Clase;
use Entitys\Asistencia;
use Utils\Token;

class ClaseServices
{
    private ClaseRepository $claseRepo;
    private AsistenciaRepository $asistenciaRepo;
    private array $security;

    public function __construct()
    {
        $this->claseRepo      = new ClaseRepository();
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
                return $this->fetchClases($queryParams);

            case $method === 'GET' && $resource === 'capacity':
                return $this->fetchCapacity((int)($queryParams['id'] ?? 0));

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                return $this->saveClase(isset($queryParams['id']) ? (int)$queryParams['id'] : null);

            case $method === 'PUT' && $resource === 'delete':
                return $this->deleteClase((int)($queryParams['id'] ?? 0));

            case $method === 'POST' && $resource === 'attendance':
                return $this->registerAttendance();

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchClases
    // -------------------------------------------------------------------------
    private function fetchClases(array $queryParams): array
    {
        $institucionId = (int)($queryParams['institucion'] ?? 0);
        if ($institucionId <= 0) {
            throw new \RuntimeException('El parámetro institucion es requerido.', 400);
        }

        $db  = Database::getInstance();
        $sql = "SELECT c.id,
                       c.nombre,
                       c.cupo_maximo,
                       c.estado,
                       pa.nombre                                           AS periodo,
                       p.primer_nombre,
                       p.primer_apellido,
                       COUNT(DISTINCT cm.id)                              AS matriculados,
                       SUM(CASE WHEN a.estado = 1 THEN 1 ELSE 0 END)     AS asistencias,
                       SUM(CASE WHEN a.estado = 2 THEN 1 ELSE 0 END)     AS faltas
                FROM clases c
                LEFT JOIN periodo_academico pa ON pa.id = c.periodo_academico
                LEFT JOIN personas p           ON p.id  = c.docente
                LEFT JOIN clase_matricula cm   ON cm.clase = c.id AND cm.estado = 1
                LEFT JOIN asistencias a        ON a.clase  = c.id
                WHERE c.institucion = :inst
                GROUP BY c.id
                ORDER BY c.nombre ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':inst', $institucionId, \PDO::PARAM_INT);
        $stmt->execute();

        $rows   = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'              => (int)$row['id'],
                'nombre'          => $row['nombre'],
                'cupo_maximo'     => (int)$row['cupo_maximo'],
                'estado'          => (int)$row['estado'],
                'periodo'         => $row['periodo']         ?? null,
                'primer_nombre'   => $row['primer_nombre']   ?? null,
                'primer_apellido' => $row['primer_apellido'] ?? null,
                'matriculados'    => (int)$row['matriculados'],
                'asistencias'     => (int)$row['asistencias'],
                'faltas'          => (int)$row['faltas'],
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // fetchCapacity
    // -------------------------------------------------------------------------
    private function fetchCapacity(int $claseId): array
    {
        if ($claseId <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $db  = Database::getInstance();
        $sql = "SELECT c.cupo_maximo,
                       COUNT(cm.id)                          AS ocupados,
                       (c.cupo_maximo - COUNT(cm.id))        AS disponible
                FROM clases c
                LEFT JOIN clase_matricula cm ON cm.clase = c.id AND cm.estado = 1
                WHERE c.id = :id
                GROUP BY c.id";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $claseId, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \RuntimeException('Clase no encontrada.', 404);
        }

        return [
            'cupo_maximo' => (int)$row['cupo_maximo'],
            'ocupados'    => (int)$row['ocupados'],
            'disponible'  => (int)$row['disponible'],
        ];
    }

    // -------------------------------------------------------------------------
    // saveClase
    // -------------------------------------------------------------------------
    private function saveClase(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['nombre', 'periodo_academico', 'institucion', 'cupo_maximo'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $nombre           = trim($data['nombre']);
        $periodoAcademico = (int)$data['periodo_academico'];
        $institucion      = (int)$data['institucion'];
        $docente          = isset($data['docente']) && $data['docente'] !== '' ? (int)$data['docente'] : null;
        $cupoMaximo       = (int)$data['cupo_maximo'];
        $estado           = isset($data['estado']) ? (int)$data['estado'] : 1;

        // Resolve id: prefer URL param, then body
        $entityId = ($id !== null && $id > 0) ? $id : (isset($data['id']) && (int)$data['id'] > 0 ? (int)$data['id'] : null);

        if ($entityId !== null) {
            $existing = $this->claseRepo->findById($entityId);
            if (!$existing) {
                throw new \RuntimeException('Clase no encontrada para actualizar.', 404);
            }
        }

        $clase = new Clase($entityId, $nombre, $periodoAcademico, $institucion, $docente, $cupoMaximo, $estado);
        $saved = $this->claseRepo->save($clase);

        return [
            'id'               => (int)$saved->getId(),
            'nombre'           => $saved->getNombre(),
            'cupo_maximo'      => $saved->getCupoMaximo(),
            'estado'           => (int)$saved->getEstado()?->getId(),
        ];
    }

    // -------------------------------------------------------------------------
    // deleteClase
    // -------------------------------------------------------------------------
    private function deleteClase(int $id): array
    {
        if ($id <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $existing = $this->claseRepo->findById($id);
        if (!$existing) {
            throw new \RuntimeException('Clase no encontrada.', 404);
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare('UPDATE clases SET estado = 2 WHERE id = :id');
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

        foreach (['matricula', 'clase', 'fecha', 'estado'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $matricula   = (int)$data['matricula'];
        $clase       = (int)$data['clase'];
        $fecha       = trim($data['fecha']);
        $horario     = isset($data['horario']) && $data['horario'] !== '' ? (int)$data['horario'] : null;
        $horaEntrada = isset($data['hora_entrada']) && $data['hora_entrada'] !== '' ? $data['hora_entrada'] : null;
        $horaSalida  = isset($data['hora_salida'])  && $data['hora_salida']  !== '' ? $data['hora_salida']  : null;
        $observacion = trim($data['observacion'] ?? '');
        $estado      = (int)$data['estado']; // 1=Presente, 2=Ausente, 3=Justificado

        $asistencia = new Asistencia(
            null,
            $fecha,
            $matricula,
            $clase,
            null,       // actividad
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
