<?php

namespace Entitys;

require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/TipoPersona.php';
require_once __DIR__ . '/Estado.php';

class PersonaInstitucion
{
  private ?int $id;
  private int $id_persona;
  private int $id_institucion;
  private int $tipo;
  private int $estado;

  public function __construct(?int $id, mixed $id_persona, mixed $id_institucion, mixed $tipo, mixed $estado)
  {
    $this->id = $id;
    $this->id_persona = $this->extractId($id_persona);
    $this->id_institucion = $this->extractId($id_institucion);
    $this->tipo = $this->extractId($tipo);
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getIdPersona(): ?Persona
  {
    return Persona::from($this->id_persona);
  }

  public function getIdInstitucion(): ?Institucion
  {
    return Institucion::from($this->id_institucion);
  }

  public function getTipo(): ?TipoPersona
  {
    return TipoPersona::from($this->tipo);
  }

  public function getEstado(): ?Estado
  {
    return Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setIdPersona(mixed $id_persona): void
  {
    $this->id_persona = $this->extractId($id_persona);
  }

  public function setIdInstitucion(mixed $id_institucion): void
  {
    $this->id_institucion = $this->extractId($id_institucion);
  }

  public function setTipo(mixed $tipo): void
  {
    $this->tipo = $this->extractId($tipo);
  }

  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  private function extractId(mixed $value): int
  {
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return $value;
  }
}
