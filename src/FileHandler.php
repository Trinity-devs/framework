<?php

namespace trinity;

use trinity\contracts\handlers\file\FileHandlerInterface;

class FileHandler implements FileHandlerInterface
{
    private array $aliases;

    public function __construct(array $config)
    {
        $this->aliases = $config;
    }

    public function getAlias(string $link): ?string
    {
        return $this->aliases[$link] ?? null;
    }

    public function aliasExists(string $link): bool
    {
        return array_key_exists($link, $this->aliases);
    }

    public function setAlias(string $link, string $path): void
    {
        $this->aliases[$link] = $path;
    }

    public function resolvePath(string $path): string
    {
        foreach ($this->aliases as $alias => $aliasPath) {
            if (strpos($path, $alias) === 0) {
                return str_replace($alias, $aliasPath, $path);
            }
        }

        return $path;
    }
}