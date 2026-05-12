<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Calificacion.php';

use Core\Database;
use Entitys\Calificacion;
use PDO;

class CalificacionRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Calificacion $calificacion): Calificacion
  {
    if ($calificacion->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO calificaciones (evaluacion, matricula, nota, observacion, estado) VALUES (:evaluacion, :matricula, :nota, :observacion, :estado)");
      $stmt->execute([
        ':evaluacion' => $calificacion->getEvaluacion()?->getId(),
        ':matricula' => $calificacion->getMatricula()?->getId(),
        ':nota' => $calificacion->getNota(),
        ':observacion' => $calificacion->getObservacion(),
        ':estado' => $calificacion->getEstado()?->getId(),
      ]);
      $calificacion->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE calificaciones SET evaluacion = :evaluacion, matricula = :matricula, nota = :nota, observacion = :observacion, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $calificacion->getId(),
        ':evaluacion' => $calificacion->getEvaluacion()?->getId(),
        ':matricula' => $calificacion->getMatricula()?->getId(),
        ':nota' => $calificacion->getNota(),
        ':observacion' => $calificacion->getObservacion(),
        ':estado' => $calificacion->getEstado()?->getId(),
      ]);
    }
    return $calificacion;
  }

  public function findById(int $id): ?Calificacion
  {
    $stmt = $this->db->prepare('SELECT * FROM calificaciones WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? new Calificacion($row['id'], $row['evaluacion'], $row['matricula'], $row['nota'], $row['observacion'], $row['estado']) : null;
  }
}
