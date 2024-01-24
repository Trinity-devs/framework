<?php

namespace trinity\validator;

use trinity\contracts\validator\ValidatorInterface;
use trinity\exception\baseException\ValidationError;

class Validator implements ValidatorInterface
{
    private array $validateData = [];

    /**
     * @param AbstractFormRequest $form
     * @return void
     */
    public function validate(AbstractFormRequest $form): void
    {
        foreach ($form->rules() as $ruleItem) {

            $this->prepareValidatableRules($ruleItem);

            foreach ($this->validateData['field'] as $field) {

                try {

                    if (is_callable($this->validateData['rule']) === true) {
                        $this->validateData['rule']();

                        continue;
                    }

                    $value = $form->getAttribute($field);
                    $this->validateField(
                        $this->validateData['rule'],
                        $value,
                        $this->validateData['settings']
                    );

                } catch (ValidationError $e) {

                    $form->addError($field, $e->getMessage());
                }
            }
        }
    }

    /**
     * @param array $ruleItem
     * @return void
     */
    private function prepareValidatableRules(array $ruleItem): void
    {
        $this->validateData['field'] = is_array($ruleItem[0]) ? $ruleItem[0] : [$ruleItem[0]];
        $this->validateData['rule'] = $ruleItem[1];
        $this->validateData['settings'] = isset($ruleItem[2]) ? array_slice($ruleItem, 2)[0] : [];
    }

    /**
     * @param string $rule
     * @param mixed $value
     * @param array $settings
     * @return void
     */
    private function validateField(string $rule, mixed $value, array $settings = []): void
    {
        $customClassRule = __NAMESPACE__ . '\\rules\\' . ucfirst($rule) . 'ValidatorRule';

        if (class_exists($customClassRule) === false) {
            throw new ('Класс ' . $customClassRule . ' не найден');
        }

        $customRule = new $customClassRule($settings);

        $customRule->validateRule($value);
    }
}
