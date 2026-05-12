<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Incidencia.php';

use Core\Database;
use Entitys\Incidencia;
use PDO;

class IncidenciaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Incidencia $incidencia): Incidencia
  {
    if ($incidencia->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO incidencia (fecha, persona, nivel, descripcion) VALUES (:fecha, :persona, :nivel, :descripcion)");
      $stmt->execute([
        ':fecha' => $incidencia->getFecha(),
        ':persona' => $incidencia->getPersona()?->getId(),
        ':nivel' => $incidencia->getNivel()?->getId(),
        ':descripcion' => $incidencia->getDescripcion(),
      ]);
      $incidencia->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE incidencia SET fecha = :fecha, persona = :persona, nivel = :nivel, descripcion = :descripcion WHERE id = :id");
      $stmt->execute([
        ':id' => $incidencia->getId(),
        ':fecha' => $incidencia->getFecha(),
        ':persona' => $incidencia->getPersona()?->getId(),
        ':nivel' => $incidencia->getNivel()?->getId(),
        ':descripcion' => $incidencia->getDescripcion(),
      ]);
    }

    return $incidencia;
  }

  public function findById(int $id): ?Incidencia
  {
    $stmt = $this->db->prepare('SELECT * FROM incidencia WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Incidencia($row['id'], $row['fecha'], $row['persona'], $row['nivel'], $row['descripcion']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM incidencia ORDER BY fecha DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Incidencia($row['id'], $row['fecha'], $row['persona'], $row['nivel'], $row['descripcion']), $rows ?: []);
  }
}
