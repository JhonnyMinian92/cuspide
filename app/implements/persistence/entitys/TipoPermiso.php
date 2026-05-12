<?php

namespace Entitys;

require_once __DIR__ . '/../repository/TipoPermisoRepository.php';

class TipoPermiso
{
  private ?int $id;
  private string $nemonico;
  private string $nombre;

  public function __construct(?int $id, string $nemonico, string $nombre)
  {
    $this->id = $id;
    $this->nemonico = $nemonico;
    $this->nombre = $nombre;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getNemonico(): string
  {
    return $this->nemonico;
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setNemonico(string $nemonico): void
  {
    $this->nemonico = $nemonico;
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public static function from(int $id): ?self
  {
    try {
      $repositorio = new \Repositories\TipoPermisoRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', '');
  }
}
