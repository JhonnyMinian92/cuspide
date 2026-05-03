<?php

namespace Entitys;

require_once __DIR__ . '/../repository/InstitucionRepository.php';
require_once __DIR__ . '/Estado.php';

class Institucion
{

  private $id;
  private $ruc;
  private $razon_social;
  private $nombre;
  private $logotipo;
  private $estado;

  public function __construct($id, $ruc, $razon_social, $nombre, $logotipo, $estado)
  {
    $this->id = $id;
    $this->ruc = $ruc;
    $this->razon_social = $razon_social;
    $this->nombre = $nombre;
    $this->logotipo = $logotipo;
    $this->estado = is_string($estado) ? $estado : $estado->getId();
  }

  public function getId()
  {
    return $this->id;
  }

  public function getRuc()
  {
    return $this->ruc;
  }

  public function getRazonSocial()
  {
    return $this->razon_social;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  public function getLogotipo()
  {
    return $this->logotipo;
  }

  public function getEstado()
  {
    return Estado::from($this->estado);
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setRuc($ruc)
  {
    $this->ruc = $ruc;
  }

  public function setRazonSocial($razon_social)
  {
    $this->razon_social = $razon_social;
  }

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setLogotipo($logotipo)
  {
    $this->logotipo = $logotipo;
  }

  public function setEstado($estado)
  {
    $this->estado = is_string($estado) ? $estado : $estado->getId();
  }

  public static function from($id): ?self
  {
    try {
      $repositorio = new \Repositories\InstitucionRepository();
      $institucion = $repositorio->findById($id);
      if ($institucion) {
        return $institucion;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', '', '', '', 1);
  }
}
