<?php

namespace Entitys;

require_once __DIR__ . '/../repository/FichaMedicaRepository.php';
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Estado.php';

class FichaMedica
{
  private ?int $id;
  private int $persona;
  private ?string $grupo_sanguineo;
  private ?string $alergias;
  private ?string $enfermedades_preexistentes;
  private ?string $medicamentos_actuales;
  private ?string $contacto_emergencia_nombre;
  private ?string $contacto_emergencia_telefono;
  private int $estado;

  public function __construct(?int $id, mixed $persona, ?string $grupo_sanguineo, ?string $alergias, ?string $enfermedades_preexistentes, ?string $medicamentos_actuales, ?string $contacto_emergencia_nombre, ?string $contacto_emergencia_telefono, mixed $estado)
  {
    $this->id = $id;
    $this->persona = $this->extractId($persona);
    $this->grupo_sanguineo = $grupo_sanguineo;
    $this->alergias = $alergias;
    $this->enfermedades_preexistentes = $enfermedades_preexistentes;
    $this->medicamentos_actuales = $medicamentos_actuales;
    $this->contacto_emergencia_nombre = $contacto_emergencia_nombre;
    $this->contacto_emergencia_telefono = $contacto_emergencia_telefono;
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }
  public function getPersona(): ?Persona
  {
    return Persona::from($this->persona);
  }
  public function getGrupoSanguineo(): ?string
  {
    return $this->grupo_sanguineo;
  }
  public function getAlergias(): ?string
  {
    return $this->alergias;
  }
  public function getEnfermedadesPreexistentes(): ?string
  {
    return $this->enfermedades_preexistentes;
  }
  public function getMedicamentosActuales(): ?string
  {
    return $this->medicamentos_actuales;
  }
  public function getContactoEmergenciaNombre(): ?string
  {
    return $this->contacto_emergencia_nombre;
  }
  public function getContactoEmergenciaTelefono(): ?string
  {
    return $this->contacto_emergencia_telefono;
  }
  public function getEstado(): ?Estado
  {
    return Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }
  public function setPersona(mixed $persona): void
  {
    $this->persona = $this->extractId($persona);
  }
  public function setGrupoSanguineo(?string $grupo_sanguineo): void
  {
    $this->grupo_sanguineo = $grupo_sanguineo;
  }
  public function setAlergias(?string $alergias): void
  {
    $this->alergias = $alergias;
  }
  public function setEnfermedadesPreexistentes(?string $enfermedades_preexistentes): void
  {
    $this->enfermedades_preexistentes = $enfermedades_preexistentes;
  }
  public function setMedicamentosActuales(?string $medicamentos_actuales): void
  {
    $this->medicamentos_actuales = $medicamentos_actuales;
  }
  public function setContactoEmergenciaNombre(?string $contacto_emergencia_nombre): void
  {
    $this->contacto_emergencia_nombre = $contacto_emergencia_nombre;
  }
  public function setContactoEmergenciaTelefono(?string $contacto_emergencia_telefono): void
  {
    $this->contacto_emergencia_telefono = $contacto_emergencia_telefono;
  }
  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  private function extractId(mixed $value): int
  {
    if (is_object($value) && method_exists($value, 'getId')) {
      return (int)$value->getId();
    }
    return (int)$value;
  }

  public static function from(int $id): ?self
  {
    try {
      $repositorio = new \Repositories\FichaMedicaRepository();
      $entity = $repositorio->findById($id);
      if ($entity) {
        return $entity;
      }
    } catch (\Exception $e) {
    }
    return new self($id, 0, null, null, null, null, null, null, 0);
  }
}
