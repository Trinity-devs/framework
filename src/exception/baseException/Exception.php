<?php

declare(strict_types=1);

namespace trinity\exception\baseException;

use Exception as BaseException;

class Exception extends BaseException
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Exception';
    }
}
