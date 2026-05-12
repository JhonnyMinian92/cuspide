<?php

namespace Entitys;

require_once __DIR__ . '/../repository/ProductoVarianteRepository.php';
require_once __DIR__ . '/Producto.php';
require_once __DIR__ . '/Estado.php';

class ProductoVariante
{
  private ?int $id;
  private int $producto;
  private string $sku;
  private ?string $talla;
  private ?string $color;
  private ?string $genero;
  private float $precio;
  private int $estado;

  public function __construct(?int $id, mixed $producto, string $sku, ?string $talla, ?string $color, ?string $genero, float $precio, mixed $estado)
  {
    $this->id = $id;
    $this->producto = $this->extractId($producto);
    $this->sku = $sku;
    $this->talla = $talla;
    $this->color = $color;
    $this->genero = $genero;
    $this->precio = $precio;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getProducto(): ?Producto { return $this->producto === null ? null : Producto::from($this->producto); }
  public function getSku(): string { return $this->sku; }
  public function getTalla(): ?string { return $this->talla; }
  public function getColor(): ?string { return $this->color; }
  public function getGenero(): ?string { return $this->genero; }
  public function getPrecio(): float { return $this->precio; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setProducto(mixed $producto): void { $this->producto = $this->extractId($producto); }
  public function setSku(string $sku): void { $this->sku = $sku; }
  public function setTalla(?string $talla): void { $this->talla = $talla; }
  public function setColor(?string $color): void { $this->color = $color; }
  public function setGenero(?string $genero): void { $this->genero = $genero; }
  public function setPrecio(float $precio): void { $this->precio = $precio; }
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
      $repositorio = new \Repositories\ProductoVarianteRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, 0, '', null, null, null, 0.0, 0);
  }
}
