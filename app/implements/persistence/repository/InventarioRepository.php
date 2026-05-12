<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Inventario.php';

use Core\Database;
use Entitys\Inventario;
use PDO;

class InventarioRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Inventario $inventario): Inventario
  {
    if ($inventario->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO inventario (producto, variante, institucion, stock_actual, stock_minimo, fecha_actualizacion) VALUES (:producto, :variante, :institucion, :stock_actual, :stock_minimo, :fecha_actualizacion)");
      $stmt->execute($this->params($inventario));
      $inventario->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE inventario SET producto = :producto, variante = :variante, institucion = :institucion, stock_actual = :stock_actual, stock_minimo = :stock_minimo, fecha_actualizacion = :fecha_actualizacion WHERE id = :id");
      $params = $this->params($inventario);
      $params[':id'] = $inventario->getId();
      $stmt->execute($params);
    }
    return $inventario;
  }

  public function findById(int $id): ?Inventario
  {
    $stmt = $this->db->prepare('SELECT * FROM inventario WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByInstitucion(int $institucionId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM inventario WHERE institucion = :institucion ORDER BY producto ASC');
    $stmt->execute([':institucion' => $institucionId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(Inventario $inventario): array
  {
    return [
      ':producto' => $inventario->getProducto()?->getId(),
      ':variante' => $inventario->getVariante()?->getId(),
      ':institucion' => $inventario->getInstitucion()?->getId(),
      ':stock_actual' => $inventario->getStockActual(),
      ':stock_minimo' => $inventario->getStockMinimo(),
      ':fecha_actualizacion' => $inventario->getFechaActualizacion(),
    ];
  }

  private function map(array $row): Inventario
  {
    return new Inventario($row['id'], $row['producto'], $row['variante'] ?? null, $row['institucion'], (float) $row['stock_actual'], (float) $row['stock_minimo'], $row['fecha_actualizacion']);
  }
}
