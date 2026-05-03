<?php

namespace Entitys;

require_once __DIR__ . '/../repository/CiudadRepository.php';

class Ciudad
{
  private $id;
  private $nombre;
  private $provincia;

  public function __construct($id, $nombre, $provincia)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->provincia = $provincia;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  public function getProvincia()
  {
    return $this->provincia;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setProvincia($provincia)
  {
    $this->provincia = $provincia;
  }

  public static function from($id): ?self
  {
    try {
      $repositorio = new \Repositories\CiudadRepository();
      $ciudad = $repositorio->findById($id);
      if ($ciudad) {
        return $ciudad;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', '');
  }
}
