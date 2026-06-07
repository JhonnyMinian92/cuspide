<?php

namespace Entitys;

require_once __DIR__ . '/../repository/NivelAcademicoRepository.php';

class NivelAcademico
{
  private ?int $id;
  private string $nombre;
  private ?string $nemonico;

  public function __construct(?int $id, string $nombre, ?string $nemonico = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->nemonico = $nemonico;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function getNemonico(): ?string
  {
    return $this->nemonico;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public function setNemonico(?string $nemonico): void
  {
    $this->nemonico = $nemonico;
  }

  public static function from(int $id): ?self
  {
    if ($id === 0) return null;
    try {
      $repo = new \Repositories\NivelAcademicoRepository();
      return $repo->findById($id);
    } catch (\Exception $e) {
      return null;
    }
  }
}
