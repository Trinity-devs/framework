<?php

declare(strict_types=1);

namespace trinity\exception\baseException;

final class InvalidArgumentException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return ' Invalid argument';
    }
}
