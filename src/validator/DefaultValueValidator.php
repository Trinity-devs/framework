<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class DefaultValueValidator implements ValidatorInterface
{

    public function validate(string $field, array $params, Validator $validator): void
    {
        $value = $validator->getDataValue($field) ?? $params[0];

        if (is_callable($value) === true) {
            $validator->setDataValue($field, $value());
            
            return;
        }

        if (is_array($value) === true) {
            $value = array_map(function ($item) {
                if (is_callable($item) === true) {
                    return call_user_func($item);
                }

                return $item;
            }, $value);

            $validator->setDataValue($field, $value);
        }
    }
}