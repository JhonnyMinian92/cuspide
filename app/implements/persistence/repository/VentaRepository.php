<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Venta.php';

use Core\Database;
use Entitys\Venta;
use PDO;

class VentaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Venta $venta): Venta
  {
    if ($venta->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO ventas (numero, fecha, persona, matricula, institucion, tipo, subtotal, descuento, impuesto, total, saldo, estado) VALUES (:numero, :fecha, :persona, :matricula, :institucion, :tipo, :subtotal, :descuento, :impuesto, :total, :saldo, :estado)");
      $stmt->execute($this->params($venta));
      $venta->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE ventas SET numero = :numero, fecha = :fecha, persona = :persona, matricula = :matricula, institucion = :institucion, tipo = :tipo, subtotal = :subtotal, descuento = :descuento, impuesto = :impuesto, total = :total, saldo = :saldo, estado = :estado WHERE id = :id");
      $params = $this->params($venta);
      $params[':id'] = $venta->getId();
      $stmt->execute($params);
    }
    return $venta;
  }

  public function findById(int $id): ?Venta
  {
    $stmt = $this->db->prepare('SELECT * FROM ventas WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByNumero(string $numero): ?Venta
  {
    $stmt = $this->db->prepare('SELECT * FROM ventas WHERE numero = :numero');
    $stmt->execute([':numero' => $numero]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByPersona(int $personaId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM ventas WHERE persona = :persona ORDER BY fecha DESC');
    $stmt->execute([':persona' => $personaId]);
    return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  private function params(Venta $venta): array
  {
    return [
      ':numero' => $venta->getNumero(),
      ':fecha' => $venta->getFecha(),
      ':persona' => $venta->getPersona()?->getId(),
      ':matricula' => $venta->getMatricula()?->getId(),
      ':institucion' => $venta->getInstitucion()?->getId(),
      ':tipo' => $venta->getTipo(),
      ':subtotal' => $venta->getSubtotal(),
      ':descuento' => $venta->getDescuento(),
      ':impuesto' => $venta->getImpuesto(),
      ':total' => $venta->getTotal(),
      ':saldo' => $venta->getSaldo(),
      ':estado' => $venta->getEstado()?->getId(),
    ];
  }

  private function map(array $row): Venta
  {
    return new Venta($row['id'], $row['numero'], $row['fecha'], $row['persona'], $row['matricula'] ?? null, $row['institucion'], $row['tipo'], (float) $row['subtotal'], (float) $row['descuento'], (float) $row['impuesto'], (float) $row['total'], (float) $row['saldo'], $row['estado']);
  }
}
