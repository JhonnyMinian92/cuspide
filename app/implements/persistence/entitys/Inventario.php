<?php

namespace Entitys;

require_once __DIR__ . '/../repository/InventarioRepository.php';
require_once __DIR__ . '/Producto.php';
require_once __DIR__ . '/ProductoVariante.php';
require_once __DIR__ . '/Institucion.php';

class Inventario
{
  private ?int $id;
  private int $producto;
  private ?int $variante;
  private int $institucion;
  private float $stock_actual;
  private float $stock_minimo;
  private string $fecha_actualizacion;

  public function __construct(?int $id, mixed $producto, mixed $variante, mixed $institucion, float $stock_actual, float $stock_minimo, string $fecha_actualizacion)
  {
    $this->id = $id;
    $this->producto = $this->extractId($producto);
    $this->variante = $this->extractId($variante);
    $this->institucion = $this->extractId($institucion);
    $this->stock_actual = $stock_actual;
    $this->stock_minimo = $stock_minimo;
    $this->fecha_actualizacion = $fecha_actualizacion;
  }

  public function getId(): ?int { return $this->id; }
  public function getProducto(): ?Producto { return $this->producto === null ? null : Producto::from($this->producto); }
  public function getVariante(): ?ProductoVariante { return $this->variante === null ? null : ProductoVariante::from($this->variante); }
  public function getInstitucion(): ?Institucion { return $this->institucion === null ? null : Institucion::from($this->institucion); }
  public function getStockActual(): float { return $this->stock_actual; }
  public function getStockMinimo(): float { return $this->stock_minimo; }
  public function getFechaActualizacion(): string { return $this->fecha_actualizacion; }

  public function setId(?int $id): void { $this->id = $id; }
  public function setProducto(mixed $producto): void { $this->producto = $this->extractId($producto); }
  public function setVariante(mixed $variante): void { $this->variante = $this->extractId($variante); }
  public function setInstitucion(mixed $institucion): void { $this->institucion = $this->extractId($institucion); }
  public function setStockActual(float $stock_actual): void { $this->stock_actual = $stock_actual; }
  public function setStockMinimo(float $stock_minimo): void { $this->stock_minimo = $stock_minimo; }
  public function setFechaActualizacion(string $fecha_actualizacion): void { $this->fecha_actualizacion = $fecha_actualizacion; }

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
      $repositorio = new \Repositories\InventarioRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, 0, null, 0, 0.0, 0.0, '');
  }
}
