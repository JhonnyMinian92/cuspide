<?php

namespace Entitys;

require_once __DIR__ . '/../repository/VentaDetalleRepository.php';
require_once __DIR__ . '/Venta.php';
require_once __DIR__ . '/Producto.php';
require_once __DIR__ . '/ProductoVariante.php';

class VentaDetalle
{
  private ?int $id;
  private int $venta;
  private int $producto;
  private ?int $variante;
  private float $cantidad;
  private float $precio_unitario;
  private float $descuento;
  private float $total;

  public function __construct(?int $id, mixed $venta, mixed $producto, mixed $variante, float $cantidad, float $precio_unitario, float $descuento, float $total)
  {
    $this->id = $id;
    $this->venta = $this->extractId($venta);
    $this->producto = $this->extractId($producto);
    $this->variante = $this->extractId($variante);
    $this->cantidad = $cantidad;
    $this->precio_unitario = $precio_unitario;
    $this->descuento = $descuento;
    $this->total = $total;
  }

  public function getId(): ?int { return $this->id; }
  public function getVenta(): ?Venta { return $this->venta === null ? null : Venta::from($this->venta); }
  public function getProducto(): ?Producto { return $this->producto === null ? null : Producto::from($this->producto); }
  public function getVariante(): ?ProductoVariante { return $this->variante === null ? null : ProductoVariante::from($this->variante); }
  public function getCantidad(): float { return $this->cantidad; }
  public function getPrecioUnitario(): float { return $this->precio_unitario; }
  public function getDescuento(): float { return $this->descuento; }
  public function getTotal(): float { return $this->total; }

  public function setId(?int $id): void { $this->id = $id; }
  public function setVenta(mixed $venta): void { $this->venta = $this->extractId($venta); }
  public function setProducto(mixed $producto): void { $this->producto = $this->extractId($producto); }
  public function setVariante(mixed $variante): void { $this->variante = $this->extractId($variante); }
  public function setCantidad(float $cantidad): void { $this->cantidad = $cantidad; }
  public function setPrecioUnitario(float $precio_unitario): void { $this->precio_unitario = $precio_unitario; }
  public function setDescuento(float $descuento): void { $this->descuento = $descuento; }
  public function setTotal(float $total): void { $this->total = $total; }

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
      $repositorio = new \Repositories\VentaDetalleRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, 0, 0, null, 0.0, 0.0, 0.0, 0.0);
  }
}
