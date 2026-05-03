<?php

namespace Entitys;

require_once __DIR__ . '/Boton.php';
require_once __DIR__ . '/TypeUser.php';

class BotonUser
{
  private $id;
  private $boton;
  private $typeUser;

  public function __construct($id, $boton, $typeUser)
  {
    $this->id = $id;
    $this->boton = $this->extractBotonId($boton);
    $this->typeUser = $this->extractTypeUserNemonico($typeUser);
  }

  public function getId()
  {
    return $this->id;
  }

  public function getBoton()
  {
    return Boton::from($this->boton);
  }

  public function getTypeUser()
  {
    return TypeUser::from($this->typeUser);
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setBoton($boton)
  {
    $this->boton = $this->extractBotonId($boton);
  }

  public function setTypeUser($typeUser)
  {
    $this->typeUser = $this->extractTypeUserNemonico($typeUser);
  }

  private function extractBotonId($value)
  {
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return $value;
  }

  private function extractTypeUserNemonico($value)
  {
    if (is_object($value) && method_exists($value, 'getNemonico')) {
      return $value->getNemonico();
    }
    return $value;
  }
}
