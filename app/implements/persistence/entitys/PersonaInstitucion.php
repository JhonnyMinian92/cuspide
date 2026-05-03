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
    $this->id_persona = is_string($id_persona) ? $id_persona : $id_persona->getId();
    $this->id_institucion = is_string($id_institucion) ? $id_institucion : $id_institucion->getId();
    $this->tipo = is_string($tipo) ? $tipo : $tipo->getId();
    $this->estado = is_string($estado) ? $estado : $estado->getId();
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
    $this->id_persona = is_string($id_persona) ? intval($id_persona) : $id_persona;
  }

  public function setIdInstitucion($id_institucion)
  {
    $this->id_institucion = is_string($id_institucion) ? intval($id_institucion) : $id_institucion;
  }

  public function setTipo($tipo)
  {
    $this->tipo = is_string($tipo) ? intval($tipo) : $tipo;
  }

  public function setEstado($estado)
  {
    $this->estado = is_string($estado) ? intval($estado) : $estado;
  }
}
