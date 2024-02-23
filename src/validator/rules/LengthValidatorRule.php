<?php

namespace trinity\validator\rules;

use trinity\contracts\validator\ValidatorRuleInterface;
use trinity\exception\baseException\ValidationError;

class LengthValidatorRule implements ValidatorRuleInterface
{
    /**
     * @param array $settings
     */
    public function __construct(private array $settings = [])
    {
    }

    /**
     * @param mixed $value
     * @return void
     * @throws ValidationError
     */
    public function validateRule(mixed $value): void
    {
        if (iconv_strlen($value) < $this->settings['min'] || iconv_strlen($value) > $this->settings['max']) {
            throw new ValidationError('Значение должно быть в диапазоне от ' . $this->settings['min'] . ' до ' . $this->settings['max'] . ' символов');
        }
    }
}
