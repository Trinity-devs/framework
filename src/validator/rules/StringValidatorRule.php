<?php

namespace trinity\validator\rules;

use trinity\contracts\validator\ValidatorRuleInterface;
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
