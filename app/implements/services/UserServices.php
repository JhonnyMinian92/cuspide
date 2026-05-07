<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/UserServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/TelefonoRepository.php';
require_once __DIR__ . '/../persistence/repository/CorreoRepository.php';
require_once __DIR__ . '/../utils/Token.php';

use Entitys\Correo;
use Interfaces\UserServiceInterface;
use Repositories\PersonaInstitucionRepository;
use Repositories\PersonaRepository;
use Repositories\TelefonoRepository;
use Repositories\CorreoRepository;
use RuntimeException;
use Utils\Token;

final class UserServices implements UserServiceInterface
{
    private string $jwtSecret;

    public function __construct()
    {
        $config = require __DIR__ . '/../../properties/security.php';
        $this->jwtSecret = (string) ($config['jwt_secret'] ?? '');
        if ($this->jwtSecret === '') {
            throw new RuntimeException('No existe configuración de seguridad para JWT.', 500);
        }
    }

    public function processRequest(string $requestUri, array $queryParams, string $authorizationHeader): array
    {
        $payload = Token::validateBearerToken($authorizationHeader, $this->jwtSecret);
        $userId = (int) ($payload['sub'] ?? 0);

        $path = parse_url($requestUri, PHP_URL_PATH) ?? '';
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        $resource = null;

        $tipo = strtolower(trim((string) ($queryParams['api'] ?? '')));
        if ($tipo !== '') {
            $resource = $tipo;
        }

        $usersIndex = array_search('users', $segments, true);
        if ($resource === null && $usersIndex !== false) {
            $candidate = $segments[$usersIndex + 1] ?? null;
            if ($candidate !== null && strtolower($candidate) !== 'index.php') {
                $resource = strtolower($candidate);
            }
        }

        if ($resource === null && $segments !== []) {
            $candidate = strtolower((string) $segments[array_key_last($segments)]);
            if ($candidate !== 'index.php') {
                $resource = $candidate;
            }
        }

        return match ($resource) {
            'find' => $this->fetchFindPerson($userId),
            'profile' => $this->fetchInfoPerson($userId),
            default => throw new RuntimeException('Ruta de API no soportada: ' . (string) $resource, 404),
        };
    }

    private function fetchFindPerson(int $userId): array
    {
        $persona = (new PersonaRepository())->findByUserId($userId);
        if ($persona === null) {
            throw new RuntimeException('No se encontró perfil para el usuario autenticado.', 404);
        }

        $personaInstitucion = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());

        $tipo = '';
        if ($personaInstitucion !== null) {
            $tipoPersona = $personaInstitucion->getTipo();
            $tipo = is_object($tipoPersona) && method_exists($tipoPersona, 'getNombre')
                ? (string) $tipoPersona->getNombre()
                : (string) $tipoPersona;
        }

        return [
            'identificacion' => (string) $persona->getIdentificacion(),
            'primer_nombre' => (string) $persona->getPrimerNombre(),
            'primer_apellido' => (string) $persona->getPrimerApellido(),
            'tipo' => $tipo,
        ];
    }

    private function fetchInfoPerson(int $userId): array
    {
        $persona = (new PersonaRepository())->findByUserId($userId);
        if ($persona === null) {
            throw new RuntimeException('No se encontró perfil para el usuario autenticado.', 404);
        }

        $personaInstitucion = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());

        $nemonicoTipo = '';
        $ruc = '';
        if ($personaInstitucion !== null) {
            $tipoPersona = $personaInstitucion->getTipo();
            $nemonicoTipo = is_object($tipoPersona) && method_exists($tipoPersona, 'getNemonico')
                ? (string) $tipoPersona->getNemonico()
                : (string) $tipoPersona;

            $institucion = $personaInstitucion->getIdInstitucion();
            $ruc = is_object($institucion) && method_exists($institucion, 'getRuc')
                ? (string) $institucion->getRuc()
                : '';
        }

        $nacionalidad = $persona->getNacionalidad();
        $nemonicoNacionalidad = is_object($nacionalidad) && method_exists($nacionalidad, 'getNemonico') ?
            (string) $nacionalidad->getNemonico() : (string) $nacionalidad;

        $genero = $persona->getGenero();
        $nemonicoGenero = is_object($genero) && method_exists($genero, 'getNemonico') ?
            (string) $genero->getNemonico() : (string) $genero;

        $etnia = $persona->getEtnia();
        $nemonicoEtnia = is_object($etnia) && method_exists($etnia, 'getNemonico') ?
            (string) $etnia->getNemonico() : (string) $etnia;

        $ciudad = $persona->getCiudad();
        $nemonicoCiudad = is_object($ciudad) && method_exists($ciudad, 'getNemonico') ?
            (string) $ciudad->getNemonico() : (string) $ciudad;

        // OBTENER TELEFONOS
        $telefonos = (new TelefonoRepository())->findByPersonaId((int) $persona->getId());
        $telefonosData = [];
        foreach ($telefonos as $telefono) {
            $telefonosData[] = [
                'numero' => (string) $telefono->getNumero(),
                'operadora' => is_object($telefono->getOperadora()) && method_exists($telefono->getOperadora(), 'getNemonico')
                    ? (string) $telefono->getOperadora()->getNemonico()
                    : (string) $telefono->getOperadora(),
                'tipo' => is_object($telefono->getTipo()) && method_exists($telefono->getTipo(), 'getNemonico')
                    ? (string) $telefono->getTipo()->getNemonico()
                    : (string) $telefono->getTipo()
            ];
        }

        // OBTENER CORREOS
        $correos = (new CorreoRepository())->findByPersonaId((int) $persona->getId());
        $correosData = [];
        foreach ($correos as $correo) {
            $correosData[] = [
                'email' => (string) $correo->getEmail(),
                'tipo' => is_object($telefono->getTipo()) && method_exists($telefono->getTipo(), 'getNemonico')
                    ? (string) $telefono->getTipo()->getNemonico()
                    : (string) $telefono->getTipo()
            ];
        }

        return [
            'identificacion' => (string) $persona->getIdentificacion(),
            'primer_nombre' => (string) $persona->getPrimerNombre(),
            'segundo_nombre' => (string) $persona->getSegundoNombre(),
            'primer_apellido' => (string) $persona->getPrimerApellido(),
            'segundo_apellido' => (string) $persona->getSegundoApellido(),
            'tipo' => $nemonicoTipo,
            'empresa' => $ruc,
            'nacionalidad' => $nemonicoNacionalidad,
            'genero' => $nemonicoGenero,
            'etnia' => $nemonicoEtnia,
            'fecha_nacimiento' => (string) $persona->getFechaNacimiento(),
            'ciudad' => $nemonicoCiudad,
            'direccion' => (string) $persona->getDireccion(),
            'contactos' => [
                'telefonos' => $telefonosData,
                'correos' => $correosData
            ],
            //estructura para registros medicos, alergas, entre otros
            //tallas (S,M,L) (Calzado) (Altura, Peso, etc ....)
            'alergias' => []
        ];
    }
}