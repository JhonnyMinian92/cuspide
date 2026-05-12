<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/MovimientoInventario.php';

use Core\Database;
use Entitys\MovimientoInventario;
use PDO;

class MovimientoInventarioRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(MovimientoInventario $movimiento): MovimientoInventario
  {
    if ($movimiento->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO movimientos_inventario (producto, variante, institucion, tipo, cantidad, stock_anterior, stock_nuevo, referencia, fecha, observacion) VALUES (:producto, :variante, :institucion, :tipo, :cantidad, :stock_anterior, :stock_nuevo, :referencia, :fecha, :observacion)");
      $stmt->execute($this->params($movimiento));
      $movimiento->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE movimientos_inventario SET producto = :producto, variante = :variante, institucion = :institucion, tipo = :tipo, cantidad = :cantidad, stock_anterior = :stock_anterior, stock_nuevo = :stock_nuevo, referencia = :referencia, fecha = :fecha, observacion = :observacion WHERE id = :id");
      $params = $this->params($movimiento);
      $params[':id'] = $movimiento->getId();
      $stmt->execute($params);
    }
    return $movimiento;
  }

  public function findById(int $id): ?MovimientoInventario
  {
    $stmt = $this->db->prepare('SELECT * FROM movimientos_inventario WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByProducto(int $productoId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM movimientos_inventario WHERE producto = :producto ORDER BY fecha DESC');
    $stmt->execute([':producto' => $productoId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(MovimientoInventario $movimiento): array
  {
    return [
      ':producto' => $movimiento->getProducto()?->getId(),
      ':variante' => $movimiento->getVariante()?->getId(),
      ':institucion' => $movimiento->getInstitucion()?->getId(),
      ':tipo' => $movimiento->getTipo(),
      ':cantidad' => $movimiento->getCantidad(),
      ':stock_anterior' => $movimiento->getStockAnterior(),
      ':stock_nuevo' => $movimiento->getStockNuevo(),
      ':referencia' => $movimiento->getReferencia(),
      ':fecha' => $movimiento->getFecha(),
      ':observacion' => $movimiento->getObservacion(),
    ];
  }

  private function map(array $row): MovimientoInventario
  {
    return new MovimientoInventario($row['id'], $row['producto'], $row['variante'] ?? null, $row['institucion'], $row['tipo'], (float) $row['cantidad'], (float) $row['stock_anterior'], (float) $row['stock_nuevo'], $row['referencia'] ?? null, $row['fecha'], $row['observacion']);
  }
}
