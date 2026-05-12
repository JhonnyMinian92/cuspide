<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/TorneoParticipante.php';

use Core\Database;
use Entitys\TorneoParticipante;
use PDO;

class TorneoParticipanteRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(TorneoParticipante $participante): TorneoParticipante
  {
    if ($participante->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO torneo_participantes (torneo, matricula, fecha_inscripcion, estado) VALUES (:torneo, :matricula, :fecha_inscripcion, :estado)");
      $stmt->execute([
        ':torneo' => $participante->getTorneo()?->getId(),
        ':matricula' => $participante->getMatricula()?->getId(),
        ':fecha_inscripcion' => $participante->getFechaInscripcion(),
        ':estado' => $participante->getEstado()?->getId(),
      ]);
      $participante->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE torneo_participantes SET torneo = :torneo, matricula = :matricula, fecha_inscripcion = :fecha_inscripcion, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $participante->getId(),
        ':torneo' => $participante->getTorneo()?->getId(),
        ':matricula' => $participante->getMatricula()?->getId(),
        ':fecha_inscripcion' => $participante->getFechaInscripcion(),
        ':estado' => $participante->getEstado()?->getId(),
      ]);
    }
    return $participante;
  }

  public function findById(int $id): ?TorneoParticipante
  {
    $stmt = $this->db->prepare('SELECT * FROM torneo_participantes WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? new TorneoParticipante($row['id'], $row['torneo'], $row['matricula'], $row['fecha_inscripcion'], $row['estado']) : null;
  }
}
