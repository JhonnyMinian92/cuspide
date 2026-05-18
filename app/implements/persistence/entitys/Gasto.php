<?php

namespace Entitys;

require_once __DIR__ . '/../repository/GastoRepository.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Estado.php';

class Gasto
{
  private ?int $id;
  private string $numero;
  private string $fecha;
  private string $descripcion;
  private string $tipo_gasto;
  private float $monto;
  private int $institucion;
  private ?int $persona;
  private ?string $referencia;
  private ?string $observacion;
  private int $estado;

  public function __construct(?int $id, string $numero, string $fecha, string $descripcion, string $tipo_gasto, float $monto, mixed $institucion, mixed $persona, ?string $referencia, ?string $observacion, mixed $estado)
  {
    $this->id = $id;
    $this->numero = $numero;
    $this->fecha = $fecha;
    $this->descripcion = $descripcion;
    $this->tipo_gasto = $tipo_gasto;
    $this->monto = $monto;
    $this->institucion = $this->extractId($institucion);
    $this->persona = $persona !== null ? $this->extractId($persona) : null;
    $this->referencia = $referencia;
    $this->observacion = $observacion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getNumero(): string { return $this->numero; }
  public function getFecha(): string { return $this->fecha; }
  public function getDescripcion(): string { return $this->descripcion; }
  public function getTipoGasto(): string { return $this->tipo_gasto; }
  public function getMonto(): float { return $this->monto; }
  public function getInstitucion(): ?Institucion { return Institucion::from($this->institucion); }
  public function getPersonaId(): ?int { return $this->persona; }
  public function getReferencia(): ?string { return $this->referencia; }
  public function getObservacion(): ?string { return $this->observacion; }
  public function getEstado(): ?Estado { return Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setNumero(string $v): void { $this->numero = $v; }
  public function setFecha(string $v): void { $this->fecha = $v; }
  public function setDescripcion(string $v): void { $this->descripcion = $v; }
  public function setTipoGasto(string $v): void { $this->tipo_gasto = $v; }
  public function setMonto(float $v): void { $this->monto = $v; }
  public function setInstitucion(mixed $v): void { $this->institucion = $this->extractId($v); }
  public function setPersonaId(?int $v): void { $this->persona = $v; }
  public function setReferencia(?string $v): void { $this->referencia = $v; }
  public function setObservacion(?string $v): void { $this->observacion = $v; }
  public function setEstado(mixed $v): void { $this->estado = $this->extractId($v); }

  private function extractId(mixed $value): int
  {
    if (is_object($value) && method_exists($value, 'getId')) return $value->getId();
    return (int) $value;
  }

  public static function from(int $id): ?self
  {
    try {
      $repo = new \Repositories\GastoRepository();
      $entity = $repo->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {}
    return new self($id, '', '', '', '', 0.0, 0, null, null, null, 1);
  }
}
