<?php

namespace Entitys;

require_once __DIR__ . '/TipoContacto.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Estado.php';

class Correo
{
  private ?int $id;
  private string $email;
  private int $tipo;
  private int $persona;
  private int $estado;

  public function __construct(?int $id, string $email, mixed $tipo, mixed $persona, mixed $estado)
  {
    $this->id = $id;
    $this->email = $email;
    $this->tipo = $this->extractId($tipo);
    $this->persona = $this->extractId($persona);
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getTipo(): ?TipoContacto
  {
    return TipoContacto::from($this->tipo);
  }

  public function getPersona(): ?Persona
  {
    return Persona::from($this->persona);
  }

  public function getEstado(): ?Estado
  {
    return Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setEmail(string $email): void
  {
    $this->email = $email;
  }

  public function setTipo(mixed $tipo): void
  {
    $this->tipo = $this->extractId($tipo);
  }

  public function setPersona(mixed $persona): void
  {
    $this->persona = $this->extractId($persona);
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
