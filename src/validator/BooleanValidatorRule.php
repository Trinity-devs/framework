<?php

namespace trinity\validator;

use trinity\contracts\ValidatorRuleInterface;
use trinity\exception\baseException\ValidationError;

class BooleanValidatorRule implements ValidatorRuleInterface
{
    /**
     * @param mixed $value
     * @return void
     * @throws ValidationError
     */
    public function validateRule(mixed $value): void
    {
        if (is_bool($value) === false) {
            throw new ValidationError('Значение должно быть булевым');
        }
    }
}