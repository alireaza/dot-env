<?php

use AliReaza\DotEnv\DotEnvBuilder;

function env(?string $key = null, string|int|null|bool $default = ''): string|int|null|bool|array
{
    $env = DotEnvBuilder::getInstance();

    if (is_null($key)) {
        return $env->toArray();
    }

    if ($env->has($key)) {
        $value = $env->get($key);

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }

    return $default;
}
