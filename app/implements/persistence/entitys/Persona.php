<?php

namespace Entitys;

require_once __DIR__ . '/../repository/PersonaRepository.php';
require_once __DIR__ . '/Nacionalidad.php';
require_once __DIR__ . '/Genero.php';
require_once __DIR__ . '/Etnia.php';
require_once __DIR__ . '/Ciudad.php';
require_once __DIR__ . '/User.php';

class Persona
{

  private $id;
  private $identificacion;
  private $primer_nombre;
  private $segundo_nombre;
  private $primer_apellido;
  private $segundo_apellido;
  private $nacionalidad;
  private $genero;
  private $etnia;
  private $fecha_nacimiento;
  private $ciudad;
  private $direccion;
  private $user;

  public function __construct($id, $identificacion, $primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido, $nacionalidad, $genero, $etnia, $fecha_nacimiento, $ciudad, $direccion, $user)
  {
    $this->id = $id;
    $this->identificacion = $identificacion;
    $this->primer_nombre = $primer_nombre;
    $this->segundo_nombre = $segundo_nombre;
    $this->primer_apellido = $primer_apellido;
    $this->segundo_apellido = $segundo_apellido;
    $this->nacionalidad = $this->extractId($nacionalidad);
    $this->genero = $this->extractId($genero);
    $this->etnia = $this->extractId($etnia);
    $this->fecha_nacimiento = $fecha_nacimiento;
    $this->ciudad = $this->extractId($ciudad);
    $this->direccion = $direccion;
    $this->user = $this->extractId($user);
  }

  public function getId()
  {
    return $this->id;
  }

  public function getIdentificacion()
  {
    return $this->identificacion;
  }

  public function getPrimerNombre()
  {
    return $this->primer_nombre;
  }

  public function getSegundoNombre()
  {
    return $this->segundo_nombre;
  }

  public function getPrimerApellido()
  {
    return $this->primer_apellido;
  }

  public function getSegundoApellido()
  {
    return $this->segundo_apellido;
  }

  public function getNacionalidad()
  {
    return Nacionalidad::from($this->nacionalidad);
  }

  public function getGenero()
  {
    return Genero::from($this->genero);
  }

  public function getEtnia()
  {
    return Etnia::from($this->etnia);
  }

  public function getFechaNacimiento()
  {
    return $this->fecha_nacimiento;
  }

  public function getCiudad()
  {
    return Ciudad::from($this->ciudad);
  }

  public function getDireccion()
  {
    return $this->direccion;
  }

  public function getUser()
  {
    return User::from($this->user);
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setIdentificacion($identificacion)
  {
    $this->identificacion = $identificacion;
  }

  public function setPrimerNombre($primer_nombre)
  {
    $this->primer_nombre = $primer_nombre;
  }

  public function setSegundoNombre($segundo_nombre)
  {
    $this->segundo_nombre = $segundo_nombre;
  }

  public function setPrimerApellido($primer_apellido)
  {
    $this->primer_apellido = $primer_apellido;
  }

  public function setSegundoApellido($segundo_apellido)
  {
    $this->segundo_apellido = $segundo_apellido;
  }

  public function setNacionalidad($nacionalidad)
  {
    $this->nacionalidad = $this->extractId($nacionalidad);
  }

  public function setGenero($genero)
  {
    $this->genero = $this->extractId($genero);
  }

  public function setEtnia($etnia)
  {
    $this->etnia = $this->extractId($etnia);
  }

  public function setFechaNacimiento($fecha_nacimiento)
  {
    $this->fecha_nacimiento = $fecha_nacimiento;
  }

  public function setCiudad($ciudad)
  {
    $this->ciudad = $this->extractId($ciudad);
  }

  public function setDireccion($direccion)
  {
    $this->direccion = $direccion;
  }

  public function setUser($user)
  {
    $this->user = $this->extractId($user);
  }

  private function extractId($value)
  {
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return $value;
  }

  public static function from($id): ?self
  {
    try {
      $repositorio = new \Repositories\PersonaRepository();
      $persona = $repositorio->findById($id);
      if ($persona) {
        return $persona;
      }
    } catch (\Exception $e) {
    }
    return new self($id, '', '', '', '', '', 0, 0, 0, '', '', '', 0);
  }
}
