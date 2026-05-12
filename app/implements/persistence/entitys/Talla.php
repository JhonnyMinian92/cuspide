<?php

namespace Entitys;

require_once __DIR__ . '/../repository/TallaRepository.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Estado.php';

class Talla
{
  private ?int $id;
  private int $persona;
  private string $tipo_prenda; // 'camiseta', 'pantalon', 'calzado', 'general'
  private string $talla; // 'S', 'M', 'L', '38', '42'
  private ?string $medida_cm; // '175' (altura), '70' (peso kg)
  private string $fecha_medicion;
  private int $estado;

  public function __construct(?int $id, mixed $persona, string $tipo_prenda, string $talla, ?string $medida_cm, string $fecha_medicion, mixed $estado)
  {
    $this->id = $id;
    $this->persona = $this->extractId($persona);
    $this->tipo_prenda = $tipo_prenda;
    $this->talla = $talla;
    $this->medida_cm = $medida_cm;
    $this->fecha_medicion = $fecha_medicion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }
  public function getPersona(): ?Persona
  {
    return Persona::from($this->persona);
  }
  public function getTipoPrenda(): string
  {
    return $this->tipo_prenda;
  }
  public function getTalla(): string
  {
    return $this->talla;
  }
  public function getMedidaCm(): ?string
  {
    return $this->medida_cm;
  }
  public function getFechaMedicion(): string
  {
    return $this->fecha_medicion;
  }
  public function getEstado(): ?Estado
  {
    return Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }
  public function setPersona(mixed $persona): void
  {
    $this->persona = $this->extractId($persona);
  }
  public function setTipoPrenda(string $tipo_prenda): void
  {
    $this->tipo_prenda = $tipo_prenda;
  }
  public function setTalla(string $talla): void
  {
    $this->talla = $talla;
  }
  public function setMedidaCm(?string $medida_cm): void
  {
    $this->medida_cm = $medida_cm;
  }
  public function setFechaMedicion(string $fecha_medicion): void
  {
    $this->fecha_medicion = $fecha_medicion;
  }
  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  private function extractId(mixed $value): int
  {
    if (is_object($value) && method_exists($value, 'getId')) {
      return (int)$value->getId();
    }
    return (int)$value;
  }

  public static function from(int $id): ?self
  {
    // Implementar carga lazy si es necesario
    return new self($id, 0, '', '', null, '', 0);
  }
}
