<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Torneo.php';

use Core\Database;
use Entitys\Torneo;
use PDO;

class TorneoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Torneo $torneo): Torneo
  {
    if ($torneo->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO torneos (nombre, fecha_inicio, fecha_fin, deporte, institucion, estado) VALUES (:nombre, :fecha_inicio, :fecha_fin, :deporte, :institucion, :estado)");
      $stmt->execute([
        ':nombre' => $torneo->getNombre(),
        ':fecha_inicio' => $torneo->getFechaInicio(),
        ':fecha_fin' => $torneo->getFechaFin(),
        ':deporte' => $torneo->getDeporte()?->getId(),
        ':institucion' => $torneo->getInstitucion()?->getId(),
        ':estado' => $torneo->getEstado()?->getId(),
      ]);
      $torneo->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE torneos SET nombre = :nombre, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, deporte = :deporte, institucion = :institucion, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $torneo->getId(),
        ':nombre' => $torneo->getNombre(),
        ':fecha_inicio' => $torneo->getFechaInicio(),
        ':fecha_fin' => $torneo->getFechaFin(),
        ':deporte' => $torneo->getDeporte()?->getId(),
        ':institucion' => $torneo->getInstitucion()?->getId(),
        ':estado' => $torneo->getEstado()?->getId(),
      ]);
    }
    return $torneo;
  }

  public function findById(int $id): ?Torneo
  {
    $stmt = $this->db->prepare('SELECT * FROM torneos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? new Torneo($row['id'], $row['nombre'], $row['fecha_inicio'], $row['fecha_fin'], $row['deporte'], $row['institucion'], $row['estado']) : null;
  }
}
