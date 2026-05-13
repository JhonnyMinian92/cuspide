<?php

namespace Repositories;

require_once __DIR__ . '/../conexion/Database.php';
require_once __DIR__ . '/../entitys/User.php';

use Core\Database;
use Entitys\User;
use PDO;

class UserRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function save(User $user): User
  {
    $hashedPassword = password_hash($user->getPassword(), PASSWORD_BCRYPT);

    if ($user->getId() === null) {
      $stmt = $this->db->prepare("
                INSERT INTO users (username, password, type, image)
                VALUES (:username, :password, :type, :image)
            ");

      $stmt->execute([
        ':username' => $user->getUsername(),
        ':password' => $hashedPassword,
        ':type' => $user->getType()->getNemonico(),
        ':image' => $user->getImage()
      ]);

      $user->setId($this->db->lastInsertId());
    } else {
      $stmt = $this->db->prepare("
                UPDATE users 
                SET username = :username,
                    password = :password,
                    type = :type,
                    image = :image
                WHERE id = :id
            ");

      $stmt->execute([
        ':id' => $user->getId(),
        ':username' => $user->getUsername(),
        ':password' => $hashedPassword,
        ':type' => $user->getType()->getNemonico(),
        ':image' => $user->getImage()
      ]);
    }

    return $user;
  }

  public function findById(int $id): ?User
  {
    $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $data = $stmt->fetch();

    if (!$data) return null;

    return new User(
      $data['id'],
      $data['username'],
      $data['password'],
      $data['type'],
      $data['image'] ?? null
    );
  }

  public function findByUsername(string $username): ?User
  {
    $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);

    $data = $stmt->fetch();

    if (!$data) return null;

    return new User(
      $data['id'],
      $data['username'],
      $data['password'],
      $data['type'],
      $data['image'] ?? null
    );
  }

  public function updateWithoutPassword(int $id, string $username, string $type, ?string $image): ?User
  {
    $stmt = $this->db->prepare(
      "UPDATE users SET username = :u, type = :t, image = :i WHERE id = :id"
    );
    $stmt->execute([':u' => $username, ':t' => $type, ':i' => $image, ':id' => $id]);
    return $this->findById($id);
  }

  public function disableById(int $id): void
  {
    $stmt = $this->db->prepare("UPDATE users SET type = 'INACTIVO' WHERE id = :id");
    $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
    $stmt->execute();
  }
}
