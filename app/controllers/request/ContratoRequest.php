<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class ContratoRequest
{
  public int $persona_id;
  public string $fecha_inscripcion;
  public string $fecha_inicio;
  public string $fecha_fin;
  public int $nivel;
  public int $institucion;

  public function __construct(array $data)
  {
    $this->persona_id        = (int)($data['persona_id'] ?? 0);
    $this->fecha_inscripcion = (string)($data['fecha_inscripcion'] ?? '');
    $this->fecha_inicio      = (string)($data['fecha_inicio'] ?? '');
    $this->fecha_fin         = (string)($data['fecha_fin'] ?? '');
    $this->nivel             = (int)($data['nivel'] ?? 0);
    $this->institucion       = (int)($data['institucion'] ?? 0);

    $this->validate();
  }

  private function validate(): void
  {
    if ($this->persona_id <= 0) throw new RuntimeException('El empleado es obligatorio.', 400);
    if ($this->fecha_inicio === '') throw new RuntimeException('La fecha de inicio es obligatoria.', 400);
    if ($this->fecha_fin === '') throw new RuntimeException('La fecha de fin es obligatoria.', 400);
    if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
  }

  public static function fromJson(): self
  {
    $body = file_get_contents('php://input');
    $data = json_decode($body ?: '', true);
    if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
    return new self($data);
  }
}
