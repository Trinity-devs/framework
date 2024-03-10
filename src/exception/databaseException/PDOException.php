<?php

declare(strict_types=1);

namespace trinity\exception\databaseException;

use PDOException as BasePDOException;

final class PDOException extends BasePDOException
{
    public function __construct($message, $errorInfo = [], $code = 500, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorInfo = $errorInfo;
        $this->code = $code;
    }

    public function getName(): string
    {
        return 'Database Exception';
    }
}
