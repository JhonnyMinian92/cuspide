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
    $this->operadora = $this->extractId($operadora);
    $this->persona = $this->extractId($persona);
    $this->tipo = $this->extractId($tipo);
    $this->estado = $this->extractId($estado);
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
    $this->operadora = $this->extractId($operadora);
  }

  public function setPersona($persona)
  {
    $this->persona = $this->extractId($persona);
  }

  public function setTipo($tipo)
  {
    $this->tipo = $this->extractId($tipo);
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
