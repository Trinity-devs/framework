<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class IpValidator implements ValidatorInterface
{

    public function validate(string $field, array $params, Validator $validator): bool
    {
        $value = $validator->getDataValue($field);

        if (filter_var($value, FILTER_VALIDATE_IP) === false) {
            $validator->addError($field, 'Значение должно быть IP адресом');

            return false;
        }

        return true;
    }
}