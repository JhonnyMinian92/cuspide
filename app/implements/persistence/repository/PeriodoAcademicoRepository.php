<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/PeriodoAcademico.php';

use Core\Database;
use Entitys\PeriodoAcademico;
use PDO;

class PeriodoAcademicoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(PeriodoAcademico $periodo): PeriodoAcademico
  {
    if ($periodo->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO periodos_academicos (nombre, fecha_inicio, fecha_fin, institucion, estado) VALUES (:nombre, :fecha_inicio, :fecha_fin, :institucion, :estado)");
      $stmt->execute([
        ':nombre' => $periodo->getNombre(),
        ':fecha_inicio' => $periodo->getFechaInicio(),
        ':fecha_fin' => $periodo->getFechaFin(),
        ':institucion' => $periodo->getInstitucion()?->getId(),
        ':estado' => $periodo->getEstado()?->getId(),
      ]);
      $periodo->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE periodos_academicos SET nombre = :nombre, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, institucion = :institucion, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $periodo->getId(),
        ':nombre' => $periodo->getNombre(),
        ':fecha_inicio' => $periodo->getFechaInicio(),
        ':fecha_fin' => $periodo->getFechaFin(),
        ':institucion' => $periodo->getInstitucion()?->getId(),
        ':estado' => $periodo->getEstado()?->getId(),
      ]);
    }
    return $periodo;
  }

  public function findById(int $id): ?PeriodoAcademico
  {
    $stmt = $this->db->prepare('SELECT * FROM periodos_academicos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? new PeriodoAcademico($row['id'], $row['nombre'], $row['fecha_inicio'], $row['fecha_fin'], $row['institucion'], $row['estado']) : null;
  }

  public function findByInstitucion(int $institucionId): array
  {
    $stmt = $this->db->prepare(
      'SELECT * FROM periodos_academicos WHERE institucion = :inst AND estado = 1 ORDER BY fecha_inicio DESC'
    );
    $stmt->execute([':inst' => $institucionId]);
    return array_map(fn($r) => new PeriodoAcademico($r['id'], $r['nombre'], $r['fecha_inicio'], $r['fecha_fin'], $r['institucion'], $r['estado']),
      $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }

  public function findActualByInstitucion(int $institucionId): ?PeriodoAcademico
  {
    $stmt = $this->db->prepare(
      "SELECT * FROM periodos_academicos WHERE institucion = :inst AND estado = 1
       AND fecha_inicio <= CURDATE() AND fecha_fin >= CURDATE()
       ORDER BY fecha_inicio DESC LIMIT 1"
    );
    $stmt->execute([':inst' => $institucionId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? new PeriodoAcademico($row['id'], $row['nombre'], $row['fecha_inicio'], $row['fecha_fin'], $row['institucion'], $row['estado']) : null;
  }
}
