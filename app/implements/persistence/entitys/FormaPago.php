<?php

namespace Entitys;

require_once __DIR__ . '/../repository/FormaPagoRepository.php';
require_once __DIR__ . '/Estado.php';

class FormaPago
{
  private ?int $id;
  private string $nombre;
  private string $nemonico;
  private string $descripcion;
  private int $estado;

  public function __construct(?int $id, string $nombre, string $nemonico, string $descripcion, mixed $estado)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->nemonico = $nemonico;
    $this->descripcion = $descripcion;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int { return $this->id; }
  public function getNombre(): string { return $this->nombre; }
  public function getNemonico(): string { return $this->nemonico; }
  public function getDescripcion(): string { return $this->descripcion; }
  public function getEstado(): ?Estado { return $this->estado === null ? null : Estado::from($this->estado); }

  public function setId(?int $id): void { $this->id = $id; }
  public function setNombre(string $nombre): void { $this->nombre = $nombre; }
  public function setNemonico(string $nemonico): void { $this->nemonico = $nemonico; }
  public function setDescripcion(string $descripcion): void { $this->descripcion = $descripcion; }
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
      $repositorio = new \Repositories\FormaPagoRepository();
      $entity = $repositorio->findById($id);
      if ($entity) return $entity;
    } catch (\Exception $e) {
    }
    return new self($id, '', '', '', 0);
  }
}
