<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Socio.php';

use Core\Database;
use Entitys\Socio;
use PDO;

class SocioRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
    $this->migrate();
  }

  private function migrate(): void
  {
    $this->db->exec("CREATE TABLE IF NOT EXISTS socios (
      id             INT AUTO_INCREMENT PRIMARY KEY,
      persona_id     INT NOT NULL,
      membresia_id   INT NOT NULL,
      fecha_inicio   DATE NOT NULL,
      fecha_fin      DATE NOT NULL,
      estado         INT NOT NULL DEFAULT 1,
      institucion_id INT NOT NULL,
      KEY idx_socio_persona (persona_id),
      KEY idx_socio_membresia (membresia_id),
      KEY idx_socio_institucion (institucion_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }

  public function save(Socio $socio): Socio
  {
    if ($socio->getId() === null) {
      $stmt = $this->db->prepare(
        "INSERT INTO socios (persona_id, membresia_id, fecha_inicio, fecha_fin, estado, institucion_id)
         VALUES (:pid, :mid, :fi, :ff, :est, :inst)"
      );
      $stmt->execute($this->params($socio));
      $socio->setId((int) $this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare(
        "UPDATE socios SET persona_id=:pid, membresia_id=:mid, fecha_inicio=:fi, fecha_fin=:ff,
         estado=:est, institucion_id=:inst WHERE id=:id"
      );
      $params = $this->params($socio);
      $params[':id'] = $socio->getId();
      $stmt->execute($params);
    }
    return $socio;
  }

  public function findById(int $id): ?Socio
  {
    $stmt = $this->db->prepare("SELECT * FROM socios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByPersonaId(int $personaId): ?Socio
  {
    $stmt = $this->db->prepare("SELECT * FROM socios WHERE persona_id = :pid ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pid' => $personaId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByPersonaAndInstitucion(int $personaId, int $instId): ?Socio
  {
    $stmt = $this->db->prepare(
      "SELECT * FROM socios WHERE persona_id = :pid AND institucion_id = :inst ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([':pid' => $personaId, ':inst' => $instId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->map($row) : null;
  }

  public function findByInstitucion(int $instId, string $q = '', int $limit = 20, int $offset = 0): array
  {
    $search = '%' . $q . '%';
    $stmt = $this->db->prepare(
      "SELECT s.id AS socio_id,
              s.persona_id, s.membresia_id, s.fecha_inicio, s.fecha_fin, s.estado AS socio_estado,
              p.identificacion,
              TRIM(CONCAT(
                COALESCE(p.primer_nombre,''), ' ',
                COALESCE(p.segundo_nombre,''), ' ',
                COALESCE(p.primer_apellido,''), ' ',
                COALESCE(p.segundo_apellido,'')
              )) AS nombre_completo,
              p.primer_nombre, p.primer_apellido,
              m.titulo AS membresia_titulo, m.precio AS membresia_precio
       FROM socios s
       JOIN personas p    ON p.id = s.persona_id
       JOIN membresias m  ON m.id = s.membresia_id
       WHERE s.institucion_id = :inst
         AND (:q = '' OR p.identificacion LIKE :search OR CONCAT(p.primer_nombre,' ',p.primer_apellido) LIKE :search2)
       ORDER BY s.id DESC
       LIMIT :lim OFFSET :off"
    );
    $stmt->bindValue(':inst',    $instId,   PDO::PARAM_INT);
    $stmt->bindValue(':q',       $q,        PDO::PARAM_STR);
    $stmt->bindValue(':search',  $search,   PDO::PARAM_STR);
    $stmt->bindValue(':search2', $search,   PDO::PARAM_STR);
    $stmt->bindValue(':lim',     $limit,    PDO::PARAM_INT);
    $stmt->bindValue(':off',     $offset,   PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  public function countByInstitucion(int $instId, string $q = ''): int
  {
    $search = '%' . $q . '%';
    $stmt = $this->db->prepare(
      "SELECT COUNT(*) FROM socios s
       JOIN personas p ON p.id = s.persona_id
       WHERE s.institucion_id = :inst
         AND (:q = '' OR p.identificacion LIKE :search OR CONCAT(p.primer_nombre,' ',p.primer_apellido) LIKE :search2)"
    );
    $stmt->bindValue(':inst',    $instId, PDO::PARAM_INT);
    $stmt->bindValue(':q',       $q,      PDO::PARAM_STR);
    $stmt->bindValue(':search',  $search, PDO::PARAM_STR);
    $stmt->bindValue(':search2', $search, PDO::PARAM_STR);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
  }

  private function params(Socio $s): array
  {
    return [
      ':pid'  => $s->getPersonaId(),
      ':mid'  => $s->getMembresiaId(),
      ':fi'   => $s->getFechaInicio(),
      ':ff'   => $s->getFechaFin(),
      ':est'  => $s->getEstado(),
      ':inst' => $s->getInstitucionId(),
    ];
  }

  private function map(array $row): Socio
  {
    return new Socio(
      (int) $row['id'],
      (int) $row['persona_id'],
      (int) $row['membresia_id'],
      $row['fecha_inicio'],
      $row['fecha_fin'],
      (int) $row['estado'],
      (int) $row['institucion_id']
    );
  }
}
