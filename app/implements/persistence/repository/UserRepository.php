<?php

namespace Repositories;

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
}
