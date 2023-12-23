<?php

namespace trinity\validator;

use trinity\contracts\ValidatorRuleInterface;
use trinity\exception\baseException\ValidationError;

class EmailValidatorRule implements ValidatorRuleInterface
{
    /**
     * @param mixed $value
     * @return void
     * @throws ValidationError
     */
    public function validateRule(mixed $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationError('Некорректный email');
        }
    }
}