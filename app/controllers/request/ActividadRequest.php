<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class ActividadRequest
{
    private string $nombre;
    private int $deporte;
    private float $precio;
    private int $nivel;
    private string $observacion;
    private int $institucion;
    private int $estado;

    public function __construct(array $data)
    {
        $this->nombre       = trim((string) ($data['nombre'] ?? ''));
        $this->deporte      = (int) ($data['deporte'] ?? 0);
        $this->precio       = (float) ($data['precio'] ?? 0);
        $this->nivel        = (int) ($data['nivel'] ?? 0);
        $this->observacion  = trim((string) ($data['observacion'] ?? ''));
        $this->institucion  = (int) ($data['institucion'] ?? 0);
        $this->estado       = (int) ($data['estado'] ?? 1);

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->nombre === '') throw new RuntimeException('El nombre de la actividad es obligatorio.', 400);
        if ($this->deporte <= 0) throw new RuntimeException('El deporte es obligatorio.', 400);
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
    public function getDeporte(): int { return $this->deporte; }
    public function getPrecio(): float { return $this->precio; }
    public function getNivel(): int { return $this->nivel; }
    public function getObservacion(): string { return $this->observacion; }
    public function getInstitucion(): int { return $this->institucion; }
    public function getEstado(): int { return $this->estado; }
}
