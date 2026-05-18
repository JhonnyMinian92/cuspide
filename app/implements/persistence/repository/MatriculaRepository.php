<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Matricula.php';

use Core\Database;
use Entitys\Matricula;
use PDO;

class MatriculaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Matricula $matricula): Matricula
  {
    if ($matricula->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO matricula (fecha_registro, persona, fecha_inicio, fecha_fin, membresia, institucion, estado) VALUES (:fecha_registro, :persona, :fecha_inicio, :fecha_fin, :membresia, :institucion, :estado)");
      $stmt->execute([
        ':fecha_registro' => $matricula->getFechaRegistro(),
        ':persona' => $matricula->getPersona()?->getId(),
        ':fecha_inicio' => $matricula->getFechaInicio(),
        ':fecha_fin' => $matricula->getFechaFin(),
        ':membresia' => $matricula->getMembresia()?->getId(),
        ':institucion' => $matricula->getInstitucion()?->getId(),
        ':estado' => $matricula->getEstado()?->getId(),
      ]);
      $matricula->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE matricula SET fecha_registro = :fecha_registro, persona = :persona, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, membresia = :membresia, institucion = :institucion, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $matricula->getId(),
        ':fecha_registro' => $matricula->getFechaRegistro(),
        ':persona' => $matricula->getPersona()?->getId(),
        ':fecha_inicio' => $matricula->getFechaInicio(),
        ':fecha_fin' => $matricula->getFechaFin(),
        ':membresia' => $matricula->getMembresia()?->getId(),
        ':institucion' => $matricula->getInstitucion()?->getId(),
        ':estado' => $matricula->getEstado()?->getId(),
      ]);
    }

    return $matricula;
  }

  public function findById(int $id): ?Matricula
  {
    $stmt = $this->db->prepare('SELECT * FROM matricula WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Matricula($row['id'], $row['fecha_registro'], $row['persona'], $row['fecha_inicio'], $row['fecha_fin'], $row['membresia'], $row['institucion'], $row['estado']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM matricula ORDER BY fecha_registro DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Matricula($row['id'], $row['fecha_registro'], $row['persona'], $row['fecha_inicio'], $row['fecha_fin'], $row['membresia'], $row['institucion'], $row['estado']), $rows ?: []);
  }

  public function findByPersonaId(int $personaId): array
  {
    $stmt = $this->db->prepare(
      "SELECT m.id, m.fecha_registro, m.fecha_inicio, m.fecha_fin,
              m.membresia AS membresia_id,
              mem.titulo  AS membresia,
              i.nombre    AS institucion,
              e.nombre    AS estado
       FROM matricula m
       LEFT JOIN membresias mem ON mem.id = m.membresia
       JOIN instituciones   i   ON i.id   = m.institucion
       JOIN estados         e   ON e.id   = m.estado
       WHERE m.persona = :pid
       ORDER BY m.fecha_registro DESC"
    );
    $stmt->execute([':pid' => $personaId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
  }

  public function disable(int $id): void
  {
    $stmt = $this->db->prepare('UPDATE matricula SET estado = 2 WHERE id = :id');
    $stmt->execute([':id' => $id]);
  }
}
