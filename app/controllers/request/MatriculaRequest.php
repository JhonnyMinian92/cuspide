<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class MatriculaRequest
{
    private int $persona;
    private string $fecha_inicio;
    private string $fecha_fin;
    private ?int $membresia;
    private int $institucion;
    private array $clases;
    private array $actividades;
    private ?float $descuento;

    public function __construct(array $data)
    {
        $this->persona      = (int) ($data['persona'] ?? 0);
        $this->fecha_inicio = trim((string) ($data['fecha_inicio'] ?? ''));
        $this->fecha_fin    = trim((string) ($data['fecha_fin'] ?? ''));
        $this->membresia    = isset($data['membresia']) && $data['membresia'] !== null ? (int) $data['membresia'] : null;
        $this->institucion  = (int) ($data['institucion'] ?? 0);
        $this->clases       = is_array($data['clases'] ?? null) ? $data['clases'] : [];
        $this->actividades  = is_array($data['actividades'] ?? null) ? $data['actividades'] : [];
        $this->descuento    = isset($data['descuento']) ? (float) $data['descuento'] : null;

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->persona <= 0) throw new RuntimeException('La persona es obligatoria.', 400);
        if ($this->fecha_inicio === '') throw new RuntimeException('La fecha de inicio es obligatoria.', 400);
        if ($this->fecha_fin === '') throw new RuntimeException('La fecha de fin es obligatoria.', 400);
        if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
        if (empty($this->clases) && empty($this->actividades)) {
            throw new RuntimeException('Debe incluir al menos una clase o actividad.', 400);
        }
    }

    public static function fromJson(): self
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body ?: '', true);
        if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
        return new self($data);
    }

    public function getPersona(): int { return $this->persona; }
    public function getFechaInicio(): string { return $this->fecha_inicio; }
    public function getFechaFin(): string { return $this->fecha_fin; }
    public function getMembresia(): ?int { return $this->membresia; }
    public function getInstitucion(): int { return $this->institucion; }
    public function getClases(): array { return $this->clases; }
    public function getActividades(): array { return $this->actividades; }
    public function getDescuento(): ?float { return $this->descuento; }
}
