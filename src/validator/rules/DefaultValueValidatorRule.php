<?php

namespace trinity\validator\rules;

use trinity\contracts\validator\ValidatorRuleInterface;

class DefaultValueValidatorRule implements ValidatorRuleInterface
{
    public function validateRule(string $field, array $params, Validator $validator): void
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
