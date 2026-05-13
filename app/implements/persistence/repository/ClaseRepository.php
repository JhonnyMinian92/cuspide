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

  public function findByInstitucionWithStats(int $institucionId): array
  {
    $stmt = $this->db->prepare(
      "SELECT c.id, c.nombre, c.cupo_maximo, c.estado,
              pa.nombre                                       AS periodo,
              p.primer_nombre,
              p.primer_apellido,
              COUNT(DISTINCT cm.id)                          AS matriculados,
              COALESCE(SUM(CASE WHEN a.estado = 1 THEN 1 ELSE 0 END), 0) AS asistencias,
              COALESCE(SUM(CASE WHEN a.estado = 2 THEN 1 ELSE 0 END), 0) AS faltas
       FROM clases c
       LEFT JOIN periodo_academico pa ON pa.id = c.periodo_academico
       LEFT JOIN personas p           ON p.id  = c.docente
       LEFT JOIN clase_matricula cm   ON cm.clase = c.id AND cm.estado = 1
       LEFT JOIN asistencias a        ON a.clase  = c.id
       WHERE c.institucion = :inst
       GROUP BY c.id
       ORDER BY c.nombre ASC"
    );
    $stmt->execute([':inst' => $institucionId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
  }

  public function findCapacity(int $claseId): ?array
  {
    $stmt = $this->db->prepare(
      "SELECT c.cupo_maximo,
              COUNT(cm.id)                    AS ocupados,
              (c.cupo_maximo - COUNT(cm.id))  AS disponible
       FROM clases c
       LEFT JOIN clase_matricula cm ON cm.clase = c.id AND cm.estado = 1
       WHERE c.id = :id
       GROUP BY c.id"
    );
    $stmt->execute([':id' => $claseId]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  public function disable(int $id): void
  {
    $stmt = $this->db->prepare('UPDATE clases SET estado = 2 WHERE id = :id');
    $stmt->execute([':id' => $id]);
  }
}
