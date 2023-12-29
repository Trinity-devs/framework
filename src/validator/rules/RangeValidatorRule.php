<?php

namespace trinity\validator\rules;

use trinity\contracts\ValidatorRuleInterface;

class RangeValidatorRule implements ValidatorRuleInterface
{
    /**
     * @param mixed $value
     * @return void
     */
    public function validateRule(mixed $value): void
    {
        if ($value < $params[0] || $value > $params[1]) {
            $validator->addError($field, 'Значение должно быть в диапазоне от ' . $params[0] . ' до ' . $params[1]);
        }
    }
}