<?php

namespace Entitys;

require_once __DIR__ . '/../repository/MovimientoInventarioRepository.php';
require_once __DIR__ . '/Producto.php';
require_once __DIR__ . '/ProductoVariante.php';
require_once __DIR__ . '/Institucion.php';

class MovimientoInventario
{
  private ?int $id;
  private int $producto;
  private ?int $variante;
  private int $institucion;
  private string $tipo;
  private float $cantidad;
  private float $stock_anterior;
  private float $stock_nuevo;
  private ?string $referencia;
  private string $fecha;
  private string $observacion;

  public function __construct(?int $id, mixed $producto, mixed $variante, mixed $institucion, string $tipo, float $cantidad, float $stock_anterior, float $stock_nuevo, ?string $referencia, string $fecha, string $observacion)
  {
    $this->id = $id;
    $this->producto = $this->extractId($producto);
    $this->variante = $this->extractId($variante);
    $this->institucion = $this->extractId($institucion);
    $this->tipo = $tipo;
    $this->cantidad = $cantidad;
    $this->stock_anterior = $stock_anterior;
    $this->stock_nuevo = $stock_nuevo;
    $this->referencia = $referencia;
    $this->fecha = $fecha;
    $this->observacion = $observacion;
  }

  public function getId(): ?int { return $this->id; }
  public function getProducto(): ?Producto { return $this->producto === null ? null : Producto::from($this->producto); }
  public function getVariante(): ?ProductoVariante { return $this->variante === null ? null : ProductoVariante::from($this->variante); }
  public function getInstitucion(): ?Institucion { return $this->institucion === null ? null : Institucion::from($this->institucion); }
  public function getTipo(): string { return $this->tipo; }
  public function getCantidad(): float { return $this->cantidad; }
  public function getStockAnterior(): float { return $this->stock_anterior; }
  public function getStockNuevo(): float { return $this->stock_nuevo; }
  public function getReferencia(): ?string { return $this->referencia; }
  public function getFecha(): string { return $this->fecha; }
  public function getObservacion(): string { return $this->observacion; }

  public function setId(?int $id): void { $this->id = $id; }
  public function setProducto(mixed $producto): void { $this->producto = $this->extractId($producto); }
  public function setVariante(mixed $variante): void { $this->variante = $this->extractId($variante); }
  public function setInstitucion(mixed $institucion): void { $this->institucion = $this->extractId($institucion); }
  public function setTipo(string $tipo): void { $this->tipo = $tipo; }
  public function setCantidad(float $cantidad): void { $this->cantidad = $cantidad; }
  public function setStockAnterior(float $stock_anterior): void { $this->stock_anterior = $stock_anterior; }
  public function setStockNuevo(float $stock_nuevo): void { $this->stock_nuevo = $stock_nuevo; }
  public function setReferencia(?string $referencia): void { $this->referencia = $referencia; }
  public function setFecha(string $fecha): void { $this->fecha = $fecha; }
  public function setObservacion(string $observacion): void { $this->observacion = $observacion; }

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
      $repositorio = new \Repositories\MovimientoInventarioRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, 0, null, 0, '', 0.0, 0.0, 0.0, null, '', '');
  }
}
