<?php

namespace Repositories;

/*
 * DB migration required (run once):
 * ALTER TABLE clases
 *   ADD COLUMN asignatura      INT          NULL,
 *   ADD COLUMN nivel_asignatura INT         NULL,
 *   ADD COLUMN precio          DECIMAL(10,2) NULL DEFAULT 0.00,
 *   ADD COLUMN observacion     TEXT          NULL;
 */

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
      $stmt = $this->db->prepare(
        "INSERT INTO clases (nombre, periodo_academico, institucion, docente, cupo_maximo, estado, asignatura, nivel, precio, observacion)
         VALUES (:nombre, :periodo_academico, :institucion, :docente, :cupo_maximo, :estado, :asignatura, :nivel, :precio, :observacion)"
      );
      $stmt->execute([
        ':nombre'           => $clase->getNombre(),
        ':periodo_academico'=> $clase->getPeriodoAcademico()?->getId(),
        ':institucion'      => $clase->getInstitucion()?->getId(),
        ':docente'          => $clase->getDocente()?->getId(),
        ':cupo_maximo'      => $clase->getCupoMaximo(),
        ':estado'           => $clase->getEstado()?->getId(),
        ':asignatura'       => $clase->getAsignatura(),
        ':nivel'            => $clase->getNivelAsignatura(),
        ':precio'           => $clase->getPrecio(),
        ':observacion'      => $clase->getObservacion(),
      ]);
      $clase->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare(
        "UPDATE clases
         SET nombre = :nombre, periodo_academico = :periodo_academico, institucion = :institucion,
             docente = :docente, cupo_maximo = :cupo_maximo, estado = :estado,
             asignatura = :asignatura, nivel = :nivel,
             precio = :precio, observacion = :observacion
         WHERE id = :id"
      );
      $stmt->execute([
        ':id'               => $clase->getId(),
        ':nombre'           => $clase->getNombre(),
        ':periodo_academico'=> $clase->getPeriodoAcademico()?->getId(),
        ':institucion'      => $clase->getInstitucion()?->getId(),
        ':docente'          => $clase->getDocente()?->getId(),
        ':cupo_maximo'      => $clase->getCupoMaximo(),
        ':estado'           => $clase->getEstado()?->getId(),
        ':asignatura'       => $clase->getAsignatura(),
        ':nivel'            => $clase->getNivelAsignatura(),
        ':precio'           => $clase->getPrecio(),
        ':observacion'      => $clase->getObservacion(),
      ]);
    }
    return $clase;
  }

  public function findById(int $id): ?Clase
  {
    $stmt = $this->db->prepare('SELECT * FROM clases WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new Clase(
      $row['id'],
      $row['nombre'],
      $row['periodo_academico'],
      $row['institucion'],
      $row['docente'],
      $row['cupo_maximo'],
      $row['estado'],
      $row['asignatura']  ?? null,
      $row['nivel']       ?? null,
      $row['precio']      ?? null,
      $row['observacion'] ?? null
    ) : null;
  }

  public function findByInstitucionWithStats(int $institucionId, ?int $periodoId = null): array
  {
    $periodoFilter = $periodoId !== null ? 'AND c.periodo_academico = :periodo' : '';
    $stmt = $this->db->prepare(
      "SELECT c.id, c.nombre, c.cupo_maximo, c.estado, c.periodo_academico,
              c.asignatura, c.nivel AS nivel_asignatura, c.precio, c.observacion, c.docente,
              pa.nombre                                                       AS periodo,
              asig.nombre                                                     AS asignatura_nombre,
              na.nombre                                                       AS nivel_nombre,
              p.primer_nombre,
              p.primer_apellido,
              COUNT(DISTINCT cm.id)                                           AS matriculados,
              COALESCE(SUM(CASE WHEN a.estado = 1 THEN 1 ELSE 0 END), 0)    AS asistencias,
              COALESCE(SUM(CASE WHEN a.estado = 2 THEN 1 ELSE 0 END), 0)    AS faltas
       FROM clases c
       LEFT JOIN periodos_academicos pa  ON pa.id   = c.periodo_academico
       LEFT JOIN asignaturas asig        ON asig.id = c.asignatura
       LEFT JOIN nivel_asignatura na     ON na.id   = c.nivel
       LEFT JOIN personas p              ON p.id    = c.docente
       LEFT JOIN clases_matricula cm     ON cm.clase = c.id AND cm.estado = 1
       LEFT JOIN asistencias a           ON a.clase  = c.id
       WHERE c.institucion = :inst AND c.estado = 1
       $periodoFilter
       GROUP BY c.id
       ORDER BY c.nombre ASC"
    );
    $params = [':inst' => $institucionId];
    if ($periodoId !== null) $params[':periodo'] = $periodoId;
    $stmt->execute($params);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
  }

  public function findDocentesByInstitucion(int $institucionId): array
  {
    $stmt = $this->db->prepare(
      "SELECT p.id,
              TRIM(CONCAT(COALESCE(p.primer_nombre,''),' ',COALESCE(p.segundo_nombre,''),' ',
                          COALESCE(p.primer_apellido,''),' ',COALESCE(p.segundo_apellido,''))) AS nombre_completo,
              p.primer_nombre, p.primer_apellido,
              tp.nombre AS tipo, tp.nemonico
       FROM personas p
       JOIN persona_institucion pi ON pi.id_persona = p.id AND pi.id_institucion = :inst AND pi.estado = 1
       JOIN tipo_persona tp        ON tp.id = pi.tipo
       WHERE (LOWER(tp.nemonico) LIKE '%doc%' OR LOWER(tp.nombre) LIKE '%docente%' OR LOWER(tp.nombre) LIKE '%entrenador%')
       ORDER BY p.primer_apellido ASC, p.primer_nombre ASC"
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
       LEFT JOIN clases_matricula cm ON cm.clase = c.id AND cm.estado = 1
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
