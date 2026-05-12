<?php

namespace Entitys;

require_once __DIR__ . '/../repository/ProductoRepository.php';
require_once __DIR__ . '/ProductoCategoria.php';
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Estado.php';

class Producto
{
  private ?int $id;
  private string $codigo;
  private string $nombre;
  private string $descripcion;
  private int $categoria;
  private float $precio;
  private float $costo;
  private bool $controla_stock;
  private int $institucion;
  private int $estado;

  public function __construct(?int $id, string $codigo, string $nombre, string $descripcion, mixed $categoria, float $precio, float $costo, bool $controla_stock, mixed $institucion, mixed $estado)
  {
    $this->id = $id;
    $this->codigo = $codigo;
    $this->nombre = $nombre;
    $this->descripcion = $descripcion;
    $this->categoria = $this->extractId($categoria);
    $this->precio = $precio;
    $this->costo = $costo;
    $this->controla_stock = $controla_stock;
    $this->institucion = $this->extractId($institucion);
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getCodigo(): string { return $this->codigo; }
  public function getNombre(): string { return $this->nombre; }
  public function getDescripcion(): string { return $this->descripcion; }
  public function getCategoria(): ?ProductoCategoria { return $this->categoria === null ? null : ProductoCategoria::from($this->categoria); }
  public function getPrecio(): float { return $this->precio; }
  public function getCosto(): float { return $this->costo; }
  public function getControlaStock(): bool { return $this->controla_stock; }
  public function getInstitucion(): ?Institucion { return $this->institucion === null ? null : Institucion::from($this->institucion); }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setCodigo(string $codigo): void { $this->codigo = $codigo; }
  public function setNombre(string $nombre): void { $this->nombre = $nombre; }
  public function setDescripcion(string $descripcion): void { $this->descripcion = $descripcion; }
  public function setCategoria(mixed $categoria): void { $this->categoria = $this->extractId($categoria); }
  public function setPrecio(float $precio): void { $this->precio = $precio; }
  public function setCosto(float $costo): void { $this->costo = $costo; }
  public function setControlaStock(bool $controla_stock): void { $this->controla_stock = $controla_stock; }
  public function setInstitucion(mixed $institucion): void { $this->institucion = $this->extractId($institucion); }
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
      $repositorio = new \Repositories\ProductoRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, '', '', '', 0, 0.0, 0.0, true, 0, 0);
  }
}
