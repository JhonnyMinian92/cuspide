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
      $stmt = $this->db->prepare("INSERT INTO actividades (nombre, categoria_deportiva, precio, nivel, observacion, institucion, estado, docente, cupo_maximo, periodo_academico) VALUES (:nombre, :categoria_deportiva, :precio, :nivel, :observacion, :institucion, :estado, :docente, :cupo_maximo, :periodo_academico)");
      $stmt->execute([
        ':nombre' => $actividad->getNombre(),
        ':categoria_deportiva' => $actividad->getCategoriaDeportiva()?->getId(),
        ':precio' => $actividad->getPrecio(),
        ':nivel' => $actividad->getNivel()?->getId(),
        ':observacion' => $actividad->getObservacion(),
        ':institucion' => $actividad->getInstitucion()?->getId(),
        ':estado' => $actividad->getEstado()?->getId(),
        ':docente' => $actividad->getDocente()?->getId(),
        ':cupo_maximo' => $actividad->getCupoMaximo(),
        ':periodo_academico' => $actividad->getPeriodoAcademico(),
      ]);
      $actividad->setId((int)$this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE actividades SET nombre = :nombre, categoria_deportiva = :categoria_deportiva, precio = :precio, nivel = :nivel, observacion = :observacion, institucion = :institucion, estado = :estado, docente = :docente, cupo_maximo = :cupo_maximo, periodo_academico = :periodo_academico WHERE id = :id");
      $stmt->execute([
        ':id' => $actividad->getId(),
        ':nombre' => $actividad->getNombre(),
        ':categoria_deportiva' => $actividad->getCategoriaDeportiva()?->getId(),
        ':precio' => $actividad->getPrecio(),
        ':nivel' => $actividad->getNivel()?->getId(),
        ':observacion' => $actividad->getObservacion(),
        ':institucion' => $actividad->getInstitucion()?->getId(),
        ':estado' => $actividad->getEstado()?->getId(),
        ':docente' => $actividad->getDocente()?->getId(),
        ':cupo_maximo' => $actividad->getCupoMaximo(),
        ':periodo_academico' => $actividad->getPeriodoAcademico(),
      ]);
    }

    return $actividad;
  }

  private function map(array $row): Actividad
  {
    return new Actividad($row['id'], $row['categoria_deportiva'], $row['precio'], $row['nivel'], $row['observacion'], $row['institucion'], $row['nombre'] ?? '', $row['estado'] ?? 1, $row['docente'], $row['cupo_maximo'], $row['periodo_academico']);
  }

  public function findById(int $id): ?Actividad
  {
    $stmt = $this->db->prepare('SELECT * FROM actividades WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? $this->map($row) : null;
  }

  public function findAll(): array
  {
    $stmt = $this->db->query('SELECT * FROM actividades ORDER BY id ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(fn($row) => $this->map($row), $rows ?: []);
  }

  public function findByInstitucionWithStats(int $institucionId): array
  {
    $stmt = $this->db->prepare(
      "SELECT ac.id, ac.nombre, ac.precio, ac.estado, ac.cupo_maximo, ac.observacion,
              ac.categoria_deportiva                                  AS categoria_deportiva_id,
              cd.nombre                                               AS categoria_deportiva_nombre,
              d.nombre                                                AS deporte_nombre,
              ac.nivel                                                AS nivel_id,
              n.nombre                                                AS nivel_nombre,
              ac.docente                                              AS docente_id,
              TRIM(CONCAT(p.primer_nombre, ' ', p.primer_apellido))    AS docente_nombre,
              ac.periodo_academico                                    AS periodo_academico_id,
              pa.nombre                                               AS periodo_academico_nombre,
              COUNT(DISTINCT am.id)                                   AS matriculados
       FROM actividades ac
       LEFT JOIN categorias_deportivas cd ON cd.id = ac.categoria_deportiva
       LEFT JOIN deportes d ON d.id = cd.deporte
       LEFT JOIN nivel_asignatura n  ON n.id     = ac.nivel
       LEFT JOIN personas p ON p.id = ac.docente
       LEFT JOIN periodos_academicos pa ON pa.id = ac.periodo_academico
       LEFT JOIN actividades_matricula am   ON am.actividad = ac.id AND am.estado = 1
       WHERE ac.institucion = :inst AND ac.estado = 1
       GROUP BY ac.id
       ORDER BY ac.nombre ASC"
    );
    $stmt->execute([':inst' => $institucionId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  public function findCapacity(int $actividadId): ?array
  {
    $stmt = $this->db->prepare(
      "SELECT a.cupo_maximo,
              COUNT(am.id) AS ocupados,
              (a.cupo_maximo - COUNT(am.id)) AS disponible
       FROM actividades a
       LEFT JOIN actividades_matricula am ON am.actividad = a.id AND am.estado = 1
       WHERE a.id = :id
       GROUP BY a.id
       LIMIT 1"
    );
    $stmt->execute([':id' => $actividadId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  public function disable(int $id): void
  {
    $stmt = $this->db->prepare('UPDATE actividades SET estado = 2 WHERE id = :id');
    $stmt->execute([':id' => $id]);
  }
}
