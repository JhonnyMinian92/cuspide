<?php

namespace Entitys;

require_once __DIR__ . '/../repository/NivelRepository.php';

class Nivel
{
  private ?int $id;
  private string $nombre;

  public function __construct(?int $id, string $nombre)
  {
    $this->id = $id;
    $this->nombre = $nombre;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public static function from(int $id): ?self
  {
    if ($id === 0) return null;
    try {
      $repo = new \Repositories\NivelRepository();
      return $repo->findById($id);
    } catch (\Exception $e) {
      return null;
    }
  }
}
