<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/TypeUser.php';

use Core\Database;
use Entitys\TypeUser;
use PDO;

class TypeUserRepository
{

  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(TypeUser $typeUser): TypeUser
  {
    if ($typeUser->getNemonico() === null) {
      $stmt = $this->db->prepare("
                INSERT INTO type_users (nemonico, nombre, descripcion, imagen)
                VALUES (:nemonico, :nombre, :descripcion, :imagen)
            ");

      $stmt->execute([
        ':nemonico' => $typeUser->getNemonico(),
        ':nombre' => $typeUser->getNombre(),
        ':descripcion' => $typeUser->getDescripcion(),
        ':imagen' => $typeUser->getImagen()
      ]);

      $typeUser->setNemonico($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("
                UPDATE type_users 
                SET nombre = :nombre, descripcion = :descripcion, imagen = :imagen
                WHERE nemonico = :nemonico
            ");

      $stmt->execute([
        ':nemonico' => $typeUser->getNemonico(),
        ':nombre' => $typeUser->getNombre(),
        ':descripcion' => $typeUser->getDescripcion(),
        ':imagen' => $typeUser->getImagen()
      ]);
    }

    return $typeUser;
  }

  public function findByNemonico(string $nemonico): ?TypeUser
  {
    $stmt = $this->db->prepare("SELECT * FROM type_users WHERE nemonico = :nemonico");
    $stmt->execute([':nemonico' => $nemonico]);

    $data = $stmt->fetch();

    if (!$data) return null;

    return new TypeUser(
      $data['nemonico'],
      $data['nombre'],
      $data['descripcion'],
      $data['imagen'] ?? null
    );
  }
}
