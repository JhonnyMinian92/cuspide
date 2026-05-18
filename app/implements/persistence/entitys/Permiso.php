<?php

namespace Entitys;

class Permiso
{
    private ?int $id;
    private int $persona;
    private ?int $matricula;
    private ?int $clase;
    private ?int $actividad;
    private string $fecha_inicio;
    private string $fecha_fin;
    private ?float $horas_por_dia;
    private string $motivo;
    private ?string $observacion;
    private int $estado;
    private ?string $fecha_registro;

    public function __construct(
        ?int $id,
        int $persona,
        ?int $matricula,
        ?int $clase,
        ?int $actividad,
        string $fecha_inicio,
        string $fecha_fin,
        ?float $horas_por_dia,
        string $motivo,
        ?string $observacion,
        int $estado = 1,
        ?string $fecha_registro = null
    ) {
        $this->id             = $id;
        $this->persona        = $persona;
        $this->matricula      = $matricula;
        $this->clase          = $clase;
        $this->actividad      = $actividad;
        $this->fecha_inicio   = $fecha_inicio;
        $this->fecha_fin      = $fecha_fin;
        $this->horas_por_dia  = $horas_por_dia;
        $this->motivo         = $motivo;
        $this->observacion    = $observacion;
        $this->estado         = $estado;
        $this->fecha_registro = $fecha_registro;
    }

    public function getId(): ?int { return $this->id; }
    public function getPersona(): int { return $this->persona; }
    public function getMatricula(): ?int { return $this->matricula; }
    public function getClase(): ?int { return $this->clase; }
    public function getActividad(): ?int { return $this->actividad; }
    public function getFechaInicio(): string { return $this->fecha_inicio; }
    public function getFechaFin(): string { return $this->fecha_fin; }
    public function getHorasPorDia(): ?float { return $this->horas_por_dia; }
    public function getMotivo(): string { return $this->motivo; }
    public function getObservacion(): ?string { return $this->observacion; }
    public function getEstado(): int { return $this->estado; }
    public function getFechaRegistro(): ?string { return $this->fecha_registro; }

    public function setId(?int $id): void { $this->id = $id; }
}
