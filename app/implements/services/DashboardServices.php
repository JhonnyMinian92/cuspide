<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use RuntimeException;
use Utils\Token;
use PDO;

final class DashboardServices
{
    private string $jwtSecret;
    private PDO $db;

    public function __construct()
    {
        $config = require __DIR__ . '/../../properties/security.php';
        $this->jwtSecret = (string) ($config['jwt_secret'] ?? '');
        if ($this->jwtSecret === '') {
            throw new RuntimeException('No existe configuración de seguridad para JWT.', 500);
        }
        $this->db = Database::getInstance();
    }

    public function processRequest(string $method, string $_requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->jwtSecret);
        $userId  = (int) ($payload['sub'] ?? 0);

        $resource = strtolower(trim((string) ($queryParams['api'] ?? '')));

        return match (true) {
            $method === 'GET' && $resource === 'stats'                 => $this->fetchStats($userId),
            $method === 'GET' && $resource === 'vencidos'              => $this->fetchVencidos($userId),
            $method === 'GET' && $resource === 'chart_ingresos_gastos' => $this->fetchChartIngresosGastos($userId),
            $method === 'GET' && $resource === 'chart_membresias'      => $this->fetchChartMembresias($userId),
            $method === 'GET' && $resource === 'ultimas_matriculas'    => $this->fetchUltimasMatriculas($userId, $queryParams),
            default => throw new RuntimeException('Ruta de API no soportada: ' . $method . ' ' . $resource, 404),
        };
    }

    // ── stats: KPIs del mes de la institución ────────────────────────────────

    private function fetchStats(int $userId): array
    {
        $instId = $this->getInstitucionByUserId($userId);

        // Total alumnos activos (persona_institucion tipo=3 es alumno/estudiante)
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM persona_institucion
             WHERE id_institucion = :inst AND tipo = 3 AND estado = 1"
        );
        $stmt->execute([':inst' => $instId]);
        $totalAlumnos = (int) $stmt->fetchColumn();

        // Matrículas registradas este mes
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM matricula
             WHERE institucion = :inst AND estado = 1
               AND YEAR(fecha_registro) = YEAR(CURDATE())
               AND MONTH(fecha_registro) = MONTH(CURDATE())"
        );
        $stmt->execute([':inst' => $instId]);
        $matriculasMes = (int) $stmt->fetchColumn();

        // Ingresos del mes: suma de pagos cobrados (estado=1)
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM pagos
             WHERE institucion = :inst AND estado = 1
               AND YEAR(fecha) = YEAR(CURDATE())
               AND MONTH(fecha) = MONTH(CURDATE())"
        );
        $stmt->execute([':inst' => $instId]);
        $ingresosMes = (float) $stmt->fetchColumn();

        // Gastos del mes
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM gastos
             WHERE institucion = :inst AND estado = 1
               AND YEAR(fecha) = YEAR(CURDATE())
               AND MONTH(fecha) = MONTH(CURDATE())"
        );
        $stmt->execute([':inst' => $instId]);
        $gastosMes = (float) $stmt->fetchColumn();

        // Cantidad de ventas con saldo pendiente creadas este mes
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM ventas
             WHERE institucion = :inst AND saldo > 0
               AND YEAR(fecha) = YEAR(CURDATE())
               AND MONTH(fecha) = MONTH(CURDATE())"
        );
        $stmt->execute([':inst' => $instId]);
        $pagosPendientesMes = (int) $stmt->fetchColumn();

        return [
            'totalAlumnos'       => $totalAlumnos,
            'matriculasMes'      => $matriculasMes,
            'ingresosMes'        => $ingresosMes,
            'gastosMes'          => $gastosMes,
            'balanceMes'         => round($ingresosMes - $gastosMes, 2),
            'pagosPendientesMes' => $pagosPendientesMes,
        ];
    }

    // ── vencidos: lista completa de ventas con saldo pendiente ───────────────

    private function fetchVencidos(int $userId): array
    {
        $instId = $this->getInstitucionByUserId($userId);

        $stmt = $this->db->prepare(
            "SELECT v.id, v.numero, v.fecha, v.total, v.saldo, v.tipo,
                    p.identificacion,
                    TRIM(CONCAT(
                        COALESCE(p.primer_nombre,''),' ',
                        COALESCE(p.segundo_nombre,''),' ',
                        COALESCE(p.primer_apellido,''),' ',
                        COALESCE(p.segundo_apellido,'')
                    )) AS nombre
             FROM ventas v
             JOIN personas p ON p.id = v.persona
             WHERE v.institucion = :inst AND v.saldo > 0
             ORDER BY v.fecha ASC
             LIMIT 50"
        );
        $stmt->execute([':inst' => $instId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(fn($r) => [
            'id'             => (int)   $r['id'],
            'numero'         => $r['numero'],
            'fecha'          => $r['fecha'],
            'total'          => (float) $r['total'],
            'saldo'          => (float) $r['saldo'],
            'tipo'           => $r['tipo'],
            'identificacion' => $r['identificacion'],
            'nombre'         => trim($r['nombre']),
        ], $rows);
    }

    // ── chart: ingresos vs gastos vs balance (últimos 6 meses) ──────────────

    private function fetchChartIngresosGastos(int $userId): array
    {
        $instId = $this->getInstitucionByUserId($userId);
        $months = $this->buildMonthKeys(6);
        $cutoff = $months[0] . '-01';

        // Ingresos por mes (pagos cobrados)
        $stmt = $this->db->prepare(
            "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, COALESCE(SUM(monto), 0) AS total
             FROM pagos
             WHERE institucion = :inst AND estado = 1 AND fecha >= :cutoff
             GROUP BY DATE_FORMAT(fecha, '%Y-%m')
             ORDER BY mes ASC"
        );
        $stmt->execute([':inst' => $instId, ':cutoff' => $cutoff]);
        $ingMap = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $ingMap[$r['mes']] = (float) $r['total'];
        }

        // Gastos por mes
        $stmt = $this->db->prepare(
            "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, COALESCE(SUM(monto), 0) AS total
             FROM gastos
             WHERE institucion = :inst AND estado = 1 AND fecha >= :cutoff
             GROUP BY DATE_FORMAT(fecha, '%Y-%m')
             ORDER BY mes ASC"
        );
        $stmt->execute([':inst' => $instId, ':cutoff' => $cutoff]);
        $gasMap = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $gasMap[$r['mes']] = (float) $r['total'];
        }

        $ingresos = [];
        $gastos   = [];
        $balance  = [];
        foreach ($months as $m) {
            $ing      = round($ingMap[$m] ?? 0, 2);
            $gas      = round($gasMap[$m] ?? 0, 2);
            $ingresos[] = $ing;
            $gastos[]   = $gas;
            $balance[]  = round($ing - $gas, 2);
        }

        return [
            'labels'   => $months,
            'ingresos' => $ingresos,
            'gastos'   => $gastos,
            'balance'  => $balance,
        ];
    }

    // ── chart: consumo de membresías en lo que va del año vigente ────────────

    private function fetchChartMembresias(int $userId): array
    {
        $instId  = $this->getInstitucionByUserId($userId);
        $anoActual = (int) date('Y');

        $stmt = $this->db->prepare(
            "SELECT COALESCE(m.titulo, 'Sin membresía') AS titulo,
                    COUNT(mat.id) AS total
             FROM matricula mat
             LEFT JOIN membresias m ON m.id = mat.membresia
             WHERE mat.institucion = :inst
               AND mat.estado = 1
               AND YEAR(mat.fecha_registro) = :anio
             GROUP BY COALESCE(m.titulo, 'Sin membresía')
             ORDER BY total DESC
             LIMIT 8"
        );
        $stmt->execute([':inst' => $instId, ':anio' => $anoActual]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'anio'  => $anoActual,
            'items' => array_map(fn($r) => [
                'titulo' => $r['titulo'],
                'total'  => (int) $r['total'],
            ], $rows),
        ];
    }

    // ── últimas matrículas registradas ───────────────────────────────────────

    private function fetchUltimasMatriculas(int $userId, array $queryParams): array
    {
        $instId = $this->getInstitucionByUserId($userId);
        $desde = !empty($queryParams['desde']) ? $queryParams['desde'] : null;
        $hasta = !empty($queryParams['hasta']) ? $queryParams['hasta'] : null;
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 20;

        $params = [':inst' => $instId];

        $sql = "SELECT mat.id,
                    mat.fecha_registro,
                    mat.fecha_inicio,
                    mat.fecha_fin,
                    p.id as persona_id,
                    p.identificacion,
                    TRIM(CONCAT(
                        COALESCE(p.primer_nombre,''),' ',
                        COALESCE(p.segundo_nombre,''),' ',
                        COALESCE(p.primer_apellido,''),' ',
                        COALESCE(p.segundo_apellido,'')
                    )) AS nombre,
                    COALESCE(tp.nombre, 'Estudiante') AS tipo,
                    COALESCE(m.titulo, 'Sin membresía') AS membresia,
                    (SELECT COUNT(*) FROM clases_matricula cm WHERE cm.matricula = mat.id AND cm.estado = 1) AS clases_count,
                    (SELECT GROUP_CONCAT(c.nombre SEPARATOR '; ') FROM clases_matricula cm JOIN clases c ON c.id = cm.clase WHERE cm.matricula = mat.id AND cm.estado = 1) AS clases_nombres,
                    (SELECT COUNT(*) FROM actividades_matricula am WHERE am.matricula = mat.id AND am.estado = 1) AS actividades_count,
                    (SELECT GROUP_CONCAT(a.nombre SEPARATOR '; ') FROM actividades_matricula am JOIN actividades a ON a.id = am.actividad WHERE am.matricula = mat.id AND am.estado = 1) AS actividades_nombres,
                    (SELECT GROUP_CONCAT(DISTINCT cd.nombre SEPARATOR '; ') FROM actividades_matricula am JOIN actividades a ON a.id = am.actividad JOIN categorias_deportivas cd ON cd.id = a.categoria_deportiva WHERE am.matricula = mat.id AND am.estado = 1) AS categorias_nombres
             FROM matricula mat
             JOIN personas p ON p.id = mat.persona
             LEFT JOIN persona_institucion pi ON pi.id_persona = p.id AND pi.id_institucion = mat.institucion
             LEFT JOIN tipo_persona tp ON tp.id = pi.tipo
             LEFT JOIN membresias m ON m.id = mat.membresia
             WHERE mat.institucion = :inst AND mat.estado = 1";

        if ($desde) {
            $sql .= " AND DATE(mat.fecha_registro) >= :desde";
            $params[':desde'] = $desde;
        }
        if ($hasta) {
            $sql .= " AND DATE(mat.fecha_registro) <= :hasta";
            $params[':hasta'] = $hasta;
        }

        $sql .= " ORDER BY mat.fecha_registro DESC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':inst', $instId, PDO::PARAM_INT);
        if ($desde) $stmt->bindValue(':desde', $desde);
        if ($hasta) $stmt->bindValue(':hasta', $hasta);
        if ($limit > 0) $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(fn($r) => [
            'id'                => (int) $r['id'],
            'nombre'            => trim($r['nombre']),
            'persona_id'        => (int) $r['persona_id'],
            'identificacion'    => $r['identificacion'],
            'tipo'              => $r['tipo'],
            'membresia'         => $r['membresia'],
            'fecha_registro'    => $r['fecha_registro'],
            'fecha_inicio'      => $r['fecha_inicio'],
            'fecha_fin'         => $r['fecha_fin'],
            'clases_count'       => (int) $r['clases_count'],
            'clases_nombres'     => $r['clases_nombres'],
            'actividades_count'  => (int) $r['actividades_count'],
            'actividades_nombres' => $r['actividades_nombres'],
            'categorias_nombres' => $r['categorias_nombres'],
        ], $rows);
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function buildMonthKeys(int $count): array
    {
        $months = [];
        for ($i = $count - 1; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime("-{$i} months"));
        }
        return $months;
    }

    private function getInstitucionByUserId(int $userId): int
    {
        $personaRepo = new PersonaRepository();
        $piRepo      = new PersonaInstitucionRepository();

        $persona = $personaRepo->findByUserId($userId);
        if ($persona === null) {
            throw new RuntimeException('No se encontró persona asociada al usuario.', 404);
        }

        $pi = $piRepo->findByPersonaId((int) $persona->getId());
        if ($pi === null) {
            throw new RuntimeException('No se encontró institución asociada al usuario.', 404);
        }

        return (int) $pi->getIdInstitucion()->getId();
    }
}
