<?php

namespace Entitys;

require_once __DIR__ . '/../repository/MembresiaRepository.php';
require_once __DIR__ . '/Estado.php';

class Membresia
{
  private ?int $id;
  private string $titulo;
  private string $precio;
  private float $porcentaje;
  private string $descripcion;
  private string $url_boton;
  private string $fecha_inicio;
  private string $fecha_fin;
  private int $estado;
  private ?int $institucion;
  private string $periodo;

  public function __construct(?int $id, string $titulo, string $precio, float $porcentaje, string $descripcion, string $url_boton, string $fecha_inicio, string $fecha_fin, mixed $estado, mixed $institucion = null, string $periodo = 'mensual')
  {
    $this->id = $id;
    $this->titulo = $titulo;
    $this->precio = $precio;
    $this->porcentaje = $porcentaje;
    $this->descripcion = $descripcion;
    $this->url_boton = $url_boton;
    $this->fecha_inicio = $fecha_inicio;
    $this->fecha_fin = $fecha_fin;
    $this->estado = $this->extractId($estado);
    $this->institucion = $institucion !== null ? (int) $institucion : null;
    $this->periodo = $periodo;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getTitulo(): string
  {
    return $this->titulo;
  }

  public function getPrecio(): string
  {
    return $this->precio;
  }

  public function getPorcentaje(): float
  {
    return $this->porcentaje;
  }

  public function getDescripcion(): string
  {
    return $this->descripcion;
  }

  public function getUrlBoton(): string
  {
    return $this->url_boton;
  }

  public function getFechaInicio(): string
  {
    return $this->fecha_inicio;
  }

  public function getFechaFin(): string
  {
    return $this->fecha_fin;
  }

  public function getPeriodo(): string
  {
    return $this->periodo;
  }

  public function setPeriodo(string $periodo): void
  {
    $this->periodo = $periodo;
  }

  public function getEstado(): ?Estado
  {
    return $this->estado === null ? null : Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setTitulo(string $titulo): void
  {
    $this->titulo = $titulo;
  }

  public function setPrecio(string $precio): void
  {
    $this->precio = $precio;
  }

  public function setPorcentaje(float $porcentaje): void
  {
    $this->porcentaje = $porcentaje;
  }

  public function setDescripcion(string $descripcion): void
  {
    $this->descripcion = $descripcion;
  }

  public function setUrlBoton(string $url_boton): void
  {
    $this->url_boton = $url_boton;
  }

  public function setFechaInicio(string $fecha_inicio): void
  {
    $this->fecha_inicio = $fecha_inicio;
  }

  public function setFechaFin(string $fecha_fin): void
  {
    $this->fecha_fin = $fecha_fin;
  }

  public function getInstitucion(): ?int { return $this->institucion; }
  public function setInstitucion(mixed $v): void { $this->institucion = $v !== null ? (int) $v : null; }

  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

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
      $repositorio = new \Repositories\MembresiaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', '', 0.0, '', '', '', '', 0);
  }
}
