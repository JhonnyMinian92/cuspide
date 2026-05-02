<?php

declare(strict_types=1);

namespace Interfaces;

interface AuthServiceInterface
{
    public function login(string $username, string $encryptedPassword): array;
}
