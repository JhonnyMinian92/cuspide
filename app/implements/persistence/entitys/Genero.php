<?php

namespace Entitys;

require_once __DIR__ . '/../repository/GeneroRepository.php';

class Genero
{

  private $id;
  private $nombre;

  public function __construct($id, $nombre)
  {
    $this->id = $id;
    $this->nombre = $nombre;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public static function from($id): ?self
  {
    try {
      $repositorio = new \Repositories\GeneroRepository();
      $genero = $repositorio->findById($id);
      if ($genero) {
        return $genero;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '');
  }
}
