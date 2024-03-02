<?php

namespace trinity\exception\databaseException;

class PDOException extends \PDOException
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