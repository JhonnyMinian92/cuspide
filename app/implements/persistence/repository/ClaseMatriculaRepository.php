<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/ClaseMatricula.php';

use Core\Database;
use Entitys\ClaseMatricula;
use PDO;

class ClaseMatriculaRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(ClaseMatricula $claseMatricula): ClaseMatricula
  {
    if ($claseMatricula->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO clases_matricula (clase, matricula, modalidad, fecha_inicio, fecha_fin, estado) VALUES (:clase, :matricula, :modalidad, :fecha_inicio, :fecha_fin, :estado)");
      $stmt->execute([
        ':clase' => $claseMatricula->getClase()?->getId(),
        ':matricula' => $claseMatricula->getMatricula()?->getId(),
        ':modalidad' => $claseMatricula->getModalidad()?->getId(),
        ':fecha_inicio' => $claseMatricula->getFechaInicio(),
        ':fecha_fin' => $claseMatricula->getFechaFin(),
        ':estado' => $claseMatricula->getEstado()?->getId(),
      ]);
      $claseMatricula->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE clases_matricula SET clase = :clase, matricula = :matricula, modalidad = :modalidad, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $claseMatricula->getId(),
        ':clase' => $claseMatricula->getClase()?->getId(),
        ':matricula' => $claseMatricula->getMatricula()?->getId(),
        ':modalidad' => $claseMatricula->getModalidad()?->getId(),
        ':fecha_inicio' => $claseMatricula->getFechaInicio(),
        ':fecha_fin' => $claseMatricula->getFechaFin(),
        ':estado' => $claseMatricula->getEstado()?->getId(),
      ]);
    }

    return $claseMatricula;
  }

  public function findById(int $id): ?ClaseMatricula
  {
    $stmt = $this->db->prepare('SELECT * FROM clases_matricula WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new ClaseMatricula($row['id'], $row['clase'], $row['matricula'], $row['modalidad'], $row['fecha_inicio'], $row['fecha_fin'], $row['estado']) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM clases_matricula ORDER BY fecha_inicio DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new ClaseMatricula($row['id'], $row['clase'], $row['matricula'], $row['modalidad'], $row['fecha_inicio'], $row['fecha_fin'], $row['estado']), $rows ?: []);
  }
}
