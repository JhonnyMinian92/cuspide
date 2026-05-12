<?php

declare(strict_types=1);

$password = 'Zeus2@26';

if ($password === '') {
  echo "Error: Debes proporcionar una contraseña.\n";
  exit(1);
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

echo $hashedPassword . PHP_EOL;