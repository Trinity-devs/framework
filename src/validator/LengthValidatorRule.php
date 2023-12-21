<?php

namespace trinity\validator;

use trinity\contracts\ValidatorRuleInterface;

class LengthValidatorRule implements ValidatorRuleInterface
{
    public function validateRule(string $field, array $params, Validator $validator): void
    {
        $value = $validator->getDataValue($field);

        if (strlen($value) < $params[0] || strlen($value) > $params[1]) {
            $validator->addError($field, 'Число символов должно быть в диапазоне от ' . $params[0] . ' до ' . $params[1]);
        }
    }
}