<?php

namespace Entitys;

require_once __DIR__ . '/TypeUser.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class User
{
  private ?int $id;
  private string $username;
  private string $password;
  private string $type;
  private ?string $image;

  public function __construct(?int $id, string $username, string $password, mixed $type, ?string $image = null)
  {
    $this->id = $id;
    $this->username = $username;
    $this->password = $password;
    $this->type = $this->extractId($type);
    $this->image = $image;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getUsername(): string
  {
    return $this->username;
  }
  public function getPassword(): string
  {
    return $this->password;
  }

  public function getType(): ?TypeUser
  {
    return TypeUser::from($this->type);
  }

  public function getImage(): ?string
  {
    return $this->image;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setUsername(string $username): void
  {
    $this->username = $username;
  }

  public function setPassword(string $password): void
  {
    $this->password = $password;
  }

  public function setType(mixed $type): void
  {
    $this->type = $this->extractId($type);
  }

  public function setImage(?string $image): void
  {
    $this->image = $image;
  }

  private function extractId(mixed $value): string
  {
    if (is_object($value) && method_exists($value, 'getNemonico')) {
      return $value->getNemonico();
    }
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return $value;
  }

  public static function from($id): ?self
  {
    try {
      $repositorio = new \Repositories\UserRepository();
      $user = $repositorio->findById($id);
      if ($user) {
        return $user;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', '', '');
  }
}
