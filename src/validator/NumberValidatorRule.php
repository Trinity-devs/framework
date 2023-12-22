<?php

namespace trinity\validator;

use trinity\contracts\ValidatorRuleInterface;
use trinity\exception\baseException\ValidationError;

class NumberValidatorRule implements ValidatorRuleInterface
{
    private $integerPattern = '/^[0-9]+$/';

    /**
     * @param mixed $value
     * @return void
     * @throws ValidationError
     */
    public function validateRule(mixed $value): void
    {
        if (preg_match($this->integerPattern, $value) === 0) {
            throw new ValidationError('Значение должно быть целым числом');
        }
    }
}