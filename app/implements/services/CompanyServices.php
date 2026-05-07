<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/CompanyServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/InstitucionRepository.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\CompanyServiceInterface;
use Repositories\InstitucionRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use RuntimeException;
use Utils\Token;

final class CompanyServices implements CompanyServiceInterface
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
      'find' => $this->fetchInstitucion($userId),
      default => throw new RuntimeException('Ruta de API no soportada: ' . (string) $resource, 404),
    };
  }

  private function fetchInstitucion(int $userId): array
  {
    $persona = (new PersonaRepository())->findByUserId($userId);
    if ($persona === null) {
      throw new RuntimeException('No se encontró persona del usuario autenticado.', 404);
    }

    $personaInstitucion = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());
    if ($personaInstitucion === null) {
      throw new RuntimeException('No se encontró institución asociada.', 404);
    }

    $idInstitucion = $personaInstitucion->getIdInstitucion();
    if (is_object($idInstitucion)) {
      $institucionId = (int) $idInstitucion->getId();
    } else {
      $institucionId = (int) $idInstitucion;
    }

    $institucion = (new InstitucionRepository())->findById($institucionId);
    if ($institucion === null) {
      throw new RuntimeException('No se encontró institución.', 404);
    }

    return [
      'ruc' => (string) $institucion->getRuc(),
      'nombre' => (string) $institucion->getNombre(),
      'logo' => (string) $institucion->getLogotipo(),
    ];
  }
}