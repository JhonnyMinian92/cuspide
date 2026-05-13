<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class MembresiaRequest
{
    private string $titulo;
    private string $precio;
    private float $porcentaje;
    private string $descripcion;
    private string $url_boton;
    private string $fecha_inicio;
    private string $fecha_fin;
    private int $estado;

    public function __construct(array $data)
    {
        $this->titulo       = trim((string) ($data['titulo'] ?? ''));
        $this->precio       = trim((string) ($data['precio'] ?? '0'));
        $this->porcentaje   = (float) ($data['porcentaje'] ?? 0);
        $this->descripcion  = trim((string) ($data['descripcion'] ?? ''));
        $this->url_boton    = trim((string) ($data['url_boton'] ?? ''));
        $this->fecha_inicio = trim((string) ($data['fecha_inicio'] ?? ''));
        $this->fecha_fin    = trim((string) ($data['fecha_fin'] ?? ''));
        $this->estado       = (int) ($data['estado'] ?? 1);

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->titulo === '') throw new RuntimeException('El título es obligatorio.', 400);
        if ($this->fecha_inicio === '') throw new RuntimeException('La fecha de inicio es obligatoria.', 400);
        if ($this->fecha_fin === '') throw new RuntimeException('La fecha de fin es obligatoria.', 400);
    }

    public static function fromJson(): self
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body ?: '', true);
        if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
        return new self($data);
    }

    public function getTitulo(): string { return $this->titulo; }
    public function getPrecio(): string { return $this->precio; }
    public function getPorcentaje(): float { return $this->porcentaje; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getUrlBoton(): string { return $this->url_boton; }
    public function getFechaInicio(): string { return $this->fecha_inicio; }
    public function getFechaFin(): string { return $this->fecha_fin; }
    public function getEstado(): int { return $this->estado; }
}
