<?php

namespace Entitys;

require_once __DIR__ . '/Estado.php';
require_once __DIR__ . '/Persona.php';

class PerfilMedico
{

  private ?int $id;
  private string $fecha;
  private int $persona;
  private int $estado;

  public function __construct(?int $id, string $fecha, mixed $persona, mixed $estado)
  {
    $this->id = $id;
    $this->fecha = $fecha;
    $this->persona = $this->extractId($persona);
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(?int $id): self
  {
    $this->id = $id;
    return $this;
  }

  public function getFecha(): string
  {
    return $this->fecha;
  }

  public function setFecha(string $fecha): self
  {
    $this->fecha = $fecha;
    return $this;
  }

  public function getPersona(): ?Persona
  {
    return Persona::from($this->persona);
  }

  public function setPersona(mixed $persona): self
  {
    $this->persona = $this->extractId($persona);
    return $this;
  }

  public function getEstado(): ?Estado
  {
    return Estado::from($this->estado);
  }

  public function setEstado(mixed $estado): self
  {
    $this->estado = $this->extractId($estado);

    return $this;
  }

  private function extractId(mixed $value): int
  {
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return $value;
  }
}
