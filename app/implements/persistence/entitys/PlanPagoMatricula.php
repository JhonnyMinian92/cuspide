<?php

namespace Entitys;

require_once __DIR__ . '/../repository/PlanPagoMatriculaRepository.php';
require_once __DIR__ . '/Matricula.php';
require_once __DIR__ . '/PeriodicidadPago.php';
require_once __DIR__ . '/Estado.php';

class PlanPagoMatricula
{
  private ?int $id;
  private int $matricula;
  private int $periodicidad;
  private float $subtotal;
  private float $descuento;
  private float $total;
  private float $saldo;
  private string $fecha_generacion;
  private int $estado;

  public function __construct(?int $id, mixed $matricula, mixed $periodicidad, float $subtotal, float $descuento, float $total, float $saldo, string $fecha_generacion, mixed $estado)
  {
    $this->id = $id;
    $this->matricula = $this->extractId($matricula);
    $this->periodicidad = $this->extractId($periodicidad);
    $this->subtotal = $subtotal;
    $this->descuento = $descuento;
    $this->total = $total;
    $this->saldo = $saldo;
    $this->fecha_generacion = $fecha_generacion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getMatricula(): ?Matricula { return $this->matricula === null ? null : Matricula::from($this->matricula); }
  public function getPeriodicidad(): ?PeriodicidadPago { return $this->periodicidad === null ? null : PeriodicidadPago::from($this->periodicidad); }
  public function getSubtotal(): float { return $this->subtotal; }
  public function getDescuento(): float { return $this->descuento; }
  public function getTotal(): float { return $this->total; }
  public function getSaldo(): float { return $this->saldo; }
  public function getFechaGeneracion(): string { return $this->fecha_generacion; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setMatricula(mixed $matricula): void { $this->matricula = $this->extractId($matricula); }
  public function setPeriodicidad(mixed $periodicidad): void { $this->periodicidad = $this->extractId($periodicidad); }
  public function setSubtotal(float $subtotal): void { $this->subtotal = $subtotal; }
  public function setDescuento(float $descuento): void { $this->descuento = $descuento; }
  public function setTotal(float $total): void { $this->total = $total; }
  public function setSaldo(float $saldo): void { $this->saldo = $saldo; }
  public function setFechaGeneracion(string $fecha_generacion): void { $this->fecha_generacion = $fecha_generacion; }
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
      $repositorio = new \Repositories\PlanPagoMatriculaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, 0, 0, 0.0, 0.0, 0.0, 0.0, '', 0);
  }
}
