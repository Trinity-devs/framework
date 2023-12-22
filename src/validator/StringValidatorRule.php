<?php

namespace trinity\validator;

use trinity\contracts\ValidatorRuleInterface;
use trinity\exception\baseException\ValidationError;

class StringValidatorRule implements ValidatorRuleInterface
{
    /**
     * @param mixed $value
     * @return void
     * @throws ValidationError
     */
    public function validateRule(mixed $value): void
    {
        if (is_string($value) === false) {
            throw new ValidationError('Значение должно быть строкой');
        }
    }
}