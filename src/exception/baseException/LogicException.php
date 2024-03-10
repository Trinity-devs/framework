<?php

declare(strict_types=1);

namespace trinity\exception\baseException;

final class LogicException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Logic exception';
    }
}
