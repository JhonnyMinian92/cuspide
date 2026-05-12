<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/CompanyServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\CompanyServiceInterface;
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

    return $this->getCompanyInfo($userId);
  }

  private function getCompanyInfo(int $userId): array
  {
    $persona = (new PersonaRepository())->findByUserId($userId);
    if ($persona === null) {
      throw new RuntimeException('No se encontró perfil para el usuario autenticado.', 404);
    }

    $personaInstitucion = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());
    if ($personaInstitucion === null) {
      throw new RuntimeException('El usuario no está asociado a ninguna institución.', 404);
    }

    $institucion = $personaInstitucion->getIdInstitucion();
    if ($institucion === null) {
      throw new RuntimeException('No se encontraron datos de la institución.', 404);
    }

    // Se asume que la entidad Institucion tiene estos métodos get
    return [
      'nombre' => $institucion->getNombre(),
      'ruc' => $institucion->getRuc(),
    ];
  }
}
