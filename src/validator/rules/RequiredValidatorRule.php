<?php

namespace trinity\validator\rules;

use trinity\contracts\ValidatorRuleInterface;
use trinity\exception\baseException\ValidationError;

class RequiredValidatorRule implements ValidatorRuleInterface
{
    /**
     * @param mixed $value
     * @return void
     * @throws ValidationError
     */
    public function validateRule(mixed $value): void
    {
        if ($value === null || $value === '') {
            throw new ValidationError('Поле обязательно для заполнения');
        }
    }
}