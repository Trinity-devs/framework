<?php

declare(strict_types=1);

namespace trinity\exception\baseException;

final class InvalidCallException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Invalid Call';
    }
}
