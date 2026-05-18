<?php

declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../persistence/repository/CategoriaDeportivaRepository.php';
require_once __DIR__ . '/../persistence/entitys/CategoriaDeportiva.php';
require_once __DIR__ . '/../../controllers/request/CategoriaDeportivaRequest.php';
require_once __DIR__ . '/../utils/Token.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/PersonaInstitucionRepository.php';

use Repositories\CategoriaDeportivaRepository;
use Repositories\PersonaRepository;
use Repositories\PersonaInstitucionRepository;
use Entitys\CategoriaDeportiva;
use Request\CategoriaDeportivaRequest;
use Utils\Token;
use RuntimeException;

class CategoriaDeportivaServices
{
  private CategoriaDeportivaRepository $repository;
  private string $jwtSecret;

  public function __construct()
  {
    $this->repository = new CategoriaDeportivaRepository();
    $config = require __DIR__ . '/../../properties/security.php';
    $this->jwtSecret = (string) ($config['jwt_secret'] ?? '');
    if ($this->jwtSecret === '') {
      throw new RuntimeException('No existe configuración de seguridad para JWT.', 500);
    }
  }

  public function processRequest(string $method, array $queryParams, string $authHeader): array
  {
    $payload = Token::validateBearerToken($authHeader, $this->jwtSecret);
    $userId = (int) ($payload['sub'] ?? 0);
    $institucionId = $this->getInstitucionByUserId($userId);

    $action = strtolower($queryParams['api'] ?? '');
    $id = isset($queryParams['id']) ? (int)$queryParams['id'] : null;

    return match (true) {
      $method === 'GET' && $action === 'list' => $this->list($institucionId),
      $method === 'POST' && $action === 'save' => $this->create($institucionId),
      $method === 'PUT' && $action === 'save' && $id !== null => $this->update($id, $institucionId),
      $method === 'DELETE' && $action === 'delete' && $id !== null => $this->delete($id, $institucionId),
      default => throw new RuntimeException('Acción no válida o no especificada.', 404),
    };
  }

  private function list(int $institucionId): array
  {
    return $this->repository->findAllByInstitucion($institucionId);
  }

  private function create(int $institucionId): array
  {
    $req = CategoriaDeportivaRequest::fromJson();
    if ($req->getInstitucion() !== $institucionId) {
      throw new RuntimeException('La institución de la solicitud no coincide con la del usuario.', 403);
    }

    $categoria = new CategoriaDeportiva(
      null,
      $req->getNombre(),
      $req->getNemonico(),
      $req->getDeporte(),
      $req->getEdadMinima(),
      $req->getEdadMaxima(),
      $req->getDescripcion(),
      $req->getInstitucion(),
      $req->getEstado()
    );

    $saved = $this->repository->save($categoria);
    return ['id' => $saved->getId(), 'message' => 'Categoría creada exitosamente.'];
  }

  private function update(int $id, int $institucionId): array
  {
    $req = CategoriaDeportivaRequest::fromJson();
    if ($req->getInstitucion() !== $institucionId) {
      throw new RuntimeException('La institución de la solicitud no coincide con la del usuario.', 403);
    }

    $existing = $this->repository->findById($id);
    if ($existing === null || $existing->getInstitucion()?->getId() !== $institucionId) {
      throw new RuntimeException('Categoría no encontrada o no pertenece a la institución.', 404);
    }

    $categoria = new CategoriaDeportiva(
      $id,
      $req->getNombre(),
      $req->getNemonico(),
      $req->getDeporte(),
      $req->getEdadMinima(),
      $req->getEdadMaxima(),
      $req->getDescripcion(),
      $req->getInstitucion(),
      $req->getEstado()
    );

    $this->repository->save($categoria);
    return ['id' => $id, 'message' => 'Categoría actualizada exitosamente.'];
  }

  private function delete(int $id, int $institucionId): array
  {
    $existing = $this->repository->findById($id);
    if ($existing === null || $existing->getInstitucion()?->getId() !== $institucionId) {
      throw new RuntimeException('Categoría no encontrada o no pertenece a la institución.', 404);
    }

    $this->repository->softDelete($id);
    return ['id' => $id, 'message' => 'Categoría eliminada (desactivada) exitosamente.'];
  }

  private function getInstitucionByUserId(int $userId): int
  {
    $personaRepo = new PersonaRepository();
    $piRepo      = new PersonaInstitucionRepository();

    $persona = $personaRepo->findByUserId($userId);
    if ($persona === null) {
      throw new RuntimeException('No se encontró persona asociada al usuario.', 404);
    }

    $pi = $piRepo->findByPersonaId((int) $persona->getId());
    if ($pi === null) {
      throw new RuntimeException('No se encontró institución asociada al usuario.', 404);
    }

    $institucion = $pi->getIdInstitucion();
    if ($institucion === null) {
      throw new RuntimeException('La persona no tiene una institución válida asociada.', 404);
    }

    return (int) $institucion->getId();
  }
}