<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/CuentaCobrarServiceInterface.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Repositories\PersonaRepository;
use Utils\Token;
use Interfaces\CuentaCobrarServiceInterface;

class CuentaCobrarServices implements CuentaCobrarServiceInterface
{
    private PersonaRepository $personaRepo;
    private array $security;

    public function __construct()
    {
        $this->personaRepo = new PersonaRepository();
        $this->security    = require __DIR__ . '/../../properties/security.php';
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
            case $method === 'GET' && $resource === 'list':
                return $this->fetchCuentasCobrar($queryParams);

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchCuentasCobrar
    // -------------------------------------------------------------------------
    private function fetchCuentasCobrar(array $queryParams): array
    {
        $institucion = isset($queryParams['institucion']) ? (int)$queryParams['institucion'] : 0;
        if ($institucion <= 0) {
            throw new \RuntimeException('El parámetro institucion es requerido.', 400);
        }

        $personaId      = isset($queryParams['persona_id']) ? (int)$queryParams['persona_id'] : 0;
        $identificacion = trim($queryParams['identificacion'] ?? '');

        // If identificacion filter provided, resolve to persona id
        if ($personaId <= 0 && $identificacion !== '') {
            $persona = $this->personaRepo->findByIdentificacion($identificacion);
            if (!$persona) {
                throw new \RuntimeException('Persona no encontrada con esa identificación.', 404);
            }
            $personaId = (int)$persona->getId();
        }

        $db = Database::getInstance();

        $sql = "SELECT v.id,
                       v.numero,
                       v.fecha,
                       v.tipo,
                       v.total,
                       v.saldo,
                       p.identificacion,
                       CONCAT(p.primer_nombre,' ',p.primer_apellido) AS nombre_cliente,
                       DATEDIFF(CURDATE(), v.fecha) AS dias_vencido
                FROM ventas v
                JOIN personas p ON p.id = v.persona
                WHERE v.saldo > 0
                  AND v.estado = 1
                  AND v.institucion = :inst";

        $params = [':inst' => $institucion];

        if ($personaId > 0) {
            $sql             .= ' AND v.persona = :persona_id';
            $params[':persona_id'] = $personaId;
        }

        $sql .= ' ORDER BY v.fecha ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $ventas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalPorCobrar = 0.0;
        $cuentas        = [];

        foreach ($ventas as $venta) {
            $totalPorCobrar += (float)$venta['saldo'];

            // Fetch detail lines for this venta
            $sqlDet = "SELECT vd.descripcion,
                              vd.cantidad,
                              vd.precio_unitario,
                              vd.subtotal
                       FROM venta_detalle vd
                       WHERE vd.venta = :id";
            $stmtDet = $db->prepare($sqlDet);
            $stmtDet->bindValue(':id', (int)$venta['id'], \PDO::PARAM_INT);
            $stmtDet->execute();
            $detalle = $stmtDet->fetchAll(\PDO::FETCH_ASSOC);

            $detalleFormatted = [];
            foreach ($detalle as $d) {
                $detalleFormatted[] = [
                    'descripcion'     => $d['descripcion'],
                    'cantidad'        => (float)$d['cantidad'],
                    'precio_unitario' => (float)$d['precio_unitario'],
                    'subtotal'        => (float)$d['subtotal'],
                ];
            }

            $cuentas[] = [
                'id'             => (int)$venta['id'],
                'numero'         => $venta['numero'],
                'fecha'          => $venta['fecha'],
                'tipo'           => $venta['tipo'],
                'total'          => (float)$venta['total'],
                'saldo'          => (float)$venta['saldo'],
                'identificacion' => $venta['identificacion'],
                'nombre_cliente' => $venta['nombre_cliente'],
                'dias_vencido'   => (int)$venta['dias_vencido'],
                'detalle'        => $detalleFormatted,
            ];
        }

        return [
            'total_por_cobrar' => round($totalPorCobrar, 2),
            'cuentas'          => $cuentas,
        ];
    }
}
