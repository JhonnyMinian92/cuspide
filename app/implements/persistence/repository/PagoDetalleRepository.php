<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/PagoDetalle.php';

use Core\Database;
use Entitys\PagoDetalle;
use PDO;

class PagoDetalleRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(PagoDetalle $detalle): PagoDetalle
  {
    if ($detalle->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO pago_detalle (pago, cuota_matricula, venta, valor) VALUES (:pago, :cuota_matricula, :venta, :valor)");
      $stmt->execute($this->params($detalle));
      $detalle->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE pago_detalle SET pago = :pago, cuota_matricula = :cuota_matricula, venta = :venta, valor = :valor WHERE id = :id");
      $params = $this->params($detalle);
      $params[':id'] = $detalle->getId();
      $stmt->execute($params);
    }
    return $detalle;
  }

  public function findById(int $id): ?PagoDetalle
  {
    $stmt = $this->db->prepare('SELECT * FROM pago_detalle WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByPago(int $pagoId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM pago_detalle WHERE pago = :pago ORDER BY id ASC');
    $stmt->execute([':pago' => $pagoId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(PagoDetalle $detalle): array
  {
    return [
      ':pago' => $detalle->getPago()?->getId(),
      ':cuota_matricula' => $detalle->getCuotaMatricula()?->getId(),
      ':venta' => $detalle->getVenta()?->getId(),
      ':valor' => $detalle->getValor(),
    ];
  }

  private function map(array $row): PagoDetalle
  {
    return new PagoDetalle($row['id'], $row['pago'], $row['cuota_matricula'] ?? null, $row['venta'] ?? null, (float) $row['valor']);
  }
}
