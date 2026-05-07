<?php

namespace Entitys;

require_once __DIR__ . '/../repository/TipoContactoRepository.php';

class TipoContacto
{
  private $id;
  private $nombre;
  private $nemonico;

  public function __construct($id, $nombre, $nemonico)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->nemonico = $nemonico;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  public function getNemonico()
  {
    return $this->nemonico;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setNemonico($nemonico)
  {
    $this->nemonico = $nemonico;
  }

  public static function from($id): ?self
  {
    try {
      $repositorio = new \Repositories\TipoContactoRepository();
      $tipoContacto = $repositorio->findById($id);
      if ($tipoContacto) {
        return $tipoContacto;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', '');
  }
}