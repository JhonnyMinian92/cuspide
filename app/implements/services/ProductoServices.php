<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/ProductoServiceInterface.php';
require_once __DIR__ . '/../persistence/conexion/Database.php';
require_once __DIR__ . '/../persistence/repository/ProductoRepository.php';
require_once __DIR__ . '/../persistence/entitys/Producto.php';
require_once __DIR__ . '/../utils/Token.php';

use Core\Database;
use Repositories\ProductoRepository;
use Entitys\Producto;
use Utils\Token;
use Interfaces\ProductoServiceInterface;

class ProductoServices implements ProductoServiceInterface
{
    private ProductoRepository $productoRepo;
    private array $security;

    public function __construct()
    {
        $this->productoRepo = new ProductoRepository();
        $this->security     = require __DIR__ . '/../../properties/security.php';
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
                return $this->fetchProductos($queryParams);

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'save':
                $id = isset($queryParams['id']) ? (int)$queryParams['id'] : null;
                return $this->saveProducto($id);

            case $method === 'PUT' && $resource === 'disable':
                if (empty($queryParams['id'])) {
                    throw new \RuntimeException('El parámetro id es requerido.', 400);
                }
                return $this->disableProducto((int)$queryParams['id']);

            case $method === 'GET' && $resource === 'categories':
                return $this->fetchCategorias();

            case in_array($method, ['POST', 'PUT'], true) && $resource === 'saveCategory':
            case in_array($method, ['POST', 'PUT'], true) && $resource === 'savecategory':
                $id = isset($queryParams['id']) ? (int)$queryParams['id'] : null;
                return $this->saveCategoria($id);

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchProductos
    // -------------------------------------------------------------------------
    private function fetchProductos(array $queryParams): array
    {
        $db = Database::getInstance();

        $sql = "SELECT pr.id,
                       pr.codigo,
                       pr.nombre,
                       pr.descripcion,
                       pr.precio,
                       pr.costo,
                       pr.controla_stock,
                       pr.estado,
                       pc.nombre AS categoria_nombre,
                       pr.categoria,
                       COALESCE(inv.stock_actual, 0) AS stock
                FROM productos pr
                LEFT JOIN producto_categorias pc ON pc.id = pr.categoria
                LEFT JOIN inventario inv ON inv.producto = pr.id
                WHERE pr.estado = 1";

        $params = [];

        if (!empty($queryParams['institucion'])) {
            $sql              .= ' AND pr.institucion = :inst';
            $params[':inst']   = (int)$queryParams['institucion'];
        }

        if (!empty($queryParams['categoria'])) {
            $sql              .= ' AND pr.categoria = :cat';
            $params[':cat']    = (int)$queryParams['categoria'];
        }

        $sql .= ' ORDER BY pr.nombre ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'              => (int)$row['id'],
                'codigo'          => $row['codigo'],
                'nombre'          => $row['nombre'],
                'descripcion'     => $row['descripcion'],
                'precio'          => (float)$row['precio'],
                'costo'           => (float)$row['costo'],
                'controla_stock'  => (bool)$row['controla_stock'],
                'estado'          => (int)$row['estado'],
                'categoria'       => isset($row['categoria']) ? (int)$row['categoria'] : null,
                'categoria_nombre'=> $row['categoria_nombre'] ?? null,
                'stock'           => (float)$row['stock'],
            ];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // saveProducto
    // -------------------------------------------------------------------------
    private function saveProducto(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        foreach (['codigo', 'nombre', 'descripcion', 'categoria', 'precio', 'costo', 'institucion'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \RuntimeException("El campo {$field} es requerido.", 400);
            }
        }

        $bodyId = isset($data['id']) ? (int)$data['id'] : 0;
        $finalId = ($id !== null && $id > 0) ? $id : ($bodyId > 0 ? $bodyId : null);

        $estado       = isset($data['estado']) ? (int)$data['estado'] : 1;
        $controlaStock = isset($data['controla_stock']) ? (bool)$data['controla_stock'] : false;

        $producto = new Producto(
            $finalId,
            trim($data['codigo']),
            trim($data['nombre']),
            trim($data['descripcion']),
            (int)$data['categoria'],
            (float)$data['precio'],
            (float)$data['costo'],
            $controlaStock,
            (int)$data['institucion'],
            $estado
        );

        $saved = $this->productoRepo->save($producto);

        return [
            'id'             => (int)$saved->getId(),
            'codigo'         => $saved->getCodigo(),
            'nombre'         => $saved->getNombre(),
            'descripcion'    => $saved->getDescripcion(),
            'precio'         => $saved->getPrecio(),
            'costo'          => $saved->getCosto(),
            'controla_stock' => $saved->getControlaStock(),
            'estado'         => $estado,
        ];
    }

    // -------------------------------------------------------------------------
    // disableProducto
    // -------------------------------------------------------------------------
    private function disableProducto(int $id): array
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare('UPDATE productos SET estado = 2 WHERE id = :id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Producto no encontrado.', 404);
        }

        return [
            'id'     => $id,
            'estado' => 'INACTIVO',
        ];
    }

    // -------------------------------------------------------------------------
    // fetchCategorias
    // -------------------------------------------------------------------------
    private function fetchCategorias(): array
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT id, nombre, nemonico, descripcion, institucion, estado FROM producto_categorias WHERE estado = 1 ORDER BY nombre ASC'
        );
        $stmt->execute();
        $rows   = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'          => (int)$row['id'],
                'nombre'      => $row['nombre'],
                'nemonico'    => $row['nemonico'] ?? null,
                'descripcion' => $row['descripcion'],
                'institucion' => isset($row['institucion']) ? (int)$row['institucion'] : null,
                'estado'      => (int)($row['estado'] ?? 1),
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // saveCategoria
    // -------------------------------------------------------------------------
    private function saveCategoria(?int $id): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Cuerpo de la solicitud inválido.', 400);
        }

        $nombre = trim((string)($data['nombre'] ?? ''));
        if ($nombre === '') {
            throw new \RuntimeException('El campo nombre es requerido.', 400);
        }

        $descripcion = isset($data['descripcion']) && $data['descripcion'] !== ''
            ? trim((string)$data['descripcion'])
            : null;
        $nemonico = trim((string)($data['nemonico'] ?? ''));
        if ($nemonico === '') {
            $nemonico = strtoupper((string)preg_replace('/[^A-Z0-9]+/', '_', strtoupper($nombre)));
        }
        $institucion = isset($data['institucion']) && $data['institucion'] !== '' ? (int)$data['institucion'] : null;
        $estado = isset($data['estado']) ? (int)$data['estado'] : 1;
        $db = Database::getInstance();
        $bodyId = isset($data['id']) ? (int)$data['id'] : 0;
        $finalId = ($id !== null && $id > 0) ? $id : ($bodyId > 0 ? $bodyId : null);

        if ($finalId !== null) {
            $stmt = $db->prepare(
                'UPDATE producto_categorias
                 SET nombre = :nombre, nemonico = :nemonico, descripcion = :descripcion, institucion = :institucion, estado = :estado
                 WHERE id = :id'
            );
            $stmt->execute([
                ':nombre' => $nombre,
                ':nemonico' => $nemonico,
                ':descripcion' => $descripcion,
                ':institucion' => $institucion,
                ':estado' => $estado,
                ':id' => $finalId,
            ]);
            if ($stmt->rowCount() === 0) {
                $check = $db->prepare('SELECT id FROM producto_categorias WHERE id = :id');
                $check->execute([':id' => $finalId]);
                if (!$check->fetch(\PDO::FETCH_ASSOC)) {
                    throw new \RuntimeException('Categoría no encontrada.', 404);
                }
            }
        } else {
            $stmt = $db->prepare(
                'INSERT INTO producto_categorias (nombre, nemonico, descripcion, institucion, estado)
                 VALUES (:nombre, :nemonico, :descripcion, :institucion, :estado)'
            );
            $stmt->execute([
                ':nombre' => $nombre,
                ':nemonico' => $nemonico,
                ':descripcion' => $descripcion,
                ':institucion' => $institucion,
                ':estado' => $estado,
            ]);
            $finalId = (int)$db->lastInsertId();
        }

        return [
            'id' => (int)$finalId,
            'nombre' => $nombre,
            'nemonico' => $nemonico,
            'descripcion' => $descripcion,
            'institucion' => $institucion,
            'estado' => $estado,
        ];
    }
}
