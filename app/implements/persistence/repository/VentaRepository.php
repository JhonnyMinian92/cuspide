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

  public function findByInstitucion(int $institucionId, array $filters): array
  {
    $limit = (int) ($filters['limit'] ?? 20);
    $page = (int) ($filters['page'] ?? 1);
    $offset = ($page - 1) * $limit;
    $desde = $filters['desde'] ?? null;
    $hasta = $filters['hasta'] ?? null;
    $q = $filters['q'] ?? null;

    $whereClauses = ['v.institucion = :inst'];
    $params = [':inst' => $institucionId];

    if ($desde !== null) {
      $whereClauses[] = 'v.fecha >= :desde';
      $params[':desde'] = $desde;
    }
    if ($hasta !== null) {
      $whereClauses[] = 'v.fecha <= :hasta_end';
      $params[':hasta_end'] = $hasta . ' 23:59:59';
    }
    if ($q) {
      $whereClauses[] = "(v.numero LIKE :q OR p.identificacion LIKE :q OR CONCAT(p.primer_nombre, ' ', p.primer_apellido) LIKE :q)";
      $params[':q'] = "%{$q}%";
    }

    $whereSql = implode(' AND ', $whereClauses);

    $countSql = "SELECT COUNT(v.id) FROM ventas v JOIN personas p ON p.id = v.persona WHERE $whereSql";
    $countStmt = $this->db->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = (int) $countStmt->fetchColumn();

    $itemsSql = "SELECT v.id, v.numero, v.fecha, v.tipo, v.subtotal, v.descuento, v.total, v.saldo, v.estado,
                   p.identificacion,
                   CONCAT(p.primer_nombre,' ',p.primer_apellido) AS nombre
            FROM ventas v
            JOIN personas p ON p.id = v.persona
            WHERE $whereSql
            ORDER BY v.fecha DESC, v.id DESC LIMIT :lim OFFSET :off";
    $stmt = $this->db->prepare($itemsSql);
    foreach ($params as $k => $v) {
      $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

    return ['items' => $items, 'total' => $totalItems];
  }

  public function findPendientesByInstitucion(int $institucionId): array
  {
    $stmt = $this->db->prepare(
      "SELECT v.id, v.numero, v.fecha, v.total, v.saldo, v.tipo,
              p.identificacion,
              CONCAT(p.primer_nombre,' ',p.primer_apellido) AS nombre
       FROM ventas v
       JOIN personas p ON p.id = v.persona
       WHERE v.saldo > 0
         AND v.estado = 1
         AND v.institucion = :inst
       ORDER BY v.fecha ASC"
    );
    $stmt->execute([':inst' => $institucionId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
  }

  public function findPendientesByPersona(int $personaId): array
  {
    $stmt = $this->db->prepare(
      'SELECT id, saldo FROM ventas WHERE persona = :persona AND saldo > 0 AND estado = 1 ORDER BY fecha ASC'
    );
    $stmt->execute([':persona' => $personaId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
  }

  public function updateSaldo(int $id, float $saldo, bool $pagada): void
  {
    if ($pagada) {
      $stmt = $this->db->prepare('UPDATE ventas SET saldo = 0, estado = 2 WHERE id = :id');
      $stmt->execute([':id' => $id]);
    } else {
      $stmt = $this->db->prepare('UPDATE ventas SET saldo = :saldo WHERE id = :id');
      $stmt->execute([':saldo' => $saldo, ':id' => $id]);
    }
  }

  public function cancel(int $id): void
  {
    // Also sets saldo to 0, which is logical for a cancelled sale.
    $stmt = $this->db->prepare('UPDATE ventas SET estado = 2, saldo = 0 WHERE id = :id');
    $stmt->execute([':id' => $id]);
  }

  public function findCuentasCobrar(int $institucionId, ?int $personaId = null): array
  {
    $sql = "SELECT v.id, v.numero, v.fecha, v.tipo, v.total, v.saldo,
                   p.identificacion,
                   CONCAT(p.primer_nombre,' ',p.primer_apellido) AS nombre_cliente,
                   DATEDIFF(CURDATE(), v.fecha) AS dias_vencido
            FROM ventas v
            JOIN personas p ON p.id = v.persona
            WHERE v.saldo > 0
              AND v.estado = 1
              AND v.institucion = :inst";
    $params = [':inst' => $institucionId];
    if ($personaId !== null) {
      $sql .= ' AND v.persona = :pid';
      $params[':pid'] = $personaId;
    }
    $sql .= ' ORDER BY v.fecha ASC';
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
  }
}
