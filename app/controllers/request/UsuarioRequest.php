<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class UsuarioRequest
{
    private string $username;
    private ?string $password;
    private string $tipo_user;
    private ?int $persona_id;
    private ?string $imagen;

    public function __construct(array $data)
    {
        $this->username  = trim((string) ($data['username'] ?? ''));
        $this->password  = isset($data['password']) && $data['password'] !== '' ? trim((string) $data['password']) : null;
        $this->tipo_user = trim((string) ($data['tipo_user'] ?? ''));
        $this->persona_id = isset($data['persona_id']) && $data['persona_id'] !== '' ? (int)$data['persona_id'] : null;
        $this->imagen    = isset($data['imagen']) && $data['imagen'] !== '' ? trim((string) $data['imagen']) : null;

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->username === '') throw new RuntimeException('El username es obligatorio.', 400);
        if ($this->tipo_user === '') throw new RuntimeException('El tipo de usuario es obligatorio.', 400);
    }

    public static function fromJson(): self
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body ?: '', true);
        if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
        return new self($data);
    }

    public function getUsername(): string { return $this->username; }
    public function getPassword(): ?string { return $this->password; }
    public function getTipoUser(): string { return $this->tipo_user; }
    public function getPersonaId(): ?int { return $this->persona_id; }
    public function getImagen(): ?string { return $this->imagen; }
}
