<?php

namespace Entitys;

require_once __DIR__ . '/Operadora.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/TipoContacto.php';
require_once __DIR__ . '/Estado.php';

class Telefono
{
  private ?int $id;
  private string $numero;
  private int $operadora;
  private int $persona;
  private int $tipo;
  private int $estado;

  public function __construct(?int $id, string $numero, mixed $operadora, mixed $persona, mixed $tipo, mixed $estado)
  {
    $this->id = $id;
    $this->numero = $numero;
    $this->operadora = $this->extractId($operadora);
    $this->persona = $this->extractId($persona);
    $this->tipo = $this->extractId($tipo);
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getNumero(): string
  {
    return $this->numero;
  }

  public function getOperadora(): ?Operadora
  {
    return Operadora::from($this->operadora);
  }

  public function getPersona(): ?Persona
  {
    return Persona::from($this->persona);
  }

  public function getTipo(): ?TipoContacto
  {
    return TipoContacto::from($this->tipo);
  }

  public function getEstado(): ?Estado
  {
    return Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setNumero(string $numero): void
  {
    $this->numero = $numero;
  }

  public function setOperadora(mixed $operadora): void
  {
    $this->operadora = $this->extractId($operadora);
  }

  public function setPersona(mixed $persona): void
  {
    $this->persona = $this->extractId($persona);
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
