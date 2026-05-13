<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class AsignaturaRequest
{
    private string $nombre;
    private string $descripcion;
    private int $institucion;
    private int $estado;

    public function __construct(array $data)
    {
        $this->nombre       = trim((string) ($data['nombre'] ?? ''));
        $this->descripcion  = trim((string) ($data['descripcion'] ?? ''));
        $this->institucion  = (int) ($data['institucion'] ?? 0);
        $this->estado       = (int) ($data['estado'] ?? 1);

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->nombre === '') throw new RuntimeException('El nombre de la asignatura es obligatorio.', 400);
        if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
    }

    public static function fromJson(): self
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body ?: '', true);
        if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
        return new self($data);
    }

    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getInstitucion(): int { return $this->institucion; }
    public function getEstado(): int { return $this->estado; }
}
