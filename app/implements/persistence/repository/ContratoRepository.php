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
    if ($contrato->getId() === null) {
      $stmt = $this->db->prepare(
        "INSERT INTO contratacion (fecha_inscripcion, fecha_inicio, fecha_fin, nivel, institucion, persona)
         VALUES (:fecha_inscripcion, :fecha_inicio, :fecha_fin, :nivel, :institucion, :persona)"
      );
      $stmt->execute($this->params($contrato));
      $contrato->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare(
        "UPDATE contratacion SET fecha_inscripcion = :fecha_inscripcion, fecha_inicio = :fecha_inicio,
         fecha_fin = :fecha_fin, nivel = :nivel, institucion = :institucion, persona = :persona
         WHERE id = :id"
      );
      $params = $this->params($contrato);
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
      "SELECT c.*, p.identificacion,
              CONCAT(COALESCE(p.primer_nombre,''),' ',COALESCE(p.primer_apellido,''),' ',COALESCE(p.segundo_apellido,'')) AS nombre_completo,
              tp.nombre AS tipo_persona,
              n.nombre AS nivel_nombre
       FROM contratacion c
       JOIN personas p ON p.id = c.persona
       LEFT JOIN persona_institucion pi ON pi.id_persona = p.id AND pi.id_institucion = c.institucion
       LEFT JOIN tipo_persona tp ON tp.id = pi.tipo
       LEFT JOIN nivel n ON n.id = c.nivel
       WHERE c.institucion = :inst
       ORDER BY c.fecha_inscripcion DESC
       LIMIT :lim"
    );
    $stmt->bindValue(':inst', $institucionId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  public function findByPersona(int $personaId): array
  {
    $stmt = $this->db->prepare(
      "SELECT c.*, n.nombre AS nivel_nombre, i.nombre AS institucion_nombre
       FROM contratacion c
       LEFT JOIN nivel n ON n.id = c.nivel
       LEFT JOIN instituciones i ON i.id = c.institucion
       WHERE c.persona = :persona
       ORDER BY c.fecha_inicio DESC"
    );
    $stmt->execute([':persona' => $personaId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  private function params(Contrato $c): array
  {
    return [
      ':fecha_inscripcion' => $c->getFechaInscripcion(),
      ':fecha_inicio'      => $c->getFechaInicio(),
      ':fecha_fin'         => $c->getFechaFin(),
      ':nivel'             => $c->getNivel()?->getId(),
      ':institucion'       => $c->getInstitucion()?->getId(),
      ':persona'           => $c->getPersona()?->getId(),
    ];
  }

  private function map(array $row): Contrato
  {
    return new Contrato($row['id'], $row['fecha_inscripcion'], $row['fecha_inicio'], $row['fecha_fin'], $row['nivel'], $row['institucion'], $row['persona']);
  }
}
