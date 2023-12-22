<?php

namespace trinity\validator;

use trinity\contracts\ValidatorRuleInterface;

class UrlValidatorRule implements ValidatorRuleInterface
{

    public function validateRule(string $field, array $params, Validator $validator): void
    {
        $value = $validator->getDataValue($field);

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            $validator->addError($field, 'Значение должно быть URL');
        }
    }
}