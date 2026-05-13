<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/VentaRepository.php';
require_once __DIR__ . '/../persistence/repository/VentaDetalleRepository.php';
require_once __DIR__ . '/../persistence/repository/PagoRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/entitys/Venta.php';
require_once __DIR__ . '/../persistence/entitys/VentaDetalle.php';
require_once __DIR__ . '/../persistence/entitys/Pago.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Repositories\VentaRepository;
use Repositories\VentaDetalleRepository;
use Repositories\PagoRepository;
use Repositories\PersonaRepository;
use Entitys\Venta;
use Entitys\VentaDetalle;
use Entitys\Pago;
use Utils\Token;

class VentaServices
{
    private VentaRepository       $ventaRepo;
    private VentaDetalleRepository $detalleRepo;
    private PagoRepository        $pagoRepo;
    private PersonaRepository     $personaRepo;
    private array                 $security;

    public function __construct()
    {
        $this->ventaRepo   = new VentaRepository();
        $this->detalleRepo = new VentaDetalleRepository();
        $this->pagoRepo    = new PagoRepository();
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
                return $this->fetchVentas($queryParams);

            case $method === 'GET' && $resource === 'byclient':
                return $this->fetchByClient($queryParams);

            case $method === 'GET' && $resource === 'detail':
                if (empty($queryParams['id'])) {
                    throw new \RuntimeException('El parámetro id es requerido.', 400);
                }
                return $this->fetchDetail((int)$queryParams['id']);

            case $method === 'POST' && $resource === 'create':
                return $this->createVenta();

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchVentas
    // -------------------------------------------------------------------------
    private function fetchVentas(array $queryParams): array
    {
        if (empty($queryParams['institucion'])) {
            throw new \RuntimeException('El parámetro institucion es requerido.', 400);
        }

        $institucion = (int)$queryParams['institucion'];
        $limit       = isset($queryParams['limit']) ? max(1, (int)$queryParams['limit']) : 100;
        $db          = Database::getInstance();

        $sql = "SELECT v.id,
                       v.numero,
                       v.fecha,
                       v.tipo,
                       v.subtotal,
                       v.descuento,
                       v.total,
                       v.saldo,
                       v.estado,
                       p.identificacion,
                       CONCAT(p.primer_nombre,' ',p.primer_apellido) AS nombre
                FROM ventas v
                JOIN personas p ON p.id = v.persona
                WHERE v.institucion = :inst";

        $params = [':inst' => $institucion];

        if (!empty($queryParams['desde'])) {
            $sql              .= ' AND v.fecha >= :desde';
            $params[':desde']  = $queryParams['desde'];
        }

        if (!empty($queryParams['hasta'])) {
            $sql              .= ' AND v.fecha <= :hasta';
            $params[':hasta']  = $queryParams['hasta'];
        }

        $sql .= ' ORDER BY v.fecha DESC LIMIT :lim';

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'             => (int)$row['id'],
                'numero'         => $row['numero'],
                'fecha'          => $row['fecha'],
                'tipo'           => $row['tipo'],
                'subtotal'       => (float)$row['subtotal'],
                'descuento'      => (float)$row['descuento'],
                'total'          => (float)$row['total'],
                'saldo'          => (float)$row['saldo'],
                'estado'         => (int)$row['estado'],
                'identificacion' => $row['identificacion'],
                'nombre'         => $row['nombre'],
            ];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // fetchByClient
    // -------------------------------------------------------------------------
    private function fetchByClient(array $queryParams): array
    {
        $identificacion = trim($queryParams['identificacion'] ?? '');
        if ($identificacion === '') {
            throw new \RuntimeException('El parámetro identificacion es requerido.', 400);
        }

        $persona = $this->personaRepo->findByIdentificacion($identificacion);
        if (!$persona) {
            throw new \RuntimeException('Persona no encontrada.', 404);
        }

        $ventas = $this->ventaRepo->findByPersona((int)$persona->getId());

        $result = [];
        foreach ($ventas as $venta) {
            $result[] = [
                'id'       => (int)$venta->getId(),
                'numero'   => $venta->getNumero(),
                'fecha'    => $venta->getFecha(),
                'tipo'     => $venta->getTipo(),
                'subtotal' => $venta->getSubtotal(),
                'descuento'=> $venta->getDescuento(),
                'impuesto' => $venta->getImpuesto(),
                'total'    => $venta->getTotal(),
                'saldo'    => $venta->getSaldo(),
                'estado'   => $venta->getEstado()?->getId(),
            ];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // fetchDetail
    // -------------------------------------------------------------------------
    private function fetchDetail(int $id): array
    {
        $venta = $this->ventaRepo->findById($id);
        if (!$venta) {
            throw new \RuntimeException('Venta no encontrada.', 404);
        }

        $db = Database::getInstance();

        // Fetch detail lines
        $sqlDet  = 'SELECT vd.* FROM venta_detalle vd WHERE vd.venta = :id ORDER BY vd.id ASC';
        $stmtDet = $db->prepare($sqlDet);
        $stmtDet->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmtDet->execute();
        $detalles = $stmtDet->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch pagos applied to this venta
        $sqlPag = "SELECT p.numero,
                          p.fecha,
                          p.monto,
                          fp.nombre AS forma_pago
                   FROM pagos p
                   JOIN formas_pago fp ON fp.id = p.forma_pago
                   WHERE p.venta = :vid
                   ORDER BY p.fecha ASC";
        $stmtPag = $db->prepare($sqlPag);
        $stmtPag->bindValue(':vid', $id, \PDO::PARAM_INT);
        $stmtPag->execute();
        $pagosRows = $stmtPag->fetchAll(\PDO::FETCH_ASSOC);

        $detallesFormatted = [];
        foreach ($detalles as $d) {
            $detallesFormatted[] = [
                'id'              => (int)$d['id'],
                'descripcion'     => $d['descripcion'] ?? null,
                'cantidad'        => (float)$d['cantidad'],
                'precio_unitario' => (float)$d['precio_unitario'],
                'descuento'       => (float)$d['descuento'],
                'subtotal'        => isset($d['subtotal']) ? (float)$d['subtotal'] : (float)$d['total'],
            ];
        }

        $pagosFormatted = [];
        foreach ($pagosRows as $p) {
            $pagosFormatted[] = [
                'numero'    => $p['numero'],
                'fecha'     => $p['fecha'],
                'monto'     => (float)$p['monto'],
                'forma_pago'=> $p['forma_pago'],
            ];
        }

        return [
            'venta'           => [
                'id'        => (int)$venta->getId(),
                'numero'    => $venta->getNumero(),
                'fecha'     => $venta->getFecha(),
                'tipo'      => $venta->getTipo(),
                'subtotal'  => $venta->getSubtotal(),
                'descuento' => $venta->getDescuento(),
                'impuesto'  => $venta->getImpuesto(),
                'total'     => $venta->getTotal(),
                'saldo'     => $venta->getSaldo(),
                'estado'    => $venta->getEstado()?->getId(),
            ],
            'detalles'        => $detallesFormatted,
            'pagos_aplicados' => $pagosFormatted,
        ];
    }

    // -------------------------------------------------------------------------
    // createVenta
    // -------------------------------------------------------------------------
    private function createVenta(): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['persona', 'institucion', 'tipo', 'items'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $items = $data['items'];
        if (!is_array($items) || count($items) === 0) {
            throw new \RuntimeException('Se requiere al menos un item.', 400);
        }

        $personaId   = (int)$data['persona'];
        $matriculaId = isset($data['matricula']) && $data['matricula'] !== '' ? (int)$data['matricula'] : null;
        $institucion = (int)$data['institucion'];
        $tipo        = trim($data['tipo']);
        $descuento   = (float)($data['descuento'] ?? 0);
        $formaPago   = isset($data['forma_pago']) && $data['forma_pago'] !== '' ? (int)$data['forma_pago'] : null;
        $referencia  = $data['referencia'] ?? null;

        // Calculate totals
        $subtotal = 0.0;
        foreach ($items as $item) {
            $qty   = (float)($item['cantidad']       ?? 0);
            $price = (float)($item['precio_unitario'] ?? 0);
            $subtotal += $qty * $price;
        }

        $impuesto = 0.0; // extend here if 12% IVA is needed
        $total    = round($subtotal - $descuento + $impuesto, 2);
        $saldo    = ($formaPago !== null) ? 0.0 : $total;
        $estado   = 1;

        $numero = 'VTA-' . date('Ymd') . '-' . uniqid();
        $fecha  = date('Y-m-d H:i:s');

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // 1. Insert venta
            $venta = new Venta(
                null,
                $numero,
                $fecha,
                $personaId,
                $matriculaId,
                $institucion,
                $tipo,
                $subtotal,
                $descuento,
                $impuesto,
                $total,
                $saldo,
                $estado
            );
            $ventaSaved = $this->ventaRepo->save($venta);
            $ventaId    = (int)$ventaSaved->getId();

            // 2. Insert venta_detalle + stock updates
            foreach ($items as $item) {
                $productoId     = (int)($item['producto_id']     ?? 0);
                $qty            = (float)($item['cantidad']       ?? 0);
                $price          = (float)($item['precio_unitario'] ?? 0);
                $itemDescuento  = (float)($item['descuento']      ?? 0);
                $itemSubtotal   = round(($qty * $price) - $itemDescuento, 2);
                $descripcion    = $item['descripcion'] ?? null;

                $sqlDetIns = "INSERT INTO venta_detalle
                                  (venta, descripcion, cantidad, precio_unitario, descuento, subtotal)
                              VALUES
                                  (:venta, :descripcion, :cantidad, :precio_unitario, :descuento, :subtotal)";
                $stmtDetIns = $db->prepare($sqlDetIns);
                $stmtDetIns->execute([
                    ':venta'           => $ventaId,
                    ':descripcion'     => $descripcion,
                    ':cantidad'        => $qty,
                    ':precio_unitario' => $price,
                    ':descuento'       => $itemDescuento,
                    ':subtotal'        => $itemSubtotal,
                ]);

                if ($productoId <= 0) {
                    continue;
                }

                // Check if producto controla_stock
                $sqlProd = 'SELECT controla_stock FROM productos WHERE id = :pid LIMIT 1';
                $stmtProd = $db->prepare($sqlProd);
                $stmtProd->bindValue(':pid', $productoId, \PDO::PARAM_INT);
                $stmtProd->execute();
                $prodRow = $stmtProd->fetch(\PDO::FETCH_ASSOC);

                if ($prodRow && (bool)$prodRow['controla_stock']) {
                    // 3. Update inventario (reduce stock)
                    $sqlInv = "UPDATE inventario
                               SET cantidad_actual = cantidad_actual - :qty
                               WHERE producto = :prod AND institucion = :inst";
                    $stmtInv = $db->prepare($sqlInv);
                    $stmtInv->execute([
                        ':qty'  => $qty,
                        ':prod' => $productoId,
                        ':inst' => $institucion,
                    ]);

                    // Get new stock value for movement record
                    $sqlStk = 'SELECT cantidad_actual FROM inventario WHERE producto = :prod AND institucion = :inst LIMIT 1';
                    $stmtStk = $db->prepare($sqlStk);
                    $stmtStk->execute([':prod' => $productoId, ':inst' => $institucion]);
                    $stkRow  = $stmtStk->fetch(\PDO::FETCH_ASSOC);
                    $stockNuevo = $stkRow ? (float)$stkRow['cantidad_actual'] : 0.0;

                    // 4. Insert movimiento_inventario
                    $sqlMov = "INSERT INTO movimiento_inventario
                                   (producto, variante, tipo_movimiento, cantidad, fecha, referencia, observacion, institucion)
                               VALUES
                                   (:prod, NULL, 'SALIDA', :qty, :fecha, :ref, :obs, :inst)";
                    $stmtMov = $db->prepare($sqlMov);
                    $stmtMov->execute([
                        ':prod'  => $productoId,
                        ':qty'   => $qty,
                        ':fecha' => $fecha,
                        ':ref'   => $numero,
                        ':obs'   => 'Venta ' . $numero,
                        ':inst'  => $institucion,
                    ]);
                }
            }

            // 5. If forma_pago provided, insert pago immediately
            if ($formaPago !== null) {
                $numeroPago = 'PAG-' . date('Ymd') . '-' . uniqid();
                $sqlPago = "INSERT INTO pagos
                                (numero, fecha, persona, matricula, venta, forma_pago, institucion, monto, referencia, observacion, estado)
                            VALUES
                                (:numero, :fecha, :persona, :matricula, :venta, :forma_pago, :inst, :monto, :referencia, :obs, 1)";
                $stmtPago = $db->prepare($sqlPago);
                $stmtPago->execute([
                    ':numero'     => $numeroPago,
                    ':fecha'      => $fecha,
                    ':persona'    => $personaId,
                    ':matricula'  => $matriculaId,
                    ':venta'      => $ventaId,
                    ':forma_pago' => $formaPago,
                    ':inst'       => $institucion,
                    ':monto'      => $total,
                    ':referencia' => $referencia,
                    ':obs'        => 'Pago de venta ' . $numero,
                ]);
            }

            $db->commit();

        } catch (\Throwable $e) {
            $db->rollBack();
            throw new \RuntimeException('Error al crear la venta: ' . $e->getMessage(), 500);
        }

        return [
            'id'     => $ventaId,
            'numero' => $numero,
            'total'  => $total,
            'saldo'  => $saldo,
        ];
    }
}
