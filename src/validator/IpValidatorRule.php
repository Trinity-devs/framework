<?php

namespace trinity\validator;

use trinity\contracts\ValidatorRuleInterface;

class IpValidatorRule implements ValidatorRuleInterface
{

    public function validateRule(string $field, array $params, Validator $validator): void
    {
        $value = $validator->getDataValue($field);

        if (filter_var($value, FILTER_VALIDATE_IP) === false) {
            $validator->addError($field, 'Значение должно быть IP адресом');
        }
    }
}