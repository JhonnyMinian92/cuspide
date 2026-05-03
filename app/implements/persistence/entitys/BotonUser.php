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
    $this->boton = $boton;
    $this->typeUser = $typeUser;
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
    $this->boton = is_string($boton) ? $boton : $boton->getId();
  }

  public function setTypeUser($typeUser)
  {
    $this->typeUser = is_string($typeUser) ? $typeUser : $typeUser->getNemonico();
  }
}
