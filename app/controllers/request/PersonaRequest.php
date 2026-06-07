<?php

declare(strict_types=1);

namespace Request;

use RuntimeException;

class PersonaRequest
{
    // Datos Personales
    public string $identificacion;
    public string $primer_nombre;
    public ?string $segundo_nombre;
    public string $primer_apellido;
    public ?string $segundo_apellido;
    public ?string $fecha_nacimiento;
    public ?int $genero;
    public ?int $etnia;
    public ?int $nacionalidad;
    public ?int $ciudad;
    public ?string $direccion;

    // Datos Institucionales
    public int $institucion;
    public int $tipo_persona_id;
    public int $pi_estado;

    // Ficha Médica
    public ?string $grupo_sanguineo;
    public ?string $contacto_emergencia_nombre;
    public ?string $contacto_emergencia_telefono;
    public ?string $alergias;
    public ?string $enfermedades_preexistentes;
    public ?string $medicamentos_actuales;

    // Contacto
    public array $telefonos;
    public array $correos;

    // Acceso al sistema
    public ?string $username;
    public ?string $password;
    public ?string $tipo_user;

    public function __construct(array $data)
    {
        // Datos Personales
        $this->identificacion   = trim((string) ($data['identificacion'] ?? ''));
        $this->primer_nombre    = trim((string) ($data['primer_nombre'] ?? ''));
        $this->segundo_nombre   = isset($data['segundo_nombre']) && $data['segundo_nombre'] !== '' ? trim((string) $data['segundo_nombre']) : null;
        $this->primer_apellido  = trim((string) ($data['primer_apellido'] ?? ''));
        $this->segundo_apellido = isset($data['segundo_apellido']) && $data['segundo_apellido'] !== '' ? trim((string) $data['segundo_apellido']) : null;
        $this->fecha_nacimiento = isset($data['fecha_nacimiento']) && $data['fecha_nacimiento'] !== '' ? (string) $data['fecha_nacimiento'] : null;
        $this->genero           = isset($data['genero']) && $data['genero'] !== '' ? (int) $data['genero'] : null;
        $this->etnia            = isset($data['etnia']) && $data['etnia'] !== '' ? (int) $data['etnia'] : null;
        $this->nacionalidad     = isset($data['nacionalidad']) && $data['nacionalidad'] !== '' ? (int) $data['nacionalidad'] : null;
        $this->ciudad           = isset($data['ciudad']) && $data['ciudad'] !== '' ? (int) $data['ciudad'] : null;
        $this->direccion        = isset($data['direccion']) && $data['direccion'] !== '' ? trim((string) $data['direccion']) : null;

        // Datos Institucionales
        $this->institucion     = (int) ($data['institucion'] ?? 0);
        $this->tipo_persona_id = (int) ($data['tipo_persona_id'] ?? 0);
        $this->pi_estado       = (int) ($data['pi_estado'] ?? 1);

        // Ficha Médica
        $this->grupo_sanguineo              = isset($data['grupo_sanguineo']) && $data['grupo_sanguineo'] !== '' ? (string) $data['grupo_sanguineo'] : null;
        $this->contacto_emergencia_nombre   = isset($data['contacto_emergencia_nombre']) && $data['contacto_emergencia_nombre'] !== '' ? trim((string) $data['contacto_emergencia_nombre']) : null;
        $this->contacto_emergencia_telefono = isset($data['contacto_emergencia_telefono']) && $data['contacto_emergencia_telefono'] !== '' ? trim((string) $data['contacto_emergencia_telefono']) : null;
        $this->alergias                     = isset($data['alergias']) && $data['alergias'] !== '' ? trim((string) $data['alergias']) : null;
        $this->enfermedades_preexistentes   = isset($data['enfermedades_preexistentes']) && $data['enfermedades_preexistentes'] !== '' ? trim((string) $data['enfermedades_preexistentes']) : null;
        $this->medicamentos_actuales        = isset($data['medicamentos_actuales']) && $data['medicamentos_actuales'] !== '' ? trim((string) $data['medicamentos_actuales']) : null;

        // Contacto
        $this->telefonos = is_array($data['telefonos'] ?? null) ? $data['telefonos'] : [];
        $this->correos   = is_array($data['correos'] ?? null) ? $data['correos'] : [];

        // Acceso al sistema
        $this->username  = isset($data['username']) && $data['username'] !== '' ? trim((string) $data['username']) : null;
        $this->password  = isset($data['password']) && $data['password'] !== '' ? (string) $data['password'] : null;
        $this->tipo_user = isset($data['tipo_user']) && $data['tipo_user'] !== '' ? (string) $data['tipo_user'] : null;

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->identificacion === '') throw new RuntimeException('La identificación es obligatoria.', 400);
        if ($this->primer_nombre === '') throw new RuntimeException('El primer nombre es obligatorio.', 400);
        if ($this->primer_apellido === '') throw new RuntimeException('El primer apellido es obligatorio.', 400);
        if ($this->institucion <= 0) throw new RuntimeException('La institución es obligatoria.', 400);
        if ($this->tipo_persona_id <= 0) throw new RuntimeException('El tipo de persona es obligatorio.', 400);
    }

    public static function fromJson(): self
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body ?: '', true);
        if (!is_array($data)) throw new RuntimeException('El body JSON es inválido o está vacío.', 400);
        return new self($data);
    }
}
