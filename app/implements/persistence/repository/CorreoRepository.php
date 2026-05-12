<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/Correo.php';

use Core\Database;
use Entitys\Correo;
use PDO;

class CorreoRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(Correo $correo): Correo
  {
    if ($correo->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO correos (email, tipo, persona, estado) VALUES (:email, :tipo, :persona, :estado)");
      $stmt->execute([
        ':email' => $correo->getEmail(),
        ':tipo' => $correo->getTipo()?->getId(),
        ':persona' => $correo->getPersona()?->getId(),
        ':estado' => $correo->getEstado()?->getId()
      ]);
      $correo->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE correos SET email = :email, tipo = :tipo, persona = :persona, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $correo->getId(),
        ':email' => $correo->getEmail(),
        ':tipo' => $correo->getTipo()?->getId(),
        ':persona' => $correo->getPersona()?->getId(),
        ':estado' => $correo->getEstado()?->getId()
      ]);
    }

    return $correo;
  }

  public function findById($id): ?Correo
  {
    $stmt = $this->db->prepare("SELECT * FROM correos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
      return new Correo($data['id'], $data['email'], $data['tipo'], $data['persona'], $data['estado']);
    }

    return null;
  }

  public function findByPersonaId($personaId): array
  {
    $stmt = $this->db->prepare('SELECT * FROM correos WHERE persona = :personaId AND estado = 1');
    $stmt->execute([':personaId' => $personaId]);
    $correos = [];

    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $correos[] = new Correo($data['id'], $data['email'], $data['tipo'], $data['persona'], $data['estado']);
    }

    return $correos;
  }
}
