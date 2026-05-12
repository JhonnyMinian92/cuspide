<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/ButtomServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/BotonRepository.php';
require_once __DIR__ . '/../persistence/repository/BotonUserRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/UserRepository.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\ButtomServiceInterface;
use Repositories\BotonUserRepository;
use Repositories\PersonaInstitucionRepository;
use Repositories\PersonaRepository;
use Repositories\UserRepository;
use RuntimeException;
use Utils\Token;

final class ButtomServices implements ButtomServiceInterface
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
      'menu' => $this->fetchMenu($userId),
      default => throw new RuntimeException('Ruta de API no soportada: ' . (string) $resource, 404),
    };
  }

  private function fetchMenu(int $userId): array
  {
    $user = (new UserRepository())->findById($userId);
    if ($user === null) {
      throw new RuntimeException('No se encontró usuario autenticado.', 404);
    }

    $botonUserRepository = new BotonUserRepository();
    $persona = (new PersonaRepository())->findByUserId($userId);
    $personaInstitucion = $persona === null ? null : (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());
    $institucionId = $personaInstitucion?->getIdInstitucion()?->getId();

    $botonesUser = $institucionId === null
      ? $botonUserRepository->findByTypeUser((string) $user->getType()->getNemonico())
      : $botonUserRepository->findByTypeUserAndInstitucion((string) $user->getType()->getNemonico(), (int) $institucionId);
    $menu = [];
    foreach ($botonesUser as $botonUser) {
      $boton = $botonUser->getBoton();
      $menu[] = [
        'orden' => (int) $boton->getOrden(),
        'nombre' => (string) $boton->getNombre(),
        'icon' => (string) $boton->getIcono(),
        'tipo' => (string) $boton->getTipo(),
      ];
    }
    usort($menu, fn(array $a, array $b): int => $a['orden'] <=> $b['orden']);

    return $menu;
  }
}
