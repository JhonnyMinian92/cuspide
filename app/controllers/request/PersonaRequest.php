<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class PersonaRequest
{
    private string $identificacion;
    private string $primer_nombre;
    private ?string $segundo_nombre;
    private string $primer_apellido;
    private ?string $segundo_apellido;
    private int $nacionalidad;
    private int $genero;
    private int $etnia;
    private string $fecha_nacimiento;
    private int $ciudad;
    private string $direccion;
    private ?string $username;
    private ?string $password;
    private ?string $tipo_user;
    private int $tipo_persona;
    private int $institucion;
    private ?string $grupo_sanguineo;
    private ?string $alergias;
    private ?string $enfermedades;
    private ?string $medicamentos;
    private ?string $contacto_emergencia_nombre;
    private ?string $contacto_emergencia_telefono;

    public function __construct(array $data)
    {
        $this->identificacion         = trim((string) ($data['identificacion'] ?? ''));
        $this->primer_nombre          = trim((string) ($data['primer_nombre'] ?? ''));
        $this->segundo_nombre         = isset($data['segundo_nombre']) ? trim((string) $data['segundo_nombre']) : null;
        $this->primer_apellido        = trim((string) ($data['primer_apellido'] ?? ''));
        $this->segundo_apellido       = isset($data['segundo_apellido']) ? trim((string) $data['segundo_apellido']) : null;
        $this->nacionalidad           = (int) ($data['nacionalidad'] ?? 0);
        $this->genero                 = (int) ($data['genero'] ?? 0);
        $this->etnia                  = (int) ($data['etnia'] ?? 0);
        $this->fecha_nacimiento       = trim((string) ($data['fecha_nacimiento'] ?? ''));
        $this->ciudad                 = (int) ($data['ciudad'] ?? 0);
        $this->direccion              = trim((string) ($data['direccion'] ?? ''));
        $this->username               = isset($data['username']) ? trim((string) $data['username']) : null;
        $this->password               = isset($data['password']) ? trim((string) $data['password']) : null;
        $this->tipo_user              = isset($data['tipo_user']) ? trim((string) $data['tipo_user']) : null;
        $this->tipo_persona           = (int) ($data['tipo_persona'] ?? 0);
        $this->institucion            = (int) ($data['institucion'] ?? 0);
        $this->grupo_sanguineo        = isset($data['grupo_sanguineo']) ? trim((string) $data['grupo_sanguineo']) : null;
        $this->alergias               = isset($data['alergias']) ? trim((string) $data['alergias']) : null;
        $this->enfermedades           = isset($data['enfermedades']) ? trim((string) $data['enfermedades']) : null;
        $this->medicamentos           = isset($data['medicamentos']) ? trim((string) $data['medicamentos']) : null;
        $this->contacto_emergencia_nombre   = isset($data['contacto_emergencia_nombre']) ? trim((string) $data['contacto_emergencia_nombre']) : null;
        $this->contacto_emergencia_telefono = isset($data['contacto_emergencia_telefono']) ? trim((string) $data['contacto_emergencia_telefono']) : null;

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->identificacion === '') throw new RuntimeException('La identificación es obligatoria.', 400);
        if ($this->primer_nombre === '') throw new RuntimeException('El primer nombre es obligatorio.', 400);
        if ($this->primer_apellido === '') throw new RuntimeException('El primer apellido es obligatorio.', 400);
        if ($this->fecha_nacimiento === '') throw new RuntimeException('La fecha de nacimiento es obligatoria.', 400);
        if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
        if ($this->tipo_persona <= 0) throw new RuntimeException('El tipo de persona es obligatorio.', 400);
    }

    public static function fromJson(): self
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body ?: '', true);
        if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
        return new self($data);
    }

    public function getIdentificacion(): string { return $this->identificacion; }
    public function getPrimerNombre(): string { return $this->primer_nombre; }
    public function getSegundoNombre(): ?string { return $this->segundo_nombre; }
    public function getPrimerApellido(): string { return $this->primer_apellido; }
    public function getSegundoApellido(): ?string { return $this->segundo_apellido; }
    public function getNacionalidad(): int { return $this->nacionalidad; }
    public function getGenero(): int { return $this->genero; }
    public function getEtnia(): int { return $this->etnia; }
    public function getFechaNacimiento(): string { return $this->fecha_nacimiento; }
    public function getCiudad(): int { return $this->ciudad; }
    public function getDireccion(): string { return $this->direccion; }
    public function getUsername(): ?string { return $this->username; }
    public function getPassword(): ?string { return $this->password; }
    public function getTipoUser(): ?string { return $this->tipo_user; }
    public function getTipoPersona(): int { return $this->tipo_persona; }
    public function getInstitucion(): int { return $this->institucion; }
    public function getGrupoSanguineo(): ?string { return $this->grupo_sanguineo; }
    public function getAlergias(): ?string { return $this->alergias; }
    public function getEnfermedades(): ?string { return $this->enfermedades; }
    public function getMedicamentos(): ?string { return $this->medicamentos; }
    public function getContactoEmergenciaNombre(): ?string { return $this->contacto_emergencia_nombre; }
    public function getContactoEmergenciaTelefono(): ?string { return $this->contacto_emergencia_telefono; }
}
