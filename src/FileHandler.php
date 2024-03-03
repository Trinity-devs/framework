<?php

namespace trinity;

use trinity\contracts\handlers\file\FileHandlerInterface;
use trinity\exception\baseException\InvalidArgumentException;
use trinity\helpers\ArrayHelper;

class FileHandler implements FileHandlerInterface
{
    private array $aliases;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->aliases = $config;
    }

    /**
     * @param string $link
     * @return string|null
     */
    public function getAlias(string $link): ?string
    {
        return $this->aliases[$link] ?? null;
    }

    /**
     * @param string $link
     * @return bool
     * @throws InvalidArgumentException
     */
    public function aliasExists(string $link): bool
    {
        return ArrayHelper::keyExists($link, $this->aliases);
    }

    /**
     * @param string $link
     * @param string $path
     * @return void
     */
    public function setAlias(string $link, string $path): void
    {
        $this->aliases[$link] = $path;
    }

    /**
     * @param string $path
     * @return string
     */
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