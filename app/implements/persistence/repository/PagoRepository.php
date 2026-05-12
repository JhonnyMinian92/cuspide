<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Pago.php';

use Core\Database;
use Entitys\Pago;
use PDO;

class PagoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Pago $pago): Pago
  {
    if ($pago->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO pagos (numero, fecha, persona, matricula, venta, forma_pago, institucion, monto, referencia, observacion, estado) VALUES (:numero, :fecha, :persona, :matricula, :venta, :forma_pago, :institucion, :monto, :referencia, :observacion, :estado)");
      $stmt->execute($this->params($pago));
      $pago->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE pagos SET numero = :numero, fecha = :fecha, persona = :persona, matricula = :matricula, venta = :venta, forma_pago = :forma_pago, institucion = :institucion, monto = :monto, referencia = :referencia, observacion = :observacion, estado = :estado WHERE id = :id");
      $params = $this->params($pago);
      $params[':id'] = $pago->getId();
      $stmt->execute($params);
    }
    return $pago;
  }

  public function findById(int $id): ?Pago
  {
    $stmt = $this->db->prepare('SELECT * FROM pagos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByNumero(string $numero): ?Pago
  {
    $stmt = $this->db->prepare('SELECT * FROM pagos WHERE numero = :numero');
    $stmt->execute([':numero' => $numero]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByPersona(int $personaId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM pagos WHERE persona = :persona ORDER BY fecha DESC');
    $stmt->execute([':persona' => $personaId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(Pago $pago): array
  {
    return [
      ':numero' => $pago->getNumero(),
      ':fecha' => $pago->getFecha(),
      ':persona' => $pago->getPersona()?->getId(),
      ':matricula' => $pago->getMatricula()?->getId(),
      ':venta' => $pago->getVenta()?->getId(),
      ':forma_pago' => $pago->getFormaPago()?->getId(),
      ':institucion' => $pago->getInstitucion()?->getId(),
      ':monto' => $pago->getMonto(),
      ':referencia' => $pago->getReferencia(),
      ':observacion' => $pago->getObservacion(),
      ':estado' => $pago->getEstado()?->getId(),
    ];
  }

  private function map(array $row): Pago
  {
    return new Pago($row['id'], $row['numero'], $row['fecha'], $row['persona'], $row['matricula'] ?? null, $row['venta'] ?? null, $row['forma_pago'], $row['institucion'], (float) $row['monto'], $row['referencia'] ?? null, $row['observacion'], $row['estado']);
  }
}
