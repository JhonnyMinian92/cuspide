<?php

namespace Entitys;

require_once __DIR__ . '/TypeUser.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class User
{
  private $id;
  private $username;
  private $password;
  private $type;
  private $image;

  public function __construct($id, $username, $password, $type, $image = null)
  {
    $this->id = $id;
    $this->username = $username;
    $this->password = $password;
    $this->type = $this->extractId($type);
    $this->image = $image;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getUsername()
  {
    return $this->username;
  }
  public function getPassword()
  {
    return $this->password;
  }

  public function getType()
  {
    return TypeUser::from($this->type);
  }

  public function getImage()
  {
    return $this->image;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setUsername($username)
  {
    $this->username = $username;
  }

  public function setPassword($password)
  {
    $this->password = $password;
  }

  public function setType($type)
  {
    $this->type = $this->extractId($type);
  }

  public function setImage($image)
  {
    $this->image = $image;
  }

  private function extractId($value)
  {
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
