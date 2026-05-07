<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/PersonaInstitucion.php';

use Core\Database;
use Entitys\PersonaInstitucion;
use PDO;

class PersonaInstitucionRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(PersonaInstitucion $personaInstitucion): PersonaInstitucion
  {
    if ($personaInstitucion->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO persona_institucion (id_persona, id_institucion, tipo, estado) VALUES (:id_persona, :id_institucion, :tipo, :estado)");
      $stmt->execute([
        ':id_persona' => $personaInstitucion->getIdPersona(),
        ':id_institucion' => $personaInstitucion->getIdInstitucion(),
        ':tipo' => $personaInstitucion->getTipo(),
        ':estado' => $personaInstitucion->getEstado()
      ]);
      $personaInstitucion->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE persona_institucion SET id_persona = :id_persona, id_institucion = :id_institucion, tipo = :tipo, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $personaInstitucion->getId(),
        ':id_persona' => $personaInstitucion->getIdPersona(),
        ':id_institucion' => $personaInstitucion->getIdInstitucion(),
        ':tipo' => $personaInstitucion->getTipo(),
        ':estado' => $personaInstitucion->getEstado()
      ]);
    }
    return $personaInstitucion;
  }

  public function findByPersonaId(int $personaId): ?PersonaInstitucion
  {
    $stmt = $this->db->prepare('SELECT * FROM persona_institucion WHERE id_persona = :id_persona AND estado = 1 ORDER BY id ASC LIMIT 1');
    $stmt->bindParam(':id_persona', $personaId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
      return new PersonaInstitucion($result['id'], $result['id_persona'], $result['id_institucion'], $result['tipo'], $result['estado']);
    }

    return null;
  }

  public function findById($id): ?PersonaInstitucion
  {
    $stmt = $this->db->prepare('SELECT * FROM persona_institucion WHERE id = :id AND estado = 1');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
      return new PersonaInstitucion(
        $result['id'],
        $result['id_persona'],
        $result['id_institucion'],
        $result['tipo'],
        $result['estado']
      );
    }
    return null;
  }
}