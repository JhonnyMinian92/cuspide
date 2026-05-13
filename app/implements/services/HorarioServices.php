<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/repository/HorarioRepository.php';
require_once __DIR__ . '/../persistence/entitys/Horario.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\HorarioRepository;
use Entitys\Horario;
use Utils\Token;

class HorarioServices
{
    private HorarioRepository $horarioRepo;
    private array $security;

    /** Day-of-week number to Spanish name mapping */
    private const DIA_NOMBRES = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
        7 => 'Domingo',
    ];

    public function __construct()
    {
        $this->horarioRepo = new HorarioRepository();
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
                return $this->fetchHorarios($queryParams);

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                return $this->saveHorario(isset($queryParams['id']) ? (int)$queryParams['id'] : null);

            case $method === 'PUT' && $resource === 'delete':
                return $this->deleteHorario((int)($queryParams['id'] ?? 0));

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchHorarios
    // -------------------------------------------------------------------------
    private function fetchHorarios(array $queryParams): array
    {
        $claseId      = isset($queryParams['clase'])      ? (int)$queryParams['clase']      : 0;
        $actividadId  = isset($queryParams['actividad'])  ? (int)$queryParams['actividad']  : 0;
        $institucionId = isset($queryParams['institucion']) ? (int)$queryParams['institucion'] : 0;

        if ($claseId > 0) {
            $horarios = $this->horarioRepo->findByClase($claseId);
        } elseif ($actividadId > 0) {
            $horarios = $this->horarioRepo->findByActividad($actividadId);
        } elseif ($institucionId > 0) {
            $horarios = $this->horarioRepo->findByInstitucion($institucionId);
        } else {
            throw new \RuntimeException('Se requiere el parámetro clase, actividad o institucion.', 400);
        }

        $result = [];
        foreach ($horarios as $h) {
            $dia = $h->getDiaSemana();
            $result[] = [
                'id'           => (int)$h->getId(),
                'nombre'       => $h->getNombre(),
                'dia_semana'   => $dia,
                'dia_nombre'   => self::DIA_NOMBRES[$dia] ?? (string)$dia,
                'hora_inicio'  => $h->getHoraInicio(),
                'hora_fin'     => $h->getHoraFin(),
                'fecha_inicio' => $h->getFechaInicio(),
                'fecha_fin'    => $h->getFechaFin(),
                'cupo_maximo'  => $h->getCupoMaximo(),
                'ubicacion'    => $h->getUbicacion(),
                'estado'       => (int)$h->getEstado()?->getId(),
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // saveHorario
    // -------------------------------------------------------------------------
    private function saveHorario(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['nombre', 'institucion', 'dia_semana', 'hora_inicio', 'hora_fin', 'cupo_maximo', 'ubicacion'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $nombre      = trim($data['nombre']);
        $institucion = (int)$data['institucion'];
        $clase       = isset($data['clase'])      && $data['clase']      !== '' ? (int)$data['clase']      : null;
        $actividad   = isset($data['actividad'])  && $data['actividad']  !== '' ? (int)$data['actividad']  : null;
        $catDep      = isset($data['categoria_deportiva']) && $data['categoria_deportiva'] !== '' ? (int)$data['categoria_deportiva'] : null;
        $entrenador  = isset($data['entrenador']) && $data['entrenador'] !== '' ? (int)$data['entrenador'] : null;
        $diaSemana   = (int)$data['dia_semana'];
        $horaInicio  = $data['hora_inicio'];
        $horaFin     = $data['hora_fin'];
        $fechaInicio = isset($data['fecha_inicio']) && $data['fecha_inicio'] !== '' ? $data['fecha_inicio'] : null;
        $fechaFin    = isset($data['fecha_fin'])    && $data['fecha_fin']    !== '' ? $data['fecha_fin']    : null;
        $cupoMaximo  = (int)$data['cupo_maximo'];
        $ubicacion   = trim($data['ubicacion']);
        $estado      = isset($data['estado']) ? (int)$data['estado'] : 1;

        // Validate dia_semana range
        if ($diaSemana < 1 || $diaSemana > 7) {
            throw new \RuntimeException('El campo dia_semana debe ser un valor entre 1 (Lunes) y 7 (Domingo).', 400);
        }

        // Resolve id: prefer URL param, then body
        $entityId = ($id !== null && $id > 0) ? $id : (isset($data['id']) && (int)$data['id'] > 0 ? (int)$data['id'] : null);

        if ($entityId !== null) {
            $existing = $this->horarioRepo->findById($entityId);
            if (!$existing) {
                throw new \RuntimeException('Horario no encontrado para actualizar.', 404);
            }
        }

        $horario = new Horario(
            $entityId,
            $nombre,
            $institucion,
            $clase,
            $actividad,
            $catDep,
            $entrenador,
            $diaSemana,
            $horaInicio,
            $horaFin,
            $fechaInicio,
            $fechaFin,
            $cupoMaximo,
            $ubicacion,
            $estado
        );

        $saved = $this->horarioRepo->save($horario);

        return [
            'id'          => (int)$saved->getId(),
            'nombre'      => $saved->getNombre(),
            'dia_semana'  => $saved->getDiaSemana(),
            'dia_nombre'  => self::DIA_NOMBRES[$saved->getDiaSemana()] ?? (string)$saved->getDiaSemana(),
            'hora_inicio' => $saved->getHoraInicio(),
            'hora_fin'    => $saved->getHoraFin(),
            'cupo_maximo' => $saved->getCupoMaximo(),
            'ubicacion'   => $saved->getUbicacion(),
            'estado'      => (int)$saved->getEstado()?->getId(),
        ];
    }

    // -------------------------------------------------------------------------
    // deleteHorario
    // -------------------------------------------------------------------------
    private function deleteHorario(int $id): array
    {
        if ($id <= 0) {
            throw new \RuntimeException('El parámetro id es requerido.', 400);
        }

        $horario = $this->horarioRepo->findById($id);
        if (!$horario) {
            throw new \RuntimeException('Horario no encontrado.', 404);
        }

        $horario->setEstado(2);
        $this->horarioRepo->save($horario);

        return ['id' => $id, 'estado' => 2];
    }
}
