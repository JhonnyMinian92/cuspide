<?php

namespace Entitys;

require_once __DIR__ . '/../repository/FichaMedicaRepository.php';
require_once __DIR__ . '/PerfilMedico.php';
require_once __DIR__ . '/Estado.php';

class FichaMedica
{
  private ?int $id;
  private string $fecha;
  private int $perfil_medico;
  private int $estado;

  public function __construct(?int $id, string $fecha, mixed $perfil_medico, mixed $estado)
  {
    $this->id = $id;
    $this->fecha = $fecha;
    $this->perfil_medico = $this->extractId($perfil_medico);
    $this->estado = $this->extractId($estado);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getFecha(): string
  {
    return $this->fecha;
  }

  public function getPerfilMedico(): ?PerfilMedico
  {
    return $this->perfil_medico === null ? null : PerfilMedico::from($this->perfil_medico);
  }

  public function getEstado(): ?Estado
  {
    return $this->estado === null ? null : Estado::from($this->estado);
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function setFecha(string $fecha): void
  {
    $this->fecha = $fecha;
  }

  public function setPerfilMedico(mixed $perfil_medico): void
  {
    $this->perfil_medico = $this->extractId($perfil_medico);
  }

  public function setEstado(mixed $estado): void
  {
    $this->estado = $this->extractId($estado);
  }

  private function extractId(mixed $value): ?int
  {
    if ($value === null) {
      return null;
    }
    if (is_object($value) && method_exists($value, 'getId')) {
      return $value->getId();
    }
    return (int) $value;
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
    return new self($id, '', 0, 0);
  }
}
