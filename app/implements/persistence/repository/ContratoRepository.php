<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Contrato.php';

use Core\Database;
use Entitys\Contrato;
use PDO;

class ContratoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Contrato $contrato): Contrato
  {
    $params = [
      ':fecha_inscripcion' => $contrato->getFechaInscripcion(),
      ':fecha_inicio' => $contrato->getFechaInicio(),
      ':fecha_fin' => $contrato->getFechaFin(),
      ':nivel' => $contrato->getNivel()?->getId(),
      ':institucion' => $contrato->getInstitucion()?->getId(),
      ':persona' => $contrato->getPersona()?->getId(),
    ];

    if ($contrato->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO contratacion (fecha_inscripcion, fecha_inicio, fecha_fin, nivel, institucion, persona) VALUES (:fecha_inscripcion, :fecha_inicio, :fecha_fin, :nivel, :institucion, :persona)");
      $stmt->execute($params);
      $contrato->setId((int)$this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE contratacion SET fecha_inscripcion = :fecha_inscripcion, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, nivel = :nivel, institucion = :institucion, persona = :persona WHERE id = :id");
      $params[':id'] = $contrato->getId();
      $stmt->execute($params);
    }
    return $contrato;
  }

  public function findById(int $id): ?Contrato
  {
    $stmt = $this->db->prepare('SELECT * FROM contratacion WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByInstitucion(int $institucionId, int $limit = 50): array
  {
    $stmt = $this->db->prepare(
      "SELECT c.*,
                    na.nemonico as nivel_nemonico,
                    na.nombre as nivel_nombre,
                    p.identificacion,
                    TRIM(CONCAT(p.primer_nombre, ' ', p.primer_apellido)) as nombre_completo,
                    tp.nombre as tipo_persona
             FROM contratacion c
             JOIN personas p ON c.persona = p.id
             LEFT JOIN nivel_academico na ON c.nivel = na.id
             LEFT JOIN persona_institucion pi ON p.id = pi.id_persona AND pi.id_institucion = c.institucion
             LEFT JOIN tipo_persona tp ON pi.tipo = tp.id
             WHERE c.institucion = :instId
             ORDER BY c.fecha_inicio DESC
             LIMIT :limit"
    );
    $stmt->bindValue(':instId', $institucionId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  private function map(array $row): Contrato
  {
    return new Contrato((int)$row['id'], $row['fecha_inscripcion'], $row['fecha_inicio'], $row['fecha_fin'], $row['nivel'], $row['institucion'], $row['persona']);
  }
}
