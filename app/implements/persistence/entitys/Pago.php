<?php

namespace Entitys;

require_once __DIR__ . '/../repository/PagoRepository.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Matricula.php';
require_once __DIR__ . '/Venta.php';
require_once __DIR__ . '/FormaPago.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Estado.php';

class Pago
{
  private ?int $id;
  private string $numero;
  private string $fecha;
  private int $persona;
  private ?int $matricula;
  private ?int $venta;
  private int $forma_pago;
  private int $institucion;
  private float $monto;
  private ?string $referencia;
  private string $observacion;
  private int $estado;

  public function __construct(?int $id, string $numero, string $fecha, mixed $persona, mixed $matricula, mixed $venta, mixed $forma_pago, mixed $institucion, float $monto, ?string $referencia, string $observacion, mixed $estado)
  {
    $this->id = $id;
    $this->numero = $numero;
    $this->fecha = $fecha;
    $this->persona = $this->extractId($persona);
    $this->matricula = $this->extractId($matricula);
    $this->venta = $this->extractId($venta);
    $this->forma_pago = $this->extractId($forma_pago);
    $this->institucion = $this->extractId($institucion);
    $this->monto = $monto;
    $this->referencia = $referencia;
    $this->observacion = $observacion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getNumero(): string { return $this->numero; }
  public function getFecha(): string { return $this->fecha; }
  public function getPersona(): ?Persona { return $this->persona === null ? null : Persona::from($this->persona); }
  public function getMatricula(): ?Matricula { return $this->matricula === null ? null : Matricula::from($this->matricula); }
  public function getVenta(): ?Venta { return $this->venta === null ? null : Venta::from($this->venta); }
  public function getFormaPago(): ?FormaPago { return $this->forma_pago === null ? null : FormaPago::from($this->forma_pago); }
  public function getInstitucion(): ?Institucion { return $this->institucion === null ? null : Institucion::from($this->institucion); }
  public function getMonto(): float { return $this->monto; }
  public function getReferencia(): ?string { return $this->referencia; }
  public function getObservacion(): string { return $this->observacion; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setNumero(string $numero): void { $this->numero = $numero; }
  public function setFecha(string $fecha): void { $this->fecha = $fecha; }
  public function setPersona(mixed $persona): void { $this->persona = $this->extractId($persona); }
  public function setMatricula(mixed $matricula): void { $this->matricula = $this->extractId($matricula); }
  public function setVenta(mixed $venta): void { $this->venta = $this->extractId($venta); }
  public function setFormaPago(mixed $forma_pago): void { $this->forma_pago = $this->extractId($forma_pago); }
  public function setInstitucion(mixed $institucion): void { $this->institucion = $this->extractId($institucion); }
  public function setMonto(float $monto): void { $this->monto = $monto; }
  public function setReferencia(?string $referencia): void { $this->referencia = $referencia; }
  public function setObservacion(string $observacion): void { $this->observacion = $observacion; }
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
      $repositorio = new \Repositories\PagoRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, '', '', 0, null, null, 0, 0, 0.0, null, '', 0);
  }
}
