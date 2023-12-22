<?php

namespace trinity\validator;

use trinity\contracts\ValidatorRuleInterface;

class DateValidatorRule implements ValidatorRuleInterface
{

    public function validateRule(string $field, array $params, Validator $validator): void
    {
        $value = $validator->getDataValue($field);

        if (strtotime($value) === false) {
            $validator->addError($field, 'Значение должно быть датой');
        }
    }
}