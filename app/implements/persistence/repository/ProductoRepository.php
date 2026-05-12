<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Producto.php';

use Core\Database;
use Entitys\Producto;
use PDO;

class ProductoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Producto $producto): Producto
  {
    if ($producto->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO productos (codigo, nombre, descripcion, categoria, precio, costo, controla_stock, institucion, estado) VALUES (:codigo, :nombre, :descripcion, :categoria, :precio, :costo, :controla_stock, :institucion, :estado)");
      $stmt->execute($this->params($producto));
      $producto->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE productos SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion, categoria = :categoria, precio = :precio, costo = :costo, controla_stock = :controla_stock, institucion = :institucion, estado = :estado WHERE id = :id");
      $params = $this->params($producto);
      $params[':id'] = $producto->getId();
      $stmt->execute($params);
    }
    return $producto;
  }

  public function findById(int $id): ?Producto
  {
    $stmt = $this->db->prepare('SELECT * FROM productos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByCodigo(string $codigo): ?Producto
  {
    $stmt = $this->db->prepare('SELECT * FROM productos WHERE codigo = :codigo');
    $stmt->execute([':codigo' => $codigo]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM productos ORDER BY nombre ASC');
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(Producto $producto): array
  {
    return [
      ':codigo' => $producto->getCodigo(),
      ':nombre' => $producto->getNombre(),
      ':descripcion' => $producto->getDescripcion(),
      ':categoria' => $producto->getCategoria()?->getId(),
      ':precio' => $producto->getPrecio(),
      ':costo' => $producto->getCosto(),
      ':controla_stock' => $producto->getControlaStock() ? 1 : 0,
      ':institucion' => $producto->getInstitucion()?->getId(),
      ':estado' => $producto->getEstado()?->getId(),
    ];
  }

  private function map(array $row): Producto
  {
    return new Producto($row['id'], $row['codigo'], $row['nombre'], $row['descripcion'], $row['categoria'], (float) $row['precio'], (float) $row['costo'], (bool) $row['controla_stock'], $row['institucion'], $row['estado']);
  }
}
