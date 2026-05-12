<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/PeriodicidadPago.php';

use Core\Database;
use Entitys\PeriodicidadPago;
use PDO;

class PeriodicidadPagoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(PeriodicidadPago $periodicidad): PeriodicidadPago
  {
    if ($periodicidad->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO periodicidad_pago (nombre, nemonico, meses, estado) VALUES (:nombre, :nemonico, :meses, :estado)");
      $stmt->execute($this->params($periodicidad));
      $periodicidad->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE periodicidad_pago SET nombre = :nombre, nemonico = :nemonico, meses = :meses, estado = :estado WHERE id = :id");
      $params = $this->params($periodicidad);
      $params[':id'] = $periodicidad->getId();
      $stmt->execute($params);
    }
    return $periodicidad;
  }

  public function findById(int $id): ?PeriodicidadPago
  {
    $stmt = $this->db->prepare('SELECT * FROM periodicidad_pago WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM periodicidad_pago ORDER BY meses ASC');
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(PeriodicidadPago $periodicidad): array
  {
    return [
      ':nombre' => $periodicidad->getNombre(),
      ':nemonico' => $periodicidad->getNemonico(),
      ':meses' => $periodicidad->getMeses(),
      ':estado' => $periodicidad->getEstado()?->getId(),
    ];
  }

  private function map(array $row): PeriodicidadPago
  {
    return new PeriodicidadPago($row['id'], $row['nombre'], $row['nemonico'], (int) $row['meses'], $row['estado']);
  }
}
