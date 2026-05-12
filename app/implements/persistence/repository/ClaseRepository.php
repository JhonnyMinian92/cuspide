<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Clase.php';

use Core\Database;
use Entitys\Clase;
use PDO;

class ClaseRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Clase $clase): Clase
  {
    if ($clase->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO clases (nombre, periodo_academico, institucion, docente, cupo_maximo, estado) VALUES (:nombre, :periodo_academico, :institucion, :docente, :cupo_maximo, :estado)");
      $stmt->execute([
        ':nombre' => $clase->getNombre(),
        ':periodo_academico' => $clase->getPeriodoAcademico()?->getId(),
        ':institucion' => $clase->getInstitucion()?->getId(),
        ':docente' => $clase->getDocente()?->getId(),
        ':cupo_maximo' => $clase->getCupoMaximo(),
        ':estado' => $clase->getEstado()?->getId(),
      ]);
      $clase->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE clases SET nombre = :nombre, periodo_academico = :periodo_academico, institucion = :institucion, docente = :docente, cupo_maximo = :cupo_maximo, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $clase->getId(),
        ':nombre' => $clase->getNombre(),
        ':periodo_academico' => $clase->getPeriodoAcademico()?->getId(),
        ':institucion' => $clase->getInstitucion()?->getId(),
        ':docente' => $clase->getDocente()?->getId(),
        ':cupo_maximo' => $clase->getCupoMaximo(),
        ':estado' => $clase->getEstado()?->getId(),
      ]);
    }
    return $clase;
  }

  public function findById(int $id): ?Clase
  {
    $stmt = $this->db->prepare('SELECT * FROM clases WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? new Clase($row['id'], $row['nombre'], $row['periodo_academico'], $row['institucion'], $row['docente'], $row['cupo_maximo'], $row['estado']) : null;
  }
}
