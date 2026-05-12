<?php

namespace Entitys;

require_once __DIR__ . '/../repository/PagoDetalleRepository.php';
require_once __DIR__ . '/Pago.php';
require_once __DIR__ . '/CuotaMatricula.php';
require_once __DIR__ . '/Venta.php';

class PagoDetalle
{
  private ?int $id;
  private int $pago;
  private ?int $cuota_matricula;
  private ?int $venta;
  private float $valor;

  public function __construct(?int $id, mixed $pago, mixed $cuota_matricula, mixed $venta, float $valor)
  {
    $this->id = $id;
    $this->pago = $this->extractId($pago);
    $this->cuota_matricula = $this->extractId($cuota_matricula);
    $this->venta = $this->extractId($venta);
    $this->valor = $valor;
  }

  public function getId(): ?int { return $this->id; }
  public function getPago(): ?Pago { return $this->pago === null ? null : Pago::from($this->pago); }
  public function getCuotaMatricula(): ?CuotaMatricula { return $this->cuota_matricula === null ? null : CuotaMatricula::from($this->cuota_matricula); }
  public function getVenta(): ?Venta { return $this->venta === null ? null : Venta::from($this->venta); }
  public function getValor(): float { return $this->valor; }

  public function setId(?int $id): void { $this->id = $id; }
  public function setPago(mixed $pago): void { $this->pago = $this->extractId($pago); }
  public function setCuotaMatricula(mixed $cuota_matricula): void { $this->cuota_matricula = $this->extractId($cuota_matricula); }
  public function setVenta(mixed $venta): void { $this->venta = $this->extractId($venta); }
  public function setValor(float $valor): void { $this->valor = $valor; }

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
      $repositorio = new \Repositories\PagoDetalleRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, 0, null, null, 0.0);
  }
}
