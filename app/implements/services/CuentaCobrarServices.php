<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../interfaces/CuentaCobrarServiceInterface.php';
require_once __DIR__ . '/../persistence/repository/PersonaRepository.php';
require_once __DIR__ . '/../persistence/repository/VentaRepository.php';
require_once __DIR__ . '/../persistence/repository/VentaDetalleRepository.php';
require_once __DIR__ . '/../utils/Token.php';

use Repositories\PersonaRepository;
use Repositories\VentaRepository;
use Repositories\VentaDetalleRepository;
use Utils\Token;
use Interfaces\CuentaCobrarServiceInterface;

class CuentaCobrarServices implements CuentaCobrarServiceInterface
{
    private PersonaRepository     $personaRepo;
    private VentaRepository       $ventaRepo;
    private VentaDetalleRepository $ventaDetalleRepo;
    private array $security;

    public function __construct()
    {
        $this->personaRepo      = new PersonaRepository();
        $this->ventaRepo        = new VentaRepository();
        $this->ventaDetalleRepo = new VentaDetalleRepository();
        $this->security         = require __DIR__ . '/../../properties/security.php';
    }

    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array
    {
        $payload = Token::validateBearerToken($authHeader, $this->security['jwt_secret']);
        if (!$payload) {
            throw new \RuntimeException('Token inválido o no autorizado.', 401);
        }

        $resource = $queryParams['api'] ?? '';
        if ($resource === '') {
            $parts    = array_values(array_filter(explode('/', parse_url($requestUri, PHP_URL_PATH))));
            $resource = strtolower(end($parts) ?: '');
        }

        switch (true) {
            case $method === 'GET' && $resource === 'list':
                return $this->fetchCuentasCobrar($queryParams);

            default:
                throw new \RuntimeException('Recurso no encontrado o método no soportado.', 404);
        }
    }

    // -------------------------------------------------------------------------
    // fetchCuentasCobrar
    // -------------------------------------------------------------------------
    private function fetchCuentasCobrar(array $queryParams): array
    {
        $institucion = isset($queryParams['institucion']) ? (int)$queryParams['institucion'] : 0;
        if ($institucion <= 0) {
            throw new \RuntimeException('El parámetro institucion es requerido.', 400);
        }

        $personaId      = isset($queryParams['persona_id']) ? (int)$queryParams['persona_id'] : 0;
        $identificacion = trim($queryParams['identificacion'] ?? '');

        // If identificacion filter provided, resolve to persona id
        if ($personaId <= 0 && $identificacion !== '') {
            $persona = $this->personaRepo->findByIdentificacion($identificacion);
            if (!$persona) {
                throw new \RuntimeException('Persona no encontrada con esa identificación.', 404);
            }
            $personaId = (int)$persona->getId();
        }

        $ventas = $this->ventaRepo->findCuentasCobrar($institucion, $personaId > 0 ? $personaId : null);

        $totalPorCobrar = 0.0;
        $cuentas        = [];

        foreach ($ventas as $venta) {
            $totalPorCobrar += (float)$venta['saldo'];

            // Fetch detail lines via repository
            $detalleEntities = $this->ventaDetalleRepo->findByVenta((int)$venta['id']);

            $detalleFormatted = [];
            foreach ($detalleEntities as $d) {
                $detalleFormatted[] = [
                    'descripcion'     => '',
                    'cantidad'        => (float)$d->getCantidad(),
                    'precio_unitario' => (float)$d->getPrecioUnitario(),
                    'subtotal'        => (float)$d->getTotal(),
                ];
            }

            $cuentas[] = [
                'id'             => (int)$venta['id'],
                'numero'         => $venta['numero'],
                'fecha'          => $venta['fecha'],
                'tipo'           => $venta['tipo'],
                'total'          => (float)$venta['total'],
                'saldo'          => (float)$venta['saldo'],
                'identificacion' => $venta['identificacion'],
                'nombre_cliente' => $venta['nombre_cliente'],
                'dias_vencido'   => (int)$venta['dias_vencido'],
                'detalle'        => $detalleFormatted,
            ];
        }

        return [
            'total_por_cobrar' => round($totalPorCobrar, 2),
            'cuentas'          => $cuentas,
        ];
    }
}
