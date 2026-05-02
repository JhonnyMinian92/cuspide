<?php

namespace Entitys;

require_once __DIR__ . '/TypeUser.php';

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
    $this->type = is_string($type) ? $type : $type->getNemonico();
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
    $this->type = is_string($type) ? $type : $type->getNemonico();
  }

  public function setImage($image)
  {
    $this->image = $image;
  }
}