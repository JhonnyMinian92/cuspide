<?php

namespace Entitys;

require_once __DIR__ . '/TipoContacto.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Estado.php';

class Correo
{
  private $id;
  private $email;
  private $tipo;
  private $persona;
  private $estado;

  public function __construct($id, $email, $tipo, $persona, $estado)
  {
    $this->id = $id;
    $this->email = $email;
    $this->tipo = $this->extractId($tipo);
    $this->persona = $this->extractId($persona);
    $this->estado = $this->extractId($estado);
  }

  public function getId()
  {
    return $this->id;
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function getTipo()
  {
    return TipoContacto::from($this->tipo);
  }

  public function getPersona()
  {
    return Persona::from($this->persona);
  }

  public function getEstado()
  {
    return Estado::from($this->estado);
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setEmail($email)
  {
    $this->email = $email;
  }

  public function setTipo($tipo)
  {
    $this->tipo = $this->extractId($tipo);
  }

  public function setPersona($persona)
  {
    $this->persona = $this->extractId($persona);
  }

  public function setEstado($estado)
  {
    $this->estado = $this->extractId($estado);
  }

  private function extractId($value)
  {
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return $value;
  }
}
