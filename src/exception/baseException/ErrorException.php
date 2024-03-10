<?php

declare(strict_types=1);

namespace trinity\exception\baseException;

final class ErrorException extends Exception
{
    public const E_HHVM_FATAL_ERROR = 16777217;

    /**
     * @return string
     */
    public function getName(): string
    {
        static $names = [
            E_COMPILE_ERROR => 'PHP Compile Error',
            E_COMPILE_WARNING => 'PHP Compile Warning',
            E_CORE_ERROR => 'PHP Core Error',
            E_CORE_WARNING => 'PHP Core Warning',
            E_DEPRECATED => 'PHP Deprecated Warning',
            E_ERROR => 'PHP Fatal Error',
            E_NOTICE => 'PHP Notice',
            E_PARSE => 'PHP Parse Error',
            E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
            E_STRICT => 'PHP Strict Warning',
            E_USER_DEPRECATED => 'PHP User Deprecated Warning',
            E_USER_ERROR => 'PHP User Error',
            E_USER_NOTICE => 'PHP User Notice',
            E_USER_WARNING => 'PHP User Warning',
            E_WARNING => 'PHP Warning',
            self::E_HHVM_FATAL_ERROR => 'HHVM Fatal Error',
        ];

        return $names[$this->getCode()] ?? 'Error';
    }

    /**
     * @param array $error
     * @return bool
     */
    public static function isFatalError(array $error): bool
    {
        return isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, self::E_HHVM_FATAL_ERROR]);
    }
}
