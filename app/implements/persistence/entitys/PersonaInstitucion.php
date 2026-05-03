<?php

namespace Entitys;

require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/TipoPersona.php';
require_once __DIR__ . '/Estado.php';

class PersonaInstitucion
{
  private $id;
  private $id_persona;
  private $id_institucion;
  private $tipo;
  private $estado;

  public function __construct($id, $id_persona, $id_institucion, $tipo, $estado)
  {
    $this->id = $id;
    $this->id_persona = $this->extractId($id_persona);
    $this->id_institucion = $this->extractId($id_institucion);
    $this->tipo = $this->extractId($tipo);
    $this->estado = $this->extractId($estado);
  }

  public function getId()
  {
    return $this->id;
  }

  public function getIdPersona()
  {
    return Persona::from($this->id_persona);
  }

  public function getIdInstitucion()
  {
    return Institucion::from($this->id_institucion);
  }

  public function getTipo()
  {
    return TipoPersona::from($this->tipo);
  }

  public function getEstado()
  {
    return Estado::from($this->estado);
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setIdPersona($id_persona)
  {
    $this->id_persona = $this->extractId($id_persona);
  }

  public function setIdInstitucion($id_institucion)
  {
    $this->id_institucion = $this->extractId($id_institucion);
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
