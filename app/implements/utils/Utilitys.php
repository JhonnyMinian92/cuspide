<?php

declare(strict_types=1);

namespace Utils;

final class Utilitys
{
    public static function toArray(object $entity): array
    {
        $data = [];
        foreach (get_class_methods($entity) as $method) {
            if (!str_starts_with($method, 'get')) {
                continue;
            }

            $key = lcfirst(substr($method, 3));
            $value = $entity->{$method}();
            $data[$key] = is_object($value) ? self::toArray($value) : $value;
        }

        return $data;
    }
}
