<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/CuotaMatricula.php';

use Core\Database;
use Entitys\CuotaMatricula;
use PDO;

class CuotaMatriculaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(CuotaMatricula $cuota): CuotaMatricula
  {
    if ($cuota->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO cuotas_matricula (plan_pago, numero, fecha_vencimiento, valor, saldo, estado) VALUES (:plan_pago, :numero, :fecha_vencimiento, :valor, :saldo, :estado)");
      $stmt->execute($this->params($cuota));
      $cuota->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE cuotas_matricula SET plan_pago = :plan_pago, numero = :numero, fecha_vencimiento = :fecha_vencimiento, valor = :valor, saldo = :saldo, estado = :estado WHERE id = :id");
      $params = $this->params($cuota);
      $params[':id'] = $cuota->getId();
      $stmt->execute($params);
    }
    return $cuota;
  }

  public function findById(int $id): ?CuotaMatricula
  {
    $stmt = $this->db->prepare('SELECT * FROM cuotas_matricula WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByPlanPago(int $planPagoId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM cuotas_matricula WHERE plan_pago = :plan_pago ORDER BY numero ASC');
    $stmt->execute([':plan_pago' => $planPagoId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(CuotaMatricula $cuota): array
  {
    return [
      ':plan_pago' => $cuota->getPlanPago()?->getId(),
      ':numero' => $cuota->getNumero(),
      ':fecha_vencimiento' => $cuota->getFechaVencimiento(),
      ':valor' => $cuota->getValor(),
      ':saldo' => $cuota->getSaldo(),
      ':estado' => $cuota->getEstado()?->getId(),
    ];
  }

  private function map(array $row): CuotaMatricula
  {
    return new CuotaMatricula($row['id'], $row['plan_pago'], (int) $row['numero'], $row['fecha_vencimiento'], (float) $row['valor'], (float) $row['saldo'], $row['estado']);
  }
}
