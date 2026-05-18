<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Gasto.php';

use Core\Database;
use Entitys\Gasto;
use PDO;

class GastoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Gasto $gasto): Gasto
  {
    if ($gasto->getId() === null) {
      $stmt = $this->db->prepare(
        "INSERT INTO gastos (numero, fecha, descripcion, tipo_gasto, monto, institucion, persona, referencia, observacion, estado)
         VALUES (:numero, :fecha, :descripcion, :tipo_gasto, :monto, :institucion, :persona, :referencia, :observacion, :estado)"
      );
      $stmt->execute($this->params($gasto));
      $gasto->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare(
        "UPDATE gastos SET numero = :numero, fecha = :fecha, descripcion = :descripcion, tipo_gasto = :tipo_gasto,
         monto = :monto, institucion = :institucion, persona = :persona, referencia = :referencia,
         observacion = :observacion, estado = :estado WHERE id = :id"
      );
      $params = $this->params($gasto);
      $params[':id'] = $gasto->getId();
      $stmt->execute($params);
    }
    return $gasto;
  }

  public function findById(int $id): ?Gasto
  {
    $stmt = $this->db->prepare('SELECT * FROM gastos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByInstitucion(int $institucionId, int $limit = 50): array
  {
    $stmt = $this->db->prepare(
      "SELECT g.*,
              CONCAT(COALESCE(p.primer_nombre,''),' ',COALESCE(p.primer_apellido,'')) AS nombre_persona
       FROM gastos g
       LEFT JOIN personas p ON p.id = g.persona
       WHERE g.institucion = :inst AND g.estado = 1
       ORDER BY g.fecha DESC
       LIMIT :lim"
    );
    $stmt->bindValue(':inst', $institucionId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  public function sumByInstitucionAndMonth(int $institucionId, int $year, int $month): float
  {
    $stmt = $this->db->prepare(
      "SELECT COALESCE(SUM(monto), 0) AS total
       FROM gastos
       WHERE institucion = :inst AND estado = 1
         AND YEAR(fecha) = :year AND MONTH(fecha) = :month"
    );
    $stmt->execute([':inst' => $institucionId, ':year' => $year, ':month' => $month]);
    return (float) $stmt->fetchColumn();
  }

  private function params(Gasto $g): array
  {
    return [
      ':numero'      => $g->getNumero(),
      ':fecha'       => $g->getFecha(),
      ':descripcion' => $g->getDescripcion(),
      ':tipo_gasto'  => $g->getTipoGasto(),
      ':monto'       => $g->getMonto(),
      ':institucion' => $g->getInstitucion()?->getId(),
      ':persona'     => $g->getPersonaId(),
      ':referencia'  => $g->getReferencia(),
      ':observacion' => $g->getObservacion(),
      ':estado'      => $g->getEstado()?->getId() ?? 1,
    ];
  }

  private function map(array $row): Gasto
  {
    return new Gasto(
      $row['id'], $row['numero'], $row['fecha'], $row['descripcion'],
      $row['tipo_gasto'], (float) $row['monto'], $row['institucion'],
      $row['persona'] ?? null, $row['referencia'] ?? null, $row['observacion'] ?? null, $row['estado']
    );
  }
}
