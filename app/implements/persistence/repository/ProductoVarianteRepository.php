<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/ProductoVariante.php';

use Core\Database;
use Entitys\ProductoVariante;
use PDO;

class ProductoVarianteRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(ProductoVariante $variante): ProductoVariante
  {
    if ($variante->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO producto_variantes (producto, sku, talla, color, genero, precio, estado) VALUES (:producto, :sku, :talla, :color, :genero, :precio, :estado)");
      $stmt->execute($this->params($variante));
      $variante->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE producto_variantes SET producto = :producto, sku = :sku, talla = :talla, color = :color, genero = :genero, precio = :precio, estado = :estado WHERE id = :id");
      $params = $this->params($variante);
      $params[':id'] = $variante->getId();
      $stmt->execute($params);
    }
    return $variante;
  }

  public function findById(int $id): ?ProductoVariante
  {
    $stmt = $this->db->prepare('SELECT * FROM producto_variantes WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByProducto(int $productoId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM producto_variantes WHERE producto = :producto ORDER BY sku ASC');
    $stmt->execute([':producto' => $productoId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(ProductoVariante $variante): array
  {
    return [
      ':producto' => $variante->getProducto()?->getId(),
      ':sku' => $variante->getSku(),
      ':talla' => $variante->getTalla(),
      ':color' => $variante->getColor(),
      ':genero' => $variante->getGenero(),
      ':precio' => $variante->getPrecio(),
      ':estado' => $variante->getEstado()?->getId(),
    ];
  }

  private function map(array $row): ProductoVariante
  {
    return new ProductoVariante($row['id'], $row['producto'], $row['sku'], $row['talla'] ?? null, $row['color'] ?? null, $row['genero'] ?? null, (float) $row['precio'], $row['estado']);
  }
}
