<?php

namespace Entitys;

require_once __DIR__ . '/../repository/VentaRepository.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Matricula.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Estado.php';

class Venta
{
  private ?int $id;
  private string $numero;
  private string $fecha;
  private int $persona;
  private ?int $matricula;
  private int $institucion;
  private string $tipo;
  private float $subtotal;
  private float $descuento;
  private float $impuesto;
  private float $total;
  private float $saldo;
  private int $estado;

  public function __construct(?int $id, string $numero, string $fecha, mixed $persona, mixed $matricula, mixed $institucion, string $tipo, float $subtotal, float $descuento, float $impuesto, float $total, float $saldo, mixed $estado)
  {
    $this->id = $id;
    $this->numero = $numero;
    $this->fecha = $fecha;
    $this->persona = $this->extractId($persona);
    $this->matricula = $this->extractId($matricula);
    $this->institucion = $this->extractId($institucion);
    $this->tipo = $tipo;
    $this->subtotal = $subtotal;
    $this->descuento = $descuento;
    $this->impuesto = $impuesto;
    $this->total = $total;
    $this->saldo = $saldo;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getNumero(): string { return $this->numero; }
  public function getFecha(): string { return $this->fecha; }
  public function getPersona(): ?Persona { return $this->persona === null ? null : Persona::from($this->persona); }
  public function getMatricula(): ?Matricula { return $this->matricula === null ? null : Matricula::from($this->matricula); }
  public function getInstitucion(): ?Institucion { return $this->institucion === null ? null : Institucion::from($this->institucion); }
  public function getTipo(): string { return $this->tipo; }
  public function getSubtotal(): float { return $this->subtotal; }
  public function getDescuento(): float { return $this->descuento; }
  public function getImpuesto(): float { return $this->impuesto; }
  public function getTotal(): float { return $this->total; }
  public function getSaldo(): float { return $this->saldo; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setNumero(string $numero): void { $this->numero = $numero; }
  public function setFecha(string $fecha): void { $this->fecha = $fecha; }
  public function setPersona(mixed $persona): void { $this->persona = $this->extractId($persona); }
  public function setMatricula(mixed $matricula): void { $this->matricula = $this->extractId($matricula); }
  public function setInstitucion(mixed $institucion): void { $this->institucion = $this->extractId($institucion); }
  public function setTipo(string $tipo): void { $this->tipo = $tipo; }
  public function setSubtotal(float $subtotal): void { $this->subtotal = $subtotal; }
  public function setDescuento(float $descuento): void { $this->descuento = $descuento; }
  public function setImpuesto(float $impuesto): void { $this->impuesto = $impuesto; }
  public function setTotal(float $total): void { $this->total = $total; }
  public function setSaldo(float $saldo): void { $this->saldo = $saldo; }
  public function setEstado(mixed $estado): void { $this->estado = $this->extractId($estado); }

  private function extractId(mixed $value): ?int
  {
    if ($value === null) {
      return null;
    }
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return (int) $value;
  }

  public static function from(int $id): ?self
  {
    try {
      $repositorio = new \Repositories\VentaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, '', '', 0, null, 0, '', 0.0, 0.0, 0.0, 0.0, 0.0, 0);
  }
}
