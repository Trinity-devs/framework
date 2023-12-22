<?php

namespace trinity\validator;

use trinity\contracts\ValidatorRuleInterface;

class EmailValidatorRule implements ValidatorRuleInterface
{
    public function validateRule(string $field, array $params, Validator $validator): void
    {
        $value = $validator->getDataValue($field);

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $validator->addError($field, 'Некорректный email');
        }
    }
}