<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class PagoRequest
{
    private int $persona;
    private ?int $matricula;
    private ?int $venta;
    private int $forma_pago;
    private int $institucion;
    private float $monto;
    private ?string $referencia;
    private string $observacion;

    public function __construct(array $data)
    {
        $this->persona     = (int) ($data['persona'] ?? 0);
        $this->matricula   = isset($data['matricula']) && $data['matricula'] !== null ? (int) $data['matricula'] : null;
        $this->venta       = isset($data['venta']) && $data['venta'] !== null ? (int) $data['venta'] : null;
        $this->forma_pago  = (int) ($data['forma_pago'] ?? 0);
        $this->institucion = (int) ($data['institucion'] ?? 0);
        $this->monto       = (float) ($data['monto'] ?? 0);
        $this->referencia  = isset($data['referencia']) && $data['referencia'] !== '' ? trim((string) $data['referencia']) : null;
        $this->observacion = trim((string) ($data['observacion'] ?? ''));

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->persona <= 0) throw new RuntimeException('La persona es obligatoria.', 400);
        if ($this->forma_pago <= 0) throw new RuntimeException('La forma de pago es obligatoria.', 400);
        if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
        if ($this->monto <= 0) throw new RuntimeException('El monto debe ser mayor a cero.', 400);
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
    public function getVenta(): ?int { return $this->venta; }
    public function getFormaPago(): int { return $this->forma_pago; }
    public function getInstitucion(): int { return $this->institucion; }
    public function getMonto(): float { return $this->monto; }
    public function getReferencia(): ?string { return $this->referencia; }
    public function getObservacion(): string { return $this->observacion; }
}
