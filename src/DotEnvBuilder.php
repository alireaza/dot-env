<?php

namespace AliReaza\DotEnv;

use AliReaza\DotEnv\Resolver\Variables;
use AliReaza\Singleton\AbstractSingleton;
use AliReaza\Singleton\SingletonInterface;

class DotEnvBuilder extends AbstractSingleton implements SingletonInterface
{
    public static ?DotEnv $instance = null;

    public static function getInstance(): DotEnv
    {
        if (is_null(static::$instance)) {
            $env = new DotEnv();

            if (class_exists(Variables::class)) {
                $env->setResolvers([
                    new Variables($_SERVER + $_ENV),
                ]);
            }

            if (file_exists($file = '.env')) {
                $env->load($file);
            }

            static::$instance = $env;
        }

        return static::$instance;
    }
}
