<?php

namespace trinity\validator\rules;

use trinity\contracts\validator\ValidatorRuleInterface;

/**
 * @deprecated
 */
class TrimValidatorRule implements ValidatorRuleInterface
{
    public function validateRule(string $field, array $params, Validator $validator): void
    {
        $value = trim($validator->getDataValue($field));

        $validator->setDataValue($field, $value);

        if (is_string($value) === false) {
            $validator->addError($field, 'Значение должно быть строкой');
        }
    }
}
