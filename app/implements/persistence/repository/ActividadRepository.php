<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Actividad.php';

use Core\Database;
use Entitys\Actividad;
use PDO;

class ActividadRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Actividad $actividad): Actividad
  {
    if ($actividad->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO actividades (nombre, deporte, precio, nivel, observacion, institucion, estado) VALUES (:nombre, :deporte, :precio, :nivel, :observacion, :institucion, :estado)");
      $stmt->execute([
        ':nombre' => $actividad->getNombre(),
        ':deporte' => $actividad->getDeporte()?->getId(),
        ':precio' => $actividad->getPrecio(),
        ':nivel' => $actividad->getNivel()?->getId(),
        ':observacion' => $actividad->getObservacion(),
        ':institucion' => $actividad->getInstitucion()?->getId(),
        ':estado' => $actividad->getEstado()?->getId(),
      ]);
      $actividad->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE actividades SET nombre = :nombre, deporte = :deporte, precio = :precio, nivel = :nivel, observacion = :observacion, institucion = :institucion, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $actividad->getId(),
        ':nombre' => $actividad->getNombre(),
        ':deporte' => $actividad->getDeporte()?->getId(),
        ':precio' => $actividad->getPrecio(),
        ':nivel' => $actividad->getNivel()?->getId(),
        ':observacion' => $actividad->getObservacion(),
        ':institucion' => $actividad->getInstitucion()?->getId(),
        ':estado' => $actividad->getEstado()?->getId(),
      ]);
    }

    return $actividad;
  }

  public function findById(int $id): ?Actividad
  {
    $stmt = $this->db->prepare('SELECT * FROM actividades WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Actividad($row['id'], $row['deporte'], $row['precio'], $row['nivel'], $row['observacion'], $row['institucion'], $row['nombre'] ?? '', $row['estado'] ?? 1) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM actividades ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => new Actividad($row['id'], $row['deporte'], $row['precio'], $row['nivel'], $row['observacion'], $row['institucion'], $row['nombre'] ?? '', $row['estado'] ?? 1), $rows ?: []);
  }

  public function findByInstitucionWithStats(int $institucionId): array
  {
    $stmt = $this->db->prepare(
      "SELECT ac.id, ac.nombre,
              d.nombre                                                AS deporte,
              n.nombre                                                AS nivel,
              ac.precio, ac.estado,
              COUNT(DISTINCT am.id)                                   AS matriculados,
              COALESCE(SUM(CASE WHEN asis.estado = 1 THEN 1 ELSE 0 END), 0) AS asistencias,
              COALESCE(SUM(CASE WHEN asis.estado = 2 THEN 1 ELSE 0 END), 0) AS faltas
       FROM actividades ac
       JOIN deportes d               ON d.id     = ac.deporte
       LEFT JOIN nivel_asignatura n  ON n.id     = ac.nivel
       LEFT JOIN actividades_matricula am   ON am.actividad = ac.id AND am.estado = 1
       LEFT JOIN asistencias asis    ON asis.actividad = ac.id
       WHERE ac.institucion = :inst
       GROUP BY ac.id
       ORDER BY ac.nombre ASC"
    );
    $stmt->execute([':inst' => $institucionId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
  }

  public function findCapacity(int $actividadId): ?array
  {
    $stmt = $this->db->prepare(
      "SELECT h.cupo_maximo,
              COUNT(am.id)                    AS ocupados,
              (h.cupo_maximo - COUNT(am.id))  AS disponible
       FROM horarios h
       LEFT JOIN actividades_matricula am ON am.actividad = h.actividad AND am.estado = 1
       WHERE h.actividad = :id AND h.estado = 1
       GROUP BY h.id
       LIMIT 1"
    );
    $stmt->execute([':id' => $actividadId]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  public function disable(int $id): void
  {
    $stmt = $this->db->prepare('UPDATE actividades SET estado = 2 WHERE id = :id');
    $stmt->execute([':id' => $id]);
  }
}
