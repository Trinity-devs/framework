<?php

namespace trinity\contracts;

interface FileHandlerInterface
{
    public function getAlias(string $link): ?string;
    public function aliasExists(string $link): bool;
    public function setAlias(string $link, string $path): void;
}