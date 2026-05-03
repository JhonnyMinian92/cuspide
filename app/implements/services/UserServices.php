<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/UserServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/InstitucionRepository.php';
require_once __DIR__ . '/../persistence/repository/BotonRepository.php';
require_once __DIR__ . '/../persistence/repository/BotonUserRepository.php';
require_once __DIR__ . '/../persistence/repository/UserRepository.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\UserServiceInterface;
use Repositories\BotonUserRepository;
use Repositories\InstitucionRepository;
use Repositories\PersonaInstitucionRepository;
use Repositories\PersonaRepository;
use Repositories\UserRepository;
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
        $resource = (($segments[0] ?? null) === 'api' && ($segments[1] ?? null) === 'users') ? ($segments[2] ?? null) : ($segments[0] ?? null);

        return match ($resource) {
            'profile' => $this->fetchProfile($userId),
            'institucion' => $this->fetchInstitucion($userId),
            'menu' => $this->fetchMenu($userId),
            'estadistica' => [],
            default => throw new RuntimeException('Ruta de API no soportada: ' . (string) $resource, 404),
        };
    }

    private function fetchProfile(int $userId): array
    {
        $persona = (new PersonaRepository())->findByUserId($userId);
        if ($persona === null) {
            throw new RuntimeException('No se encontró perfil para el usuario autenticado.', 404);
        }

        $personaInstitucion = (new PersonaInstitucionRepository())->findByPersonaId((int) $persona->getId());

        return [
            'identificacion' => (string) $persona->getIdentificacion(),
            'primer_nombre' => (string) $persona->getPrimerNombre(),
            'primer_apellido' => (string) $persona->getPrimerApellido(),
            'tipo' => $personaInstitucion ? (string) $personaInstitucion->getTipo()->getNombre() : '',
        ];
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

        $institucion = (new InstitucionRepository())->findById((int) $personaInstitucion->getIdInstitucion()->getId());
        if ($institucion === null) {
            throw new RuntimeException('No se encontró institución.', 404);
        }

        return [
            'nombre' => (string) $institucion->getNombre(),
            'logo' => (string) $institucion->getLogotipo(),
        ];
    }

    private function fetchMenu(int $userId): array
    {
        $user = (new UserRepository())->findById($userId);
        if ($user === null) {
            throw new RuntimeException('No se encontró usuario autenticado.', 404);
        }

        $botonesUser = (new BotonUserRepository())->findByTypeUser((string) $user->getType()->getNemonico());
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
