<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class HorarioRequest
{
    private string $nombre;
    private int $institucion;
    private ?int $clase;
    private ?int $actividad;
    private ?int $categoria_deportiva;
    private ?int $entrenador;
    private int $dia_semana;
    private string $hora_inicio;
    private string $hora_fin;
    private ?string $fecha_inicio;
    private ?string $fecha_fin;
    private int $cupo_maximo;
    private string $ubicacion;
    private int $estado;

    public function __construct(array $data)
    {
        $this->nombre               = trim((string) ($data['nombre'] ?? ''));
        $this->institucion          = (int) ($data['institucion'] ?? 0);
        $this->clase                = isset($data['clase']) && $data['clase'] !== null ? (int) $data['clase'] : null;
        $this->actividad            = isset($data['actividad']) && $data['actividad'] !== null ? (int) $data['actividad'] : null;
        $this->categoria_deportiva  = isset($data['categoria_deportiva']) && $data['categoria_deportiva'] !== null ? (int) $data['categoria_deportiva'] : null;
        $this->entrenador           = isset($data['entrenador']) && $data['entrenador'] !== null ? (int) $data['entrenador'] : null;
        $this->dia_semana           = (int) ($data['dia_semana'] ?? 1);
        $this->hora_inicio          = trim((string) ($data['hora_inicio'] ?? ''));
        $this->hora_fin             = trim((string) ($data['hora_fin'] ?? ''));
        $this->fecha_inicio         = isset($data['fecha_inicio']) && $data['fecha_inicio'] !== '' ? trim((string) $data['fecha_inicio']) : null;
        $this->fecha_fin            = isset($data['fecha_fin']) && $data['fecha_fin'] !== '' ? trim((string) $data['fecha_fin']) : null;
        $this->cupo_maximo          = (int) ($data['cupo_maximo'] ?? 0);
        $this->ubicacion            = trim((string) ($data['ubicacion'] ?? ''));
        $this->estado               = (int) ($data['estado'] ?? 1);

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->nombre === '') throw new RuntimeException('El nombre del horario es obligatorio.', 400);
        if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
        if ($this->hora_inicio === '') throw new RuntimeException('La hora de inicio es obligatoria.', 400);
        if ($this->hora_fin === '') throw new RuntimeException('La hora de fin es obligatoria.', 400);
        if ($this->clase === null && $this->actividad === null) {
            throw new RuntimeException('Se debe asociar el horario a una clase o actividad.', 400);
        }
    }

    public static function fromJson(): self
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body ?: '', true);
        if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
        return new self($data);
    }

    public function getNombre(): string { return $this->nombre; }
    public function getInstitucion(): int { return $this->institucion; }
    public function getClase(): ?int { return $this->clase; }
    public function getActividad(): ?int { return $this->actividad; }
    public function getCategoriaDeportiva(): ?int { return $this->categoria_deportiva; }
    public function getEntrenador(): ?int { return $this->entrenador; }
    public function getDiaSemana(): int { return $this->dia_semana; }
    public function getHoraInicio(): string { return $this->hora_inicio; }
    public function getHoraFin(): string { return $this->hora_fin; }
    public function getFechaInicio(): ?string { return $this->fecha_inicio; }
    public function getFechaFin(): ?string { return $this->fecha_fin; }
    public function getCupoMaximo(): int { return $this->cupo_maximo; }
    public function getUbicacion(): string { return $this->ubicacion; }
    public function getEstado(): int { return $this->estado; }
}
