<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Asignatura.php';

use Core\Database;
use Entitys\Asignatura;
use PDO;

class AsignaturaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Asignatura $asignatura): Asignatura
  {
    if ($asignatura->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO asignaturas (nombre, descripcion, institucion, estado) VALUES (:nombre, :descripcion, :institucion, :estado)");
      $stmt->execute([
        ':nombre' => $asignatura->getNombre(),
        ':descripcion' => $asignatura->getDescripcion(),
        ':institucion' => $asignatura->getInstitucion()?->getId(),
        ':estado' => $asignatura->getEstado()?->getId(),
      ]);
      $asignatura->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE asignaturas SET nombre = :nombre, descripcion = :descripcion, institucion = :institucion, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $asignatura->getId(),
        ':nombre' => $asignatura->getNombre(),
        ':descripcion' => $asignatura->getDescripcion(),
        ':institucion' => $asignatura->getInstitucion()?->getId(),
        ':estado' => $asignatura->getEstado()?->getId(),
      ]);
    }

    return $asignatura;
  }

  public function findById(int $id): ?Asignatura
  {
    $stmt = $this->db->prepare('SELECT * FROM asignaturas WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Asignatura($row['id'], $row['nombre'], $row['descripcion'], $row['institucion'], $row['estado']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM asignaturas ORDER BY nombre ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Asignatura($row['id'], $row['nombre'], $row['descripcion'], $row['institucion'], $row['estado']), $rows ?: []);
  }
}
