<?php

namespace Entitys;

require_once __DIR__ . '/Operadora.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/TipoContacto.php';
require_once __DIR__ . '/Estado.php';

class Telefono
{
  private $id;
  private $numero;
  private $operadora;
  private $persona;
  private $tipo;
  private $estado;

  public function __construct($id, $numero, $operadora, $persona, $tipo, $estado)
  {
    $this->id = $id;
    $this->numero = $numero;
    $this->operadora = is_string($operadora) ? $operadora : $operadora->getId();
    $this->persona = $persona;
    $this->tipo = $tipo;
    $this->estado = $estado;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getNumero()
  {
    return $this->numero;
  }

  public function getOperadora()
  {
    return Operadora::from($this->operadora);
  }

  public function getPersona()
  {
    return Persona::from($this->persona);
  }

  public function getTipo()
  {
    return TipoContacto::from($this->tipo);
  }

  public function getEstado()
  {
    return Estado::from($this->estado);
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setNumero($numero)
  {
    $this->numero = $numero;
  }

  public function setOperadora($operadora)
  {
    $this->operadora = is_string($operadora) ? $operadora : $operadora->getId();
  }

  public function setPersona($persona)
  {
    $this->persona = $persona;
  }

  public function setTipo($tipo)
  {
    $this->tipo = $tipo;
  }

  public function setEstado($estado)
  {
    $this->estado = $estado;
  }
}
