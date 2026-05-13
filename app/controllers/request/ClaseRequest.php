<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class ClaseRequest
{
    private string $nombre;
    private int $periodo_academico;
    private int $institucion;
    private ?int $docente;
    private int $cupo_maximo;
    private int $estado;

    public function __construct(array $data)
    {
        $this->nombre             = trim((string) ($data['nombre'] ?? ''));
        $this->periodo_academico  = (int) ($data['periodo_academico'] ?? 0);
        $this->institucion        = (int) ($data['institucion'] ?? 0);
        $this->docente            = isset($data['docente']) && $data['docente'] !== null ? (int) $data['docente'] : null;
        $this->cupo_maximo        = (int) ($data['cupo_maximo'] ?? 0);
        $this->estado             = (int) ($data['estado'] ?? 1);

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->nombre === '') throw new RuntimeException('El nombre de la clase es obligatorio.', 400);
        if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
        if ($this->cupo_maximo <= 0) throw new RuntimeException('El cupo máximo debe ser mayor a cero.', 400);
    }

    public static function fromJson(): self
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body ?: '', true);
        if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
        return new self($data);
    }

    public function getNombre(): string { return $this->nombre; }
    public function getPeriodoAcademico(): int { return $this->periodo_academico; }
    public function getInstitucion(): int { return $this->institucion; }
    public function getDocente(): ?int { return $this->docente; }
    public function getCupoMaximo(): int { return $this->cupo_maximo; }
    public function getEstado(): int { return $this->estado; }
}
