<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';

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
        ':tipo' => $correo->getTipo(),
        ':persona' => $correo->getPersona(),
        ':estado' => $correo->getEstado()
      ]);
      $correo->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE correos SET email = :email, tipo = :tipo, persona = :persona, estado = :estado WHERE id = :id");
      $stmt->execute([
        ':id' => $correo->getId(),
        ':email' => $correo->getEmail(),
        ':tipo' => $correo->getTipo(),
        ':persona' => $correo->getPersona(),
        ':estado' => $correo->getEstado()
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
}
