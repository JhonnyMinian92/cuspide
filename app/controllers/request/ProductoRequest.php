<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class ProductoRequest
{
    private string $codigo;
    private string $nombre;
    private string $descripcion;
    private int $categoria;
    private float $precio;
    private float $costo;
    private bool $controla_stock;
    private int $institucion;
    private int $estado;

    public function __construct(array $data)
    {
        $this->codigo         = trim((string) ($data['codigo'] ?? ''));
        $this->nombre         = trim((string) ($data['nombre'] ?? ''));
        $this->descripcion    = trim((string) ($data['descripcion'] ?? ''));
        $this->categoria      = (int) ($data['categoria'] ?? 0);
        $this->precio         = (float) ($data['precio'] ?? 0);
        $this->costo          = (float) ($data['costo'] ?? 0);
        $this->controla_stock = (bool) ($data['controla_stock'] ?? true);
        $this->institucion    = (int) ($data['institucion'] ?? 0);
        $this->estado         = (int) ($data['estado'] ?? 1);

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->codigo === '') throw new RuntimeException('El código del producto es obligatorio.', 400);
        if ($this->nombre === '') throw new RuntimeException('El nombre del producto es obligatorio.', 400);
        if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
        if ($this->precio < 0) throw new RuntimeException('El precio no puede ser negativo.', 400);
    }

    public static function fromJson(): self
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body ?: '', true);
        if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
        return new self($data);
    }

    public function getCodigo(): string { return $this->codigo; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getCategoria(): int { return $this->categoria; }
    public function getPrecio(): float { return $this->precio; }
    public function getCosto(): float { return $this->costo; }
    public function getControlaStock(): bool { return $this->controla_stock; }
    public function getInstitucion(): int { return $this->institucion; }
    public function getEstado(): int { return $this->estado; }
}
