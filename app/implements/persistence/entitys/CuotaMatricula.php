<?php

namespace Entitys;

require_once __DIR__ . '/../repository/CuotaMatriculaRepository.php';
require_once __DIR__ . '/PlanPagoMatricula.php';
require_once __DIR__ . '/Estado.php';

class CuotaMatricula
{
  private ?int $id;
  private int $plan_pago;
  private int $numero;
  private string $fecha_vencimiento;
  private float $valor;
  private float $saldo;
  private int $estado;

  public function __construct(?int $id, mixed $plan_pago, int $numero, string $fecha_vencimiento, float $valor, float $saldo, mixed $estado)
  {
    $this->id = $id;
    $this->plan_pago = $this->extractId($plan_pago);
    $this->numero = $numero;
    $this->fecha_vencimiento = $fecha_vencimiento;
    $this->valor = $valor;
    $this->saldo = $saldo;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getPlanPago(): ?PlanPagoMatricula { return $this->plan_pago === null ? null : PlanPagoMatricula::from($this->plan_pago); }
  public function getNumero(): int { return $this->numero; }
  public function getFechaVencimiento(): string { return $this->fecha_vencimiento; }
  public function getValor(): float { return $this->valor; }
  public function getSaldo(): float { return $this->saldo; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setPlanPago(mixed $plan_pago): void { $this->plan_pago = $this->extractId($plan_pago); }
  public function setNumero(int $numero): void { $this->numero = $numero; }
  public function setFechaVencimiento(string $fecha_vencimiento): void { $this->fecha_vencimiento = $fecha_vencimiento; }
  public function setValor(float $valor): void { $this->valor = $valor; }
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
      $repositorio = new \Repositories\CuotaMatriculaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, 0, 0, '', 0.0, 0.0, 0);
  }
}
