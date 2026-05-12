<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/PlanPagoMatricula.php';

use Core\Database;
use Entitys\PlanPagoMatricula;
use PDO;

class PlanPagoMatriculaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(PlanPagoMatricula $plan): PlanPagoMatricula
  {
    if ($plan->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO plan_pago_matricula (matricula, periodicidad, subtotal, descuento, total, saldo, fecha_generacion, estado) VALUES (:matricula, :periodicidad, :subtotal, :descuento, :total, :saldo, :fecha_generacion, :estado)");
      $stmt->execute($this->params($plan));
      $plan->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE plan_pago_matricula SET matricula = :matricula, periodicidad = :periodicidad, subtotal = :subtotal, descuento = :descuento, total = :total, saldo = :saldo, fecha_generacion = :fecha_generacion, estado = :estado WHERE id = :id");
      $params = $this->params($plan);
      $params[':id'] = $plan->getId();
      $stmt->execute($params);
    }
    return $plan;
  }

  public function findById(int $id): ?PlanPagoMatricula
  {
    $stmt = $this->db->prepare('SELECT * FROM plan_pago_matricula WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByMatricula(int $matriculaId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM plan_pago_matricula WHERE matricula = :matricula ORDER BY fecha_generacion DESC');
    $stmt->execute([':matricula' => $matriculaId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(PlanPagoMatricula $plan): array
  {
    return [
      ':matricula' => $plan->getMatricula()?->getId(),
      ':periodicidad' => $plan->getPeriodicidad()?->getId(),
      ':subtotal' => $plan->getSubtotal(),
      ':descuento' => $plan->getDescuento(),
      ':total' => $plan->getTotal(),
      ':saldo' => $plan->getSaldo(),
      ':fecha_generacion' => $plan->getFechaGeneracion(),
      ':estado' => $plan->getEstado()?->getId(),
    ];
  }

  private function map(array $row): PlanPagoMatricula
  {
    return new PlanPagoMatricula($row['id'], $row['matricula'], $row['periodicidad'], (float) $row['subtotal'], (float) $row['descuento'], (float) $row['total'], (float) $row['saldo'], $row['fecha_generacion'], $row['estado']);
  }
}
