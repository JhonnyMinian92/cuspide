<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/VentaRepository.php';
require_once __DIR__ . '/../persistence/repository/VentaDetalleRepository.php';
require_once __DIR__ . '/../persistence/repository/PagoRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/InventarioRepository.php';
require_once __DIR__ . '/../persistence/repository/MovimientoInventarioRepository.php';
require_once __DIR__ . '/../persistence/repository/ProductoRepository.php';
require_once __DIR__ . '/../persistence/repository/FormaPagoRepository.php';
require_once __DIR__ . '/../persistence/entitys/FormaPago.php';
require_once __DIR__ . '/../persistence/entitys/Venta.php';
require_once __DIR__ . '/../persistence/entitys/VentaDetalle.php';
require_once __DIR__ . '/../persistence/entitys/Pago.php';
require_once __DIR__ . '/../persistence/entitys/MovimientoInventario.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Repositories\VentaRepository;
use Repositories\VentaDetalleRepository;
use Repositories\PagoRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Repositories\InventarioRepository;
use Repositories\MovimientoInventarioRepository;
use Repositories\ProductoRepository;
use Repositories\FormaPagoRepository;
use Entitys\FormaPago;
use Entitys\Venta;
use Entitys\VentaDetalle;
use Entitys\Pago;
use Entitys\MovimientoInventario;
use Utils\Token;

class VentaServices
{
    private VentaRepository               $ventaRepo;
    private VentaDetalleRepository        $detalleRepo;
    private PagoRepository                $pagoRepo;
    private PersonaRepository             $personaRepo;
    private InventarioRepository          $inventarioRepo;
    private MovimientoInventarioRepository $movimientoRepo;
    private ProductoRepository            $productoRepo;
    private FormaPagoRepository           $formaPagoRepo;
    private array                         $security;
    private \PDO                          $db;

    public function __construct()
    {
        $this->db             = Database::getInstance();
        $this->ventaRepo      = new VentaRepository();
        $this->detalleRepo    = new VentaDetalleRepository();
        $this->pagoRepo       = new PagoRepository();
        $this->personaRepo    = new PersonaRepository();
        $this->inventarioRepo = new InventarioRepository();
        $this->movimientoRepo = new MovimientoInventarioRepository();
        $this->productoRepo   = new ProductoRepository();
        $this->formaPagoRepo  = new FormaPagoRepository();
        $this->security       = require __DIR__ . '/../../properties/security.php';
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
                return $this->fetchVentas($queryParams, $userId);

            case $method === 'GET' && $resource === 'byclient':
                return $this->fetchByClient($queryParams);

            case $method === 'GET' && $resource === 'detail':
                if (empty($queryParams['id'])) {
                    throw new \RuntimeException('El parámetro id es requerido.', 400);
                }
                return $this->fetchDetail((int)$queryParams['id']);

            case $method === 'POST' && $resource === 'create':
                return $this->createVenta($userId);

            case $method === 'POST' && $resource === 'cancel':
                $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
                $ventaId = !empty($queryParams['id']) ? (int)$queryParams['id'] : (!empty($body['id']) ? (int)$body['id'] : 0);
                if ($ventaId <= 0) {
                    throw new \RuntimeException('El parámetro id es requerido.', 400);
                }
                $motivo = isset($body['motivo_anulacion']) ? substr(trim((string)$body['motivo_anulacion']), 0, 255) : null;
                if ($motivo === '') $motivo = null;
                return $this->cancelVenta($ventaId, $motivo);

            case $method === 'GET' && $resource === 'formas_pago':
                return $this->fetchFormasPago();

            case $method === 'GET' && $resource === 'list_externas':
                return $this->fetchVentasExternas($queryParams, $userId);

            case $method === 'GET' && $resource === 'detail_externa':
                if (empty($queryParams['id'])) {
                    throw new \RuntimeException('El parámetro id es requerido.', 400);
                }
                return $this->fetchDetailExterna((int)$queryParams['id']);

            case $method === 'POST' && $resource === 'create_externa':
                return $this->createVentaExterna($userId);

            case $method === 'POST' && $resource === 'cancel_externa':
                $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
                $ventaExtId = !empty($queryParams['id']) ? (int)$queryParams['id'] : (!empty($body['id']) ? (int)$body['id'] : 0);
                if ($ventaExtId <= 0) {
                    throw new \RuntimeException('El parámetro id es requerido.', 400);
                }
                return $this->cancelVentaExterna($ventaExtId);

            case $method === 'POST' && $resource === 'pay_externa':
                return $this->payVentaExterna();

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchVentas
    // -------------------------------------------------------------------------
    private function fetchVentas(array $queryParams, int $userId): array
    {
        $filters = [
            'institucion' => $this->getInstitucionByUserId($userId),
            'limit'       => isset($queryParams['limit']) ? (int)$queryParams['limit'] : 20,
            'page'        => isset($queryParams['page']) ? max(1, (int)$queryParams['page']) : 1,
            'desde'       => !empty($queryParams['desde']) ? $queryParams['desde'] : null,
            'hasta'       => !empty($queryParams['hasta']) ? $queryParams['hasta'] : null,
            'q'           => !empty($queryParams['q']) ? $queryParams['q'] : null,
        ];

        if (!empty($queryParams['tipo'])) {
            $filters['tipo'] = $queryParams['tipo'];
        }

        // Reemplazo de llamada al repositorio por una consulta directa para asegurar el filtrado por 'tipo'.
        // El repositorio parece no estar aplicando este filtro correctamente.
        $nombreCompletoSql = "TRIM(CONCAT(COALESCE(p.primer_nombre,''),' ', COALESCE(p.segundo_nombre,''),' ', COALESCE(p.primer_apellido,''),' ', COALESCE(p.segundo_apellido,'')))";
        $baseSql = "FROM ventas v JOIN personas p ON v.persona = p.id WHERE v.institucion = :institucion";
        $params = [':institucion' => $filters['institucion']];

        if (!empty($filters['tipo'])) {
            $baseSql .= " AND v.tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }
        if (!empty($filters['q'])) {
            $baseSql .= " AND (v.numero LIKE :q OR p.identificacion LIKE :q OR {$nombreCompletoSql} LIKE :q)";
            $params[':q'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['desde'])) {
            $baseSql .= " AND DATE(v.fecha) >= :desde";
            $params[':desde'] = $filters['desde'];
        }
        if (!empty($filters['hasta'])) {
            $baseSql .= " AND DATE(v.fecha) <= :hasta";
            $params[':hasta'] = $filters['hasta'];
        }

        // Contar total de registros
        $countStmt = $this->db->prepare("SELECT COUNT(v.id) " . $baseSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Obtener registros con paginación
        $limit = $filters['limit'];
        $itemsSql = "SELECT v.id, v.numero, v.fecha, v.tipo, v.total, v.saldo, v.estado, p.identificacion, {$nombreCompletoSql} as nombre " . $baseSql . " ORDER BY v.fecha DESC, v.id DESC";
        if ($limit > 0) {
            $itemsSql .= " LIMIT :limit OFFSET :offset";
        }

        $itemsStmt = $this->db->prepare($itemsSql);
        foreach ($params as $key => &$val) {
            $itemsStmt->bindParam($key, $val);
        }
        if ($limit > 0) {
            $offset = ($filters['page'] - 1) * $limit;
            $itemsStmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $itemsStmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        }
        $itemsStmt->execute();
        $items = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = ['items' => $items, 'total' => $total];

        return [
            'items' => array_map(fn($row) => [
                'id'             => (int)$row['id'],
                'numero'         => $row['numero'],
                'fecha'          => $row['fecha'],
                'tipo'           => $row['tipo'],
                'total'          => (float)$row['total'],
                'saldo'          => (float)$row['saldo'],
                'estado'         => (int)$row['estado'],
                'identificacion' => $row['identificacion'],
                'nombre'         => $row['nombre'],
            ], $result['items']),
            'totalItems'  => (int) $result['total'],
            'totalPages'  => ($filters['limit'] > 0) ? (int) ceil($result['total'] / $filters['limit']) : 1,
            'currentPage' => ($filters['limit'] > 0) ? $filters['page'] : 1,
        ];
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
                'descuento' => $venta->getDescuento(),
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

        $persona = $venta->getPersona();

        // Fetch detail lines via repository
        $detalles = $this->detalleRepo->findByVenta($id);

        // Fetch pagos applied to this venta via repository
        $pagosRows = $this->pagoRepo->findByVenta($id);

        $detallesFormatted = [];
        foreach ($detalles as $d) {
            $producto = $d->getProducto();
            $detallesFormatted[] = [
                'id'              => (int)$d->getId(),
                'descripcion'     => $producto ? $producto->getNombre() : 'Descripción no disponible',
                'cantidad'        => (float)$d->getCantidad(),
                'precio_unitario' => (float)$d->getPrecioUnitario(),
                'descuento'       => (float)$d->getDescuento(),
                'subtotal'        => (float)$d->getTotal(),
            ];
        }

        $pagosFormatted = [];
        foreach ($pagosRows as $p) {
            $pagosFormatted[] = [
                'numero'    => $p['numero'],
                'fecha'     => $p['fecha'],
                'monto'     => (float)$p['monto'],
                'forma_pago' => $p['forma_pago'] ?? 'Desconocida',
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
            'cliente' => [
                'nombre' => $persona ? trim($persona->getPrimerNombre() . ' ' . $persona->getPrimerApellido()) : 'N/A',
                'identificacion' => $persona ? $persona->getIdentificacion() : 'N/A'
            ],
            'detalles'        => $detallesFormatted,
            'pagos_aplicados' => $pagosFormatted,
        ];
    }

    // -------------------------------------------------------------------------
    // createVenta
    // -------------------------------------------------------------------------
    private function createVenta(int $userId): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['persona', 'tipo', 'items'] as $field) {
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
        $institucion = $this->getInstitucionByUserId($userId);
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
                $productoId    = (int)($item['producto_id']     ?? 0);
                $qty           = (float)($item['cantidad']       ?? 0);
                $price         = (float)($item['precio_unitario'] ?? 0);
                $itemDescuento = (float)($item['descuento']      ?? 0);
                $itemSubtotal  = round(($qty * $price) - $itemDescuento, 2);

                // Save venta_detalle via repository
                $ventaDetalle = new VentaDetalle(
                    null,
                    $ventaId,
                    $productoId > 0 ? $productoId : 0,
                    null,
                    $qty,
                    $price,
                    $itemDescuento,
                    $itemSubtotal
                );
                $this->detalleRepo->save($ventaDetalle);

                if ($productoId <= 0) {
                    continue;
                }

                // Check if producto controla_stock
                $prod = $this->productoRepo->findById($productoId);
                if ($prod && (bool)$prod->getControlaStock()) {
                    // 3. Update inventario (reduce stock) via repository
                    $inv = $this->inventarioRepo->findByProductoAndInstitucion($productoId, $institucion);
                    if ($inv !== null) {
                        $stockAnterior = $inv->getStockActual();
                        $stockNuevo    = $stockAnterior - $qty;
                        $inv->setStockActual($stockNuevo);
                        $inv->setFechaActualizacion($fecha);
                        $this->inventarioRepo->save($inv);
                    } else {
                        $stockAnterior = 0.0;
                        $stockNuevo    = -$qty;
                    }

                    // 4. Insert movimiento_inventario via repository
                    $movimiento = new MovimientoInventario(
                        null,
                        $productoId,
                        null,
                        $institucion,
                        'SALIDA',
                        $qty,
                        $stockAnterior,
                        $stockNuevo,
                        $numero,
                        $fecha,
                        'Venta ' . $numero
                    );
                    $this->movimientoRepo->save($movimiento);
                }
            }

            // 5. If forma_pago provided, insert pago immediately via repository
            if ($formaPago !== null) {
                $numeroPago = 'PAG-' . date('Ymd') . '-' . uniqid();
                $pagoEntity = new Pago(
                    null,
                    $numeroPago,
                    $fecha,
                    $personaId,
                    $matriculaId,
                    $ventaId,
                    $formaPago,
                    $institucion,
                    $total,
                    $referencia,
                    'Pago de venta ' . $numero,
                    1
                );
                $this->pagoRepo->save($pagoEntity);
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

    // -------------------------------------------------------------------------
    // cancelVenta
    // -------------------------------------------------------------------------
    private function cancelVenta(int $ventaId, ?string $motivo = null): array
    {
        $venta = $this->ventaRepo->findById($ventaId);
        if (!$venta) {
            throw new \RuntimeException('Venta no encontrada.', 404);
        }
        if ($venta->getEstado()?->getId() !== 1) { // 1 = Activo/Pendiente
            throw new \RuntimeException('La venta ya ha sido anulada o saldada.', 409);
        }

        try {
            $this->db->beginTransaction();

            // 1. Revertir stock de cada item
            $detalles = $this->detalleRepo->findByVenta($ventaId);
            foreach ($detalles as $detalle) {
                $producto = $detalle->getProducto();
                if ($producto && $producto->getControlaStock()) {
                    $institucionId = (int) $venta->getInstitucion()->getId();
                    $productoId = (int) $producto->getId();
                    $cantidadDevuelta = (float) $detalle->getCantidad();

                    // Actualizar inventario
                    $inv = $this->inventarioRepo->findByProductoAndInstitucion($productoId, $institucionId);
                    if ($inv) {
                        $stockAnterior = $inv->getStockActual();
                        $stockNuevo = $stockAnterior + $cantidadDevuelta;
                        $inv->setStockActual($stockNuevo);
                        $inv->setFechaActualizacion(date('Y-m-d H:i:s'));
                        $this->inventarioRepo->save($inv);
                    } else {
                        // Este caso es improbable si se realizó una venta, pero por seguridad...
                        $stockAnterior = 0.0;
                        $stockNuevo = $cantidadDevuelta;
                    }

                    // Registrar el movimiento de inventario
                    $movimiento = new MovimientoInventario(
                        null,
                        $productoId,
                        null,
                        $institucionId,
                        'ENTRADA', // O 'DEVOLUCION'
                        $cantidadDevuelta,
                        $stockAnterior,
                        $stockNuevo,
                        $venta->getNumero(),
                        date('Y-m-d H:i:s'),
                        'Anulación de Venta ' . $venta->getNumero()
                    );
                    $this->movimientoRepo->save($movimiento);
                }
            }

            // 2. Anular pagos asociados (registra motivo si viene)
            $stmtPagos = $this->db->prepare('UPDATE pagos SET estado = 2, motivo_anulacion = :motivo WHERE venta = :venta_id');
            $stmtPagos->execute([':motivo' => $motivo, ':venta_id' => $ventaId]);

            // 3. Anular la venta (actualiza estado y pone saldo a 0)
            $this->ventaRepo->cancel($ventaId);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException('Error al anular la venta: ' . $e->getMessage(), 500);
        }

        return ['id' => $ventaId, 'status' => 'ANULADO', 'mensaje' => 'Factura anulada correctamente.'];
    }

    // -------------------------------------------------------------------------
    // fetchFormasPago
    // -------------------------------------------------------------------------
    private function fetchFormasPago(): array
    {
        $formas = $this->formaPagoRepo->findAll();
        return array_map(fn($f) => ['id' => (int) $f->getId(), 'nombre' => $f->getNombre()], $formas);
    }

    // =========================================================================
    // VENTAS EXTERNAS (clientes no registrados)
    // =========================================================================

    private function fetchVentasExternas(array $queryParams, int $userId): array
    {
        $instId  = $this->getInstitucionByUserId($userId);
        $limit   = isset($queryParams['limit'])  ? (int) $queryParams['limit']         : 20;
        $page    = isset($queryParams['page'])   ? max(1, (int) $queryParams['page'])   : 1;
        $q       = !empty($queryParams['q'])     ? $queryParams['q']                   : null;
        $desde   = !empty($queryParams['desde']) ? $queryParams['desde']               : null;
        $hasta   = !empty($queryParams['hasta']) ? $queryParams['hasta']               : null;

        $where  = 'WHERE ve.institucion = :inst';
        $params = [':inst' => $instId];

        if ($q !== null) {
            $where .= ' AND (ve.numero LIKE :q OR ve.cliente_nombre LIKE :q OR ve.cliente_identificacion LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($desde !== null) { $where .= ' AND DATE(ve.fecha) >= :desde'; $params[':desde'] = $desde; }
        if ($hasta !== null) { $where .= ' AND DATE(ve.fecha) <= :hasta'; $params[':hasta'] = $hasta; }

        $cntStmt = $this->db->prepare("SELECT COUNT(*) FROM ventas_externas ve $where");
        $cntStmt->execute($params);
        $total = (int) $cntStmt->fetchColumn();

        $sql = "SELECT ve.id, ve.numero, ve.fecha, ve.cliente_nombre, ve.cliente_identificacion,
                       ve.total, ve.saldo, ve.estado, fp.nombre AS forma_pago_nombre
                FROM ventas_externas ve
                LEFT JOIN formas_pago fp ON fp.id = ve.forma_pago
                $where ORDER BY ve.fecha DESC, ve.id DESC";
        if ($limit > 0) {
            $sql .= " LIMIT :lim OFFSET :off";
        }
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => &$v) { $stmt->bindParam($k, $v); }
        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $stmt->bindParam(':lim', $limit, \PDO::PARAM_INT);
            $stmt->bindParam(':off', $offset, \PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items'      => array_map(fn($r) => [
                'id'                    => (int)  $r['id'],
                'numero'                => $r['numero'],
                'fecha'                 => $r['fecha'],
                'cliente_nombre'        => $r['cliente_nombre'],
                'cliente_identificacion'=> $r['cliente_identificacion'],
                'total'                 => (float)$r['total'],
                'saldo'                 => (float)$r['saldo'],
                'estado'                => (int)  $r['estado'],
                'forma_pago'            => $r['forma_pago_nombre'],
            ], $rows),
            'totalItems' => $total,
            'totalPages' => $limit > 0 ? (int) ceil($total / $limit) : 1,
            'currentPage'=> $page,
        ];
    }

    private function fetchDetailExterna(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT ve.*, fp.nombre AS forma_pago_nombre
             FROM ventas_externas ve
             LEFT JOIN formas_pago fp ON fp.id = ve.forma_pago
             WHERE ve.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $venta = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$venta) {
            throw new \RuntimeException('Venta externa no encontrada.', 404);
        }

        $dStmt = $this->db->prepare(
            'SELECT ved.*, p.nombre AS producto_nombre
             FROM ventas_externas_detalle ved
             LEFT JOIN productos p ON p.id = ved.producto
             WHERE ved.venta_externa = :id ORDER BY ved.id ASC'
        );
        $dStmt->execute([':id' => $id]);
        $detalles = $dStmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'venta' => [
                'id'         => (int)   $venta['id'],
                'numero'     => $venta['numero'],
                'fecha'      => $venta['fecha'],
                'subtotal'   => (float) $venta['subtotal'],
                'descuento'  => (float) $venta['descuento'],
                'impuesto'   => (float) $venta['impuesto'],
                'total'      => (float) $venta['total'],
                'saldo'      => (float) $venta['saldo'],
                'estado'     => (int)   $venta['estado'],
                'referencia' => $venta['referencia'],
                'forma_pago' => $venta['forma_pago_nombre'],
            ],
            'cliente' => [
                'nombre'         => $venta['cliente_nombre'],
                'identificacion' => $venta['cliente_identificacion'] ?? '—',
                'telefono'       => $venta['cliente_telefono']       ?? '—',
            ],
            'detalles' => array_map(fn($d) => [
                'id'              => (int)   $d['id'],
                'descripcion'     => $d['producto_nombre'] ?? $d['descripcion'],
                'cantidad'        => (float) $d['cantidad'],
                'precio_unitario' => (float) $d['precio_unitario'],
                'descuento'       => (float) $d['descuento'],
                'subtotal'        => (float) $d['subtotal'],
            ], $detalles),
        ];
    }

    private function createVentaExterna(int $userId): array
    {
        $data = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        $instId    = $this->getInstitucionByUserId($userId);
        $nombre    = trim((string) ($data['cliente_nombre']   ?? 'Anónimo'));
        $cedula    = trim((string) ($data['cliente_identificacion'] ?? '')) ?: null;
        $telefono  = trim((string) ($data['cliente_telefono'] ?? '')) ?: null;
        $items     = is_array($data['items'] ?? null) ? $data['items'] : [];
        $descuento = (float) ($data['descuento'] ?? 0);
        $formaPago = isset($data['forma_pago']) && $data['forma_pago'] !== '' ? (int) $data['forma_pago'] : null;
        $referencia= trim((string) ($data['referencia'] ?? '')) ?: null;

        if (empty($items)) {
            throw new \RuntimeException('Se requiere al menos un item.', 400);
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float)($item['cantidad'] ?? 0) * (float)($item['precio_unitario'] ?? 0);
        }
        $impuesto = 0.0;
        $total    = round($subtotal - $descuento + $impuesto, 2);
        $saldo    = ($formaPago !== null) ? 0.0 : $total;
        $numero   = 'VEX-' . date('Ymd') . '-' . uniqid();
        $fecha    = date('Y-m-d H:i:s');

        try {
            $this->db->beginTransaction();

            $insVenta = $this->db->prepare(
                'INSERT INTO ventas_externas
                 (numero, fecha, cliente_nombre, cliente_identificacion, cliente_telefono,
                  institucion, subtotal, descuento, impuesto, total, saldo, forma_pago, referencia, estado)
                 VALUES (:num,:fec,:nom,:ced,:tel,:inst,:sub,:des,:imp,:tot,:sal,:fp,:ref,1)'
            );
            $insVenta->execute([
                ':num'  => $numero,   ':fec'  => $fecha,
                ':nom'  => $nombre,   ':ced'  => $cedula,   ':tel'  => $telefono,
                ':inst' => $instId,   ':sub'  => $subtotal, ':des'  => $descuento,
                ':imp'  => $impuesto, ':tot'  => $total,    ':sal'  => $saldo,
                ':fp'   => $formaPago,':ref'  => $referencia,
            ]);
            $ventaExtId = (int) $this->db->lastInsertId();

            $insDetalle = $this->db->prepare(
                'INSERT INTO ventas_externas_detalle
                 (venta_externa, producto, descripcion, cantidad, precio_unitario, descuento, subtotal)
                 VALUES (:vid, :pid, :desc, :qty, :price, :dis, :sub)'
            );

            foreach ($items as $item) {
                $productoId    = isset($item['producto_id']) && (int)$item['producto_id'] > 0 ? (int)$item['producto_id'] : null;
                $qty           = (float) ($item['cantidad']        ?? 0);
                $price         = (float) ($item['precio_unitario'] ?? 0);
                $itemDesc      = (float) ($item['descuento']       ?? 0);
                $itemSubtotal  = round(($qty * $price) - $itemDesc, 2);
                $descripcion   = (string) ($item['descripcion']    ?? 'Artículo');

                $insDetalle->execute([
                    ':vid'  => $ventaExtId, ':pid'  => $productoId,
                    ':desc' => $descripcion,':qty'  => $qty,
                    ':price'=> $price,      ':dis'  => $itemDesc,
                    ':sub'  => $itemSubtotal,
                ]);

                // Descontar stock si el producto lo controla
                if ($productoId !== null) {
                    $prod = $this->productoRepo->findById($productoId);
                    if ($prod && (bool) $prod->getControlaStock()) {
                        $inv = $this->inventarioRepo->findByProductoAndInstitucion($productoId, $instId);
                        if ($inv !== null) {
                            $stockAnt = $inv->getStockActual();
                            $inv->setStockActual($stockAnt - $qty);
                            $inv->setFechaActualizacion($fecha);
                            $this->inventarioRepo->save($inv);
                            $mov = new MovimientoInventario(
                                null, $productoId, null, $instId, 'SALIDA',
                                $qty, $stockAnt, $stockAnt - $qty, $numero, $fecha,
                                'Venta externa ' . $numero
                            );
                            $this->movimientoRepo->save($mov);
                        }
                    }
                }
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException('Error al crear venta externa: ' . $e->getMessage(), 500);
        }

        return ['id' => $ventaExtId, 'numero' => $numero, 'total' => $total, 'saldo' => $saldo];
    }

    private function cancelVentaExterna(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ventas_externas WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $venta = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$venta) {
            throw new \RuntimeException('Venta externa no encontrada.', 404);
        }
        if ((int) $venta['estado'] !== 1) {
            throw new \RuntimeException('La venta ya fue anulada.', 409);
        }

        try {
            $this->db->beginTransaction();

            // Revertir stock
            $dStmt = $this->db->prepare('SELECT * FROM ventas_externas_detalle WHERE venta_externa = :id');
            $dStmt->execute([':id' => $id]);
            foreach ($dStmt->fetchAll(\PDO::FETCH_ASSOC) as $d) {
                $productoId = $d['producto'] ? (int) $d['producto'] : null;
                if ($productoId === null) continue;
                $prod = $this->productoRepo->findById($productoId);
                if ($prod && (bool) $prod->getControlaStock()) {
                    $inv = $this->inventarioRepo->findByProductoAndInstitucion($productoId, (int) $venta['institucion']);
                    if ($inv) {
                        $stockAnt = $inv->getStockActual();
                        $qty      = (float) $d['cantidad'];
                        $inv->setStockActual($stockAnt + $qty);
                        $inv->setFechaActualizacion(date('Y-m-d H:i:s'));
                        $this->inventarioRepo->save($inv);
                        $mov = new MovimientoInventario(
                            null, $productoId, null, (int) $venta['institucion'], 'ENTRADA',
                            $qty, $stockAnt, $stockAnt + $qty, $venta['numero'], date('Y-m-d H:i:s'),
                            'Anulación venta externa ' . $venta['numero']
                        );
                        $this->movimientoRepo->save($mov);
                    }
                }
            }

            $this->db->prepare('UPDATE ventas_externas SET estado = 2 WHERE id = :id')->execute([':id' => $id]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException('Error al anular venta externa: ' . $e->getMessage(), 500);
        }

        return ['id' => $id, 'status' => 'ANULADO', 'mensaje' => 'Venta externa anulada correctamente.'];
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

    private function payVentaExterna(): array
    {
        $body = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($body)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        $id        = (int)   ($body['id']        ?? 0);
        $monto     = (float) ($body['monto']      ?? 0);
        $formaPago = isset($body['forma_pago']) && $body['forma_pago'] !== '' ? (int) $body['forma_pago'] : null;
        $referencia = trim((string) ($body['referencia'] ?? '')) ?: null;

        if ($id <= 0 || $monto <= 0) {
            throw new \RuntimeException('Los campos id y monto son requeridos.', 400);
        }

        $stmt = $this->db->prepare('SELECT id, saldo, estado, numero FROM ventas_externas WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $venta = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$venta) {
            throw new \RuntimeException('Venta externa no encontrada.', 404);
        }
        if ((int) $venta['estado'] !== 1) {
            throw new \RuntimeException('La venta externa está anulada.', 409);
        }

        $saldoActual = (float) $venta['saldo'];
        if ($saldoActual <= 0) {
            throw new \RuntimeException('Esta venta ya no tiene saldo pendiente.', 409);
        }

        $aplicado   = min($monto, $saldoActual);
        $nuevoSaldo = round($saldoActual - $aplicado, 2);

        $upd = $this->db->prepare(
            'UPDATE ventas_externas SET saldo = :saldo'
            . ($formaPago !== null ? ', forma_pago = :fp' : '')
            . ($referencia !== null ? ', referencia = :ref' : '')
            . ' WHERE id = :id'
        );
        $params = [':saldo' => $nuevoSaldo, ':id' => $id];
        if ($formaPago !== null) $params[':fp']  = $formaPago;
        if ($referencia !== null) $params[':ref'] = $referencia;
        $upd->execute($params);

        return [
            'id'           => $id,
            'numero'       => $venta['numero'],
            'monto_aplicado' => $aplicado,
            'saldo_nuevo'  => $nuevoSaldo,
            'mensaje'      => 'Pago de ' . number_format($aplicado, 2) . ' registrado en venta externa ' . $venta['numero'] . '.',
        ];
    }
}
