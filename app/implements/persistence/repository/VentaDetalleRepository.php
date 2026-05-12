<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/VentaDetalle.php';

use Core\Database;
use Entitys\VentaDetalle;
use PDO;

class VentaDetalleRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(VentaDetalle $detalle): VentaDetalle
  {
    if ($detalle->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO venta_detalle (venta, producto, variante, cantidad, precio_unitario, descuento, total) VALUES (:venta, :producto, :variante, :cantidad, :precio_unitario, :descuento, :total)");
      $stmt->execute($this->params($detalle));
      $detalle->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE venta_detalle SET venta = :venta, producto = :producto, variante = :variante, cantidad = :cantidad, precio_unitario = :precio_unitario, descuento = :descuento, total = :total WHERE id = :id");
      $params = $this->params($detalle);
      $params[':id'] = $detalle->getId();
      $stmt->execute($params);
    }
    return $detalle;
  }

  public function findById(int $id): ?VentaDetalle
  {
    $stmt = $this->db->prepare('SELECT * FROM venta_detalle WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByVenta(int $ventaId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM venta_detalle WHERE venta = :venta ORDER BY id ASC');
    $stmt->execute([':venta' => $ventaId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(VentaDetalle $detalle): array
  {
    return [
      ':venta' => $detalle->getVenta()?->getId(),
      ':producto' => $detalle->getProducto()?->getId(),
      ':variante' => $detalle->getVariante()?->getId(),
      ':cantidad' => $detalle->getCantidad(),
      ':precio_unitario' => $detalle->getPrecioUnitario(),
      ':descuento' => $detalle->getDescuento(),
      ':total' => $detalle->getTotal(),
    ];
  }

  private function map(array $row): VentaDetalle
  {
    return new VentaDetalle($row['id'], $row['venta'], $row['producto'], $row['variante'] ?? null, (float) $row['cantidad'], (float) $row['precio_unitario'], (float) $row['descuento'], (float) $row['total']);
  }
}
