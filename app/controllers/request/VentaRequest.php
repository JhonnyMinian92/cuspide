<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class VentaRequest
{
    private int $persona;
    private ?int $matricula;
    private int $institucion;
    private string $tipo;
    private float $descuento;
    private int $forma_pago;
    private ?string $referencia;
    private array $items;

    public function __construct(array $data)
    {
        $this->persona     = (int) ($data['persona'] ?? 0);
        $this->matricula   = isset($data['matricula']) && $data['matricula'] !== null ? (int) $data['matricula'] : null;
        $this->institucion = (int) ($data['institucion'] ?? 0);
        $this->tipo        = trim((string) ($data['tipo'] ?? 'VENTA'));
        $this->descuento   = (float) ($data['descuento'] ?? 0);
        $this->forma_pago  = (int) ($data['forma_pago'] ?? 0);
        $this->referencia  = isset($data['referencia']) && $data['referencia'] !== '' ? trim((string) $data['referencia']) : null;
        $this->items       = is_array($data['items'] ?? null) ? $data['items'] : [];

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->persona <= 0) throw new RuntimeException('La persona es obligatoria.', 400);
        if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
        if (empty($this->items)) throw new RuntimeException('Debe incluir al menos un ítem en la venta.', 400);
        foreach ($this->items as $item) {
            if (empty($item['producto']) || empty($item['cantidad']) || empty($item['precio_unitario'])) {
                throw new RuntimeException('Cada ítem debe tener producto, cantidad y precio_unitario.', 400);
            }
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
    public function getMatricula(): ?int { return $this->matricula; }
    public function getInstitucion(): int { return $this->institucion; }
    public function getTipo(): string { return $this->tipo; }
    public function getDescuento(): float { return $this->descuento; }
    public function getFormaPago(): int { return $this->forma_pago; }
    public function getReferencia(): ?string { return $this->referencia; }
    public function getItems(): array { return $this->items; }
}
