<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Evaluacion.php';

use Core\Database;
use Entitys\Evaluacion;
use PDO;

class EvaluacionRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Evaluacion $evaluacion): Evaluacion
  {
    if ($evaluacion->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO evaluaciones (nombre, clase, actividad, fecha, peso_porcentaje, estado) VALUES (:nombre, :clase, :actividad, :fecha, :peso_porcentaje, :estado)");
      $stmt->execute([
        ':nombre' => $evaluacion->getNombre(),
        ':clase' => $evaluacion->getClase()?->getId(),
        ':actividad' => $evaluacion->getActividad()?->getId(),
        ':fecha' => $evaluacion->getFecha(),
        ':peso_porcentaje' => $evaluacion->getPesoPorcentaje(),
        ':estado' => $evaluacion->getEstado()?->getId(),
      ]);
      $evaluacion->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE evaluaciones SET nombre = :nombre, clase = :clase, actividad = :actividad, fecha = :fecha, peso_porcentaje = :peso_porcentaje, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $evaluacion->getId(),
        ':nombre' => $evaluacion->getNombre(),
        ':clase' => $evaluacion->getClase()?->getId(),
        ':actividad' => $evaluacion->getActividad()?->getId(),
        ':fecha' => $evaluacion->getFecha(),
        ':peso_porcentaje' => $evaluacion->getPesoPorcentaje(),
        ':estado' => $evaluacion->getEstado()?->getId(),
      ]);
    }
    return $evaluacion;
  }

  public function findById(int $id): ?Evaluacion
  {
    $stmt = $this->db->prepare('SELECT * FROM evaluaciones WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? new Evaluacion($row['id'], $row['nombre'], $row['clase'], $row['actividad'], $row['fecha'], $row['peso_porcentaje'], $row['estado']) : null;
  }
}
