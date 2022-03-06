<?php

namespace AliReaza\DotEnv;

use Exception;
use LogicException;

class DotEnv
{
    protected array $env = [];
    protected ?array $resolvers = null;

    public function __construct(string $file = null, array $resolvers = null)
    {
        $this->setResolvers($resolvers);

        if (!is_null($file)) {
            $this->load($file);
        }
    }

    public function load(string $file): void
    {
        if (!is_readable($file) || is_dir($file)) {
            throw new Exception(sprintf('Unable to read the "%s" environment file.', $file));
        }

        $data = file_get_contents($file);

        $this->parse($data);
    }

    public function parse(string $data): void
    {
        $data = $this->normalizeLineEndings($data);
        $lines = $this->linesToArray($data);

        foreach ($lines as $line) {
            if ($this->lineSkip($line)) {
                continue;
            }

            $this->parseLineToKeyValue($line, $key, $value);

            $key = $this->lexKey($key);
            $this->env[$key] = $this->lexValue($value);
        }
    }

    public function normalizeLineEndings(string $data): string
    {
        return str_replace(["\r\n", "\r"], PHP_EOL, $data);
    }

    public function linesToArray(string $data): array
    {
        return explode(PHP_EOL, $data);
    }

    public function lineSkip(string $data): bool
    {
        $data = trim($data);

        return $data === '' || str_starts_with($data, '#');
    }

    public function parseLineToKeyValue(string $data, &$key, &$value): void
    {
        $array = explode('=', $data, 2);

        if (count($array) !== 2) {
            throw $this->createLogicException('Missing = in the environment variable declaration: ' . $data);
        }

        [$key, $value] = $array;
    }

    public function lexKey(string $data): string
    {
        if ($data !== rtrim($data)) {
            throw $this->createLogicException('Whitespace characters are not supported after the variable name: ' . $data);
        }

        $match = preg_match('/(export[ \t]++)?((?i:[A-Z][A-Z0-9_]*+))/A', $data, $matches);

        if (!$match || ($matches[0] === 'export' && $matches[1] === '') || $matches[0] !== $data) {
            throw $this->createLogicException('Invalid character in variable name: ' . $data);
        }

        return $matches[2];
    }

    public function lexValue(string $data): string
    {
        if ($data !== ltrim($data)) {
            throw $this->createLogicException('Whitespace characters are not supported before the value: ' . $data);
        }

        $data = $this->trimQuotes($data);

        $resolvers = $this->getResolvers();
        if (is_array($resolvers) && !empty($resolvers)) {
            foreach ($resolvers as $resolver) {
                $data = $resolver($data, $this->env);
            }
        }

        return $data;
    }

    public function trimQuotes(string $data): string
    {
        if (preg_match('/\A([\'"])(.*)\1\z/', $data, $matches)) {
            $data = $matches[2];
        }

        return $data;
    }

    public function setResolvers(?array $resolvers = null): void
    {
        $this->resolvers = $resolvers;
    }

    public function getResolvers(): ?array
    {
        return $this->resolvers;
    }

    public function createLogicException(string $message): LogicException
    {
        return new LogicException($message);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->env);
    }

    public function get(string $key, string $default = ''): string
    {
        return $this->env[$key] ?? $default;
    }

    public function toArray(): array
    {
        return $this->env;
    }
}
