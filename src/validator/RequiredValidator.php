<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class RequiredValidator implements ValidatorInterface
{
    public function validate(string $field, array $params, Validator $validator): bool
    {
        $value = $validator->getDataValue($field);

        if (is_array($value) === true) {
            if (count($value) === 0) {
                $validator->addError($field, 'Поле обязательно для заполнения');

                return false;
            }
        }

        if ($value === null || $value === '') {
            $validator->addError($field, 'Поле обязательно для заполнения');

            return false;
        }

        return true;
    }
}