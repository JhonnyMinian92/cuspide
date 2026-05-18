<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class CategoriaDeportivaRequest
{
  private string $nombre;
  private ?string $nemonico;
  private int $institucion;
  private ?int $deporte;
  private ?int $edad_minima;
  private ?int $edad_maxima;
  private ?string $descripcion;
  private int $estado;

  public function __construct(array $data)
  {
    $this->nombre      = trim((string) ($data['nombre'] ?? ''));
    $this->nemonico    = isset($data['nemonico']) && $data['nemonico'] !== '' ? trim((string) $data['nemonico']) : null;
    $this->institucion = (int) ($data['institucion'] ?? 0);
    $this->deporte     = isset($data['deporte']) && $data['deporte'] !== null ? (int) $data['deporte'] : null;
    $this->edad_minima = isset($data['edad_minima']) && $data['edad_minima'] !== '' && $data['edad_minima'] !== null ? (int) $data['edad_minima'] : null;
    $this->edad_maxima = isset($data['edad_maxima']) && $data['edad_maxima'] !== '' && $data['edad_maxima'] !== null ? (int) $data['edad_maxima'] : null;
    $this->descripcion = isset($data['descripcion']) && $data['descripcion'] !== '' ? trim((string) $data['descripcion']) : null;
    $this->estado      = (int) ($data['estado'] ?? 1);

    $this->validate();
  }

  private function validate(): void
  {
    if ($this->nombre === '') throw new RuntimeException('El nombre de la categoría es obligatorio.', 400);
    if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
    if ($this->deporte === null) throw new RuntimeException('El deporte es obligatorio.', 400);
  }

  public static function fromJson(): self
  {
    $body = file_get_contents('php://input');
    $data = json_decode($body ?: '', true);
    if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
    return new self($data);
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }
  public function getNemonico(): ?string
  {
    return $this->nemonico;
  }
  public function getInstitucion(): int
  {
    return $this->institucion;
  }
  public function getDeporte(): ?int
  {
    return $this->deporte;
  }
  public function getEdadMinima(): ?int
  {
    return $this->edad_minima;
  }
  public function getEdadMaxima(): ?int
  {
    return $this->edad_maxima;
  }
  public function getDescripcion(): ?string
  {
    return $this->descripcion;
  }
  public function getEstado(): int
  {
    return $this->estado;
  }
}