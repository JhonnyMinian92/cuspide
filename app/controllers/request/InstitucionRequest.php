<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class InstitucionRequest
{
    private ?string $ruc;
    private string $razon_social;
    private string $nombre;
    private string $logotipo;
    private int $estado;

    public function __construct(array $data)
    {
        $this->ruc          = isset($data['ruc']) && $data['ruc'] !== '' ? trim((string) $data['ruc']) : null;
        $this->razon_social = trim((string) ($data['razon_social'] ?? ''));
        $this->nombre       = trim((string) ($data['nombre'] ?? ''));
        $this->logotipo     = trim((string) ($data['logotipo'] ?? ''));
        $this->estado       = (int) ($data['estado'] ?? 1);

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->razon_social === '') throw new RuntimeException('La razón social es obligatoria.', 400);
        if ($this->nombre === '') throw new RuntimeException('El nombre es obligatorio.', 400);
    }

    public static function fromJson(): self
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body ?: '', true);
        if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
        return new self($data);
    }

    public function getRuc(): ?string { return $this->ruc; }
    public function getRazonSocial(): string { return $this->razon_social; }
    public function getNombre(): string { return $this->nombre; }
    public function getLogotipo(): string { return $this->logotipo; }
    public function getEstado(): int { return $this->estado; }
}
