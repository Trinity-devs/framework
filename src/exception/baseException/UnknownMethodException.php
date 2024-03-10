<?php

declare(strict_types=1);

namespace trinity\exception\baseException;

final class UnknownMethodException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Unknown method';
    }
}
