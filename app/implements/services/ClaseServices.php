<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/repository/ClaseRepository.php';
require_once __DIR__ . '/../persistence/repository/AsistenciaRepository.php';
require_once __DIR__ . '/../persistence/repository/NivelAsignaturaRepository.php';
require_once __DIR__ . '/../persistence/entitys/Clase.php';
require_once __DIR__ . '/../persistence/entitys/Asistencia.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\ClaseRepository;
use Repositories\AsistenciaRepository;
use Repositories\NivelAsignaturaRepository;
use Entitys\Clase;
use Entitys\Asistencia;
use Utils\Token;

class ClaseServices
{
    private ClaseRepository $claseRepo;
    private AsistenciaRepository $asistenciaRepo;
    private NivelAsignaturaRepository $nivelRepo;
    private array $security;

    public function __construct()
    {
        $this->claseRepo      = new ClaseRepository();
        $this->asistenciaRepo = new AsistenciaRepository();
        $this->nivelRepo      = new NivelAsignaturaRepository();
        $this->security       = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET'  && $resource === 'list':
                return $this->fetchClases($queryParams);

            case $method === 'GET'  && $resource === 'capacity':
                return $this->fetchCapacity((int)($queryParams['id'] ?? 0));

            case $method === 'GET'  && $resource === 'docentes':
                return $this->fetchDocentes((int)($queryParams['institucion'] ?? 0));

            case $method === 'GET'  && $resource === 'niveles':
                return $this->fetchNiveles();

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                return $this->saveClase(isset($queryParams['id']) ? (int)$queryParams['id'] : null);

            case $method === 'PUT'  && $resource === 'delete':
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

        $periodoId = isset($queryParams['periodo']) && $queryParams['periodo'] !== '' ? (int)$queryParams['periodo'] : null;
        $rows      = $this->claseRepo->findByInstitucionWithStats($institucionId, $periodoId);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'                => (int)$row['id'],
                'nombre'            => $row['nombre'],
                'cupo_maximo'       => (int)$row['cupo_maximo'],
                'estado'            => (int)$row['estado'],
                'periodo_academico' => $row['periodo_academico'] !== null ? (int)$row['periodo_academico'] : null,
                'periodo'           => $row['periodo']           ?? null,
                'asignatura'        => $row['asignatura']        !== null ? (int)$row['asignatura'] : null,
                'asignatura_nombre' => $row['asignatura_nombre'] ?? null,
                'nivel_asignatura'  => $row['nivel_asignatura']  !== null ? (int)$row['nivel_asignatura'] : null,
                'nivel_nombre'      => $row['nivel_nombre']      ?? null,
                'precio'            => $row['precio']            !== null ? (float)$row['precio'] : null,
                'observacion'       => $row['observacion']       ?? null,
                'docente'           => $row['docente']           !== null ? (int)$row['docente'] : null,
                'primer_nombre'     => $row['primer_nombre']     ?? null,
                'primer_apellido'   => $row['primer_apellido']   ?? null,
                'matriculados'      => (int)$row['matriculados'],
                'asistencias'       => (int)$row['asistencias'],
                'faltas'            => (int)$row['faltas'],
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

        $row = $this->claseRepo->findCapacity($claseId);
        if ($row === null) {
            throw new \RuntimeException('Clase no encontrada.', 404);
        }

        return [
            'cupo_maximo' => (int)$row['cupo_maximo'],
            'ocupados'    => (int)$row['ocupados'],
            'disponible'  => (int)$row['disponible'],
        ];
    }

    // -------------------------------------------------------------------------
    // fetchDocentes
    // -------------------------------------------------------------------------
    private function fetchDocentes(int $institucionId): array
    {
        if ($institucionId <= 0) {
            throw new \RuntimeException('El parámetro institucion es requerido.', 400);
        }

        $rows = $this->claseRepo->findDocentesByInstitucion($institucionId);

        return array_map(fn($r) => [
            'id'             => (int)$r['id'],
            'nombre_completo'=> trim($r['nombre_completo']),
            'primer_nombre'  => $r['primer_nombre']  ?? '',
            'primer_apellido'=> $r['primer_apellido'] ?? '',
            'tipo'           => $r['tipo']            ?? '',
        ], $rows);
    }

    // -------------------------------------------------------------------------
    // fetchNiveles
    // -------------------------------------------------------------------------
    private function fetchNiveles(): array
    {
        $entities = $this->nivelRepo->findAll();
        return array_map(fn($n) => [
            'id'       => $n->getId(),
            'nombre'   => $n->getNombre(),
            'nemonico' => $n->getNemonico(),
        ], $entities);
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
        $docente          = isset($data['docente'])          && $data['docente']          !== '' ? (int)$data['docente']          : null;
        $asignatura       = isset($data['asignatura'])       && $data['asignatura']       !== '' ? (int)$data['asignatura']       : null;
        $nivelAsignatura  = isset($data['nivel_asignatura']) && $data['nivel_asignatura'] !== '' ? (int)$data['nivel_asignatura'] : null;
        $precio           = isset($data['precio'])           && $data['precio']           !== '' ? (float)$data['precio']         : null;
        $observacion      = isset($data['observacion'])      && $data['observacion']      !== '' ? trim($data['observacion'])     : null;
        $cupoMaximo       = (int)$data['cupo_maximo'];
        $estado           = isset($data['estado']) ? (int)$data['estado'] : 1;

        $entityId = ($id !== null && $id > 0) ? $id : (isset($data['id']) && (int)$data['id'] > 0 ? (int)$data['id'] : null);

        if ($entityId !== null) {
            $existing = $this->claseRepo->findById($entityId);
            if (!$existing) {
                throw new \RuntimeException('Clase no encontrada para actualizar.', 404);
            }
        }

        $clase = new Clase($entityId, $nombre, $periodoAcademico, $institucion, $docente, $cupoMaximo, $estado, $asignatura, $nivelAsignatura, $precio, $observacion);
        $saved = $this->claseRepo->save($clase);

        return [
            'id'               => (int)$saved->getId(),
            'nombre'           => $saved->getNombre(),
            'cupo_maximo'      => $saved->getCupoMaximo(),
            'estado'           => (int)$saved->getEstado()?->getId(),
            'asignatura'       => $saved->getAsignatura(),
            'nivel_asignatura' => $saved->getNivelAsignatura(),
            'precio'           => $saved->getPrecio(),
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

        $this->claseRepo->disable($id);

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
        $horario     = isset($data['horario'])     && $data['horario']     !== '' ? (int)$data['horario']      : null;
        $horaEntrada = isset($data['hora_entrada']) && $data['hora_entrada'] !== '' ? $data['hora_entrada']     : null;
        $horaSalida  = isset($data['hora_salida'])  && $data['hora_salida']  !== '' ? $data['hora_salida']      : null;
        $observacion = trim($data['observacion'] ?? '');
        $estado      = (int)$data['estado'];

        $asistencia = new Asistencia(
            null, $fecha, $matricula, $clase, null, $observacion, $estado,
            null, $horario, null, $horaEntrada, $horaSalida
        );

        $saved = $this->asistenciaRepo->save($asistencia);

        return [
            'id'     => (int)$saved->getId(),
            'fecha'  => $saved->getFecha(),
            'estado' => (int)$saved->getEstado()?->getId(),
        ];
    }
}
