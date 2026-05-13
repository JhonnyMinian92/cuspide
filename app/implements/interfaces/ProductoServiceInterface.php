<?php

declare(strict_types=1);

namespace Interfaces;

interface ProductoServiceInterface
{
    public function processRequest(string $method, string $requestUri, array $queryParams, string $authHeader): array;
}
