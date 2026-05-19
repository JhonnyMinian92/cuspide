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

  private ?int $id;
  private string $identificacion;
  private string $primer_nombre;
  private ?string $segundo_nombre;
  private string $primer_apellido;
  private ?string $segundo_apellido;
  private ?int $nacionalidad;
  private ?int $genero;
  private ?int $etnia;
  private ?string $fecha_nacimiento;
  private ?int $ciudad;
  private ?string $direccion;
  private ?int $user;

  public function __construct(?int $id, string $identificacion, string $primer_nombre, ?string $segundo_nombre, string $primer_apellido, ?string $segundo_apellido, mixed $nacionalidad, mixed $genero, mixed $etnia, mixed $fecha_nacimiento, mixed $ciudad, mixed $direccion, mixed $user)
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
    $this->fecha_nacimiento = ($fecha_nacimiento !== null && $fecha_nacimiento !== '') ? (string)$fecha_nacimiento : null;
    $this->ciudad = $this->extractId($ciudad);
    $this->direccion = ($direccion !== null && $direccion !== '') ? (string)$direccion : null;
    $this->user = $this->extractId($user);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getIdentificacion(): string
  {
    return $this->identificacion;
  }

  public function getPrimerNombre(): string
  {
    return $this->primer_nombre;
  }

  public function getSegundoNombre(): ?string
  {
    return $this->segundo_nombre;
  }

  public function getPrimerApellido(): string
  {
    return $this->primer_apellido;
  }

  public function getSegundoApellido(): ?string
  {
    return $this->segundo_apellido;
  }

  public function getNacionalidad(): ?Nacionalidad
  {
    return $this->nacionalidad ? Nacionalidad::from($this->nacionalidad) : null;
  }

  public function getGenero(): ?Genero
  {
    return $this->genero ? Genero::from($this->genero) : null;
  }

  public function getEtnia(): ?Etnia
  {
    return $this->etnia ? Etnia::from($this->etnia) : null;
  }

  public function getFechaNacimiento(): ?string
  {
    return $this->fecha_nacimiento;
  }

  public function getCiudad(): ?Ciudad
  {
    return $this->ciudad ? Ciudad::from($this->ciudad) : null;
  }

  public function getDireccion(): ?string
  {
    return $this->direccion;
  }

  public function getUser(): ?User
  {
    return $this->user ? User::from($this->user) : null;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setIdentificacion(string $identificacion): void
  {
    $this->identificacion = $identificacion;
  }

  public function setPrimerNombre(string $primer_nombre): void
  {
    $this->primer_nombre = $primer_nombre;
  }

  public function setSegundoNombre(?string $segundo_nombre): void
  {
    $this->segundo_nombre = $segundo_nombre;
  }

  public function setPrimerApellido(string $primer_apellido): void
  {
    $this->primer_apellido = $primer_apellido;
  }

  public function setSegundoApellido(?string $segundo_apellido): void
  {
    $this->segundo_apellido = $segundo_apellido;
  }

  public function setNacionalidad(mixed $nacionalidad): void
  {
    $this->nacionalidad = $this->extractId($nacionalidad);
  }

  public function setGenero(mixed $genero): void
  {
    $this->genero = $this->extractId($genero);
  }

  public function setEtnia(mixed $etnia): void
  {
    $this->etnia = $this->extractId($etnia);
  }

  public function setFechaNacimiento(mixed $fecha_nacimiento): void
  {
    $this->fecha_nacimiento = ($fecha_nacimiento !== null && $fecha_nacimiento !== '') ? (string)$fecha_nacimiento : null;
  }

  public function setCiudad(mixed $ciudad): void
  {
    $this->ciudad = $this->extractId($ciudad);
  }

  public function setDireccion(mixed $direccion): void
  {
    $this->direccion = ($direccion !== null && $direccion !== '') ? (string)$direccion : null;
  }

  public function setUser(mixed $user): void
  {
    $this->user = $this->extractId($user);
  }

  private function extractId(mixed $value): ?int
  {
    if ($value === null || $value === '' || $value === '0') {
      return null;
    }
    if (is_object($value) && method_exists($value, 'getId')) {
      $id = $value->getId();
      return ($id !== null && (int)$id > 0) ? (int)$id : null;
    }
    $int = (int)$value;
    return $int > 0 ? $int : null;
  }

  public static function from($id): ?self
  {
    if ($id === null || $id === 0 || $id === '') {
      return null;
    }
    try {
      $repositorio = new \Repositories\PersonaRepository();
      $persona = $repositorio->findById((int)$id);
      if ($persona) {
        return $persona;
      }
    } catch (\Exception $e) {
    }
    return null;
  }
}
