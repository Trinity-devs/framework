<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class RangeValidator implements ValidatorInterface
{

    public function validate(string $field, array $params, Validator $validator): void
    {
        $value = $validator->getDataValue($field);

        if ($value < $params[0] || $value > $params[1]) {
            $validator->addError($field, 'Значение должно быть в диапазоне от ' . $params[0] . ' до ' . $params[1]);
        }
    }
}