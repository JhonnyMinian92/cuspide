<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/MembresiaServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/MembresiaRepository.php';
require_once __DIR__ . '/../persistence/entitys/Membresia.php';
require_once __DIR__ . '/../utils/Token.php';

use Interfaces\MembresiaServiceInterface;
use Repositories\MembresiaRepository;
use Entitys\Membresia;
use RuntimeException;
use Utils\Token;

final class MembresiaServices implements MembresiaServiceInterface
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

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->jwtSecret);

        $resource = $this->resolveResource($requestUri, $queryParams, 'membresias');

        return match (true) {
            $method === 'GET'                          && $resource === 'list' => $this->fetchMembresias(),
            in_array($method, ['POST', 'PUT'], true)  && $resource === 'save' => $this->saveMembresia(isset($queryParams['id']) ? (int) $queryParams['id'] : null),
            default => throw new RuntimeException('Ruta de API no soportada: ' . $method . ' ' . $resource, 404),
        };
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resolveResource(string $requestUri, array $queryParams, string $controllerSegment): string
    {
        $tipo = strtolower(trim((string) ($queryParams['api'] ?? '')));
        if ($tipo !== '') {
            return $tipo;
        }

        $path     = parse_url($requestUri, PHP_URL_PATH) ?? '';
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        $idx = array_search($controllerSegment, $segments, true);
        if ($idx !== false) {
            $candidate = $segments[$idx + 1] ?? null;
            if ($candidate !== null && strtolower($candidate) !== 'index.php') {
                return strtolower($candidate);
            }
        }

        if ($segments !== []) {
            $last = strtolower((string) $segments[array_key_last($segments)]);
            if ($last !== 'index.php') {
                return $last;
            }
        }

        return '';
    }

    // -------------------------------------------------------------------------
    // fetchMembresias
    // -------------------------------------------------------------------------

    private function fetchMembresias(): array
    {
        $repo      = new MembresiaRepository();
        $entities  = $repo->findAll();

        $result = [];
        foreach ($entities as $m) {
            $result[] = [
                'id'          => $m->getId(),
                'titulo'      => $m->getTitulo(),
                'precio'      => $m->getPrecio(),
                'porcentaje'  => $m->getPorcentaje(),
                'descripcion' => $m->getDescripcion(),
                'url_boton'   => $m->getUrlBoton(),
                'fecha_inicio'=> $m->getFechaInicio(),
                'fecha_fin'   => $m->getFechaFin(),
                'estado'      => $m->getEstado() !== null ? $m->getEstado()->getId() : null,
            ];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // saveMembresia
    // -------------------------------------------------------------------------

    private function saveMembresia(?int $id): array
    {
        $body = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($body)) {
            throw new RuntimeException('Cuerpo de la solicitud inválido o vacío.', 400);
        }

        $titulo      = (string) ($body['titulo']       ?? '');
        $precio      = (float)  ($body['precio']       ?? 0);
        $porcentaje  = (float)  ($body['porcentaje']   ?? 0);
        $descripcion = (string) ($body['descripcion']  ?? '');
        $urlBoton    = (string) ($body['url_boton']    ?? '');
        $fechaInicio = (string) ($body['fecha_inicio'] ?? '');
        $fechaFin    = (string) ($body['fecha_fin']    ?? '');
        $estado      = (int)    ($body['estado']       ?? 1);

        if ($titulo === '' || $fechaInicio === '' || $fechaFin === '') {
            throw new RuntimeException('Faltan campos obligatorios: titulo, fecha_inicio, fecha_fin.', 400);
        }

        $repo = new MembresiaRepository();

        if ($id !== null && $id > 0) {
            // Update: load existing and replace scalar fields via a fresh entity with the same id
            $entity = new Membresia($id, $titulo, $precio, $porcentaje, $descripcion, $urlBoton, $fechaInicio, $fechaFin, $estado);
        } else {
            // Insert
            $entity = new Membresia(null, $titulo, $precio, $porcentaje, $descripcion, $urlBoton, $fechaInicio, $fechaFin, $estado);
        }

        $saved = $repo->save($entity);

        return [
            'id'          => $saved->getId(),
            'titulo'      => $saved->getTitulo(),
            'precio'      => $saved->getPrecio(),
            'porcentaje'  => $saved->getPorcentaje(),
            'descripcion' => $saved->getDescripcion(),
            'url_boton'   => $saved->getUrlBoton(),
            'fecha_inicio'=> $saved->getFechaInicio(),
            'fecha_fin'   => $saved->getFechaFin(),
            'estado'      => $saved->getEstado() !== null ? $saved->getEstado()->getId() : $estado,
        ];
    }
}
