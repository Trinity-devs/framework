<?php

declare(strict_types=1);

namespace trinity\contracts\exception;

use Throwable;

interface ExceptionInterface extends Throwable
{
    public function getName(): string;
    public function getStatusCode(): int;
}
