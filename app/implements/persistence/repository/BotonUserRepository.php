<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/BotonUser.php';
require_once __DIR__ . '/../entitys/TypeUser.php';

use Core\Database;
use Entitys\BotonUser;
use PDO;

class BotonUserRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(BotonUser $botonUser): BotonUser
  {
    if ($botonUser->getId() === null) {
      $stmt = $this->db->prepare("INSERT INTO boton_user (boton, type_user) VALUES (:boton, :type_user)");
      $stmt->execute([
        ':boton' => $botonUser->getBoton()->getId(),
        ':type_user' => $botonUser->getTypeUser()->getNemonico()
      ]);
      $botonUser->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("UPDATE boton_user SET boton = :boton, type_user = :type_user WHERE id = :id");
      $stmt->execute([
        ':id' => $botonUser->getId(),
        ':boton' => $botonUser->getBoton()->getId(),
        ':type_user' => $botonUser->getTypeUser()->getNemonico()
      ]);
    }

    return $botonUser;
  }

  public function findById($id): ?BotonUser
  {
    $stmt = $this->db->prepare('SELECT * FROM boton_user WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? new BotonUser($row['id'], $row['boton'], $row['type_user']) : null;
  }

  public function findByTypeUser(string $typeUser): array
  {
    $stmt = $this->db->prepare('SELECT * FROM boton_user WHERE type_user = :type_user ORDER BY id ASC');
    $stmt->execute([':type_user' => $typeUser]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(
      fn($row) => new BotonUser($row['id'], $row['boton'], $row['type_user']),
      $rows ?: []
    );
  }
}
