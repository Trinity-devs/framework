<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;
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

            foreach ($this->validateData['fields'] as $field) {

                try {

                    if (is_callable($this->validateData['rule']) === true) {
                        $this->validateData['rule']();

                        continue;
                    }

                    $value = $form->getAttribute($this->validateData['fields']);

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
     * @param array $rules
     * @return void
     */
    private function prepareValidatableRules(array $rules): void
    {
        $this->validateData['fields'] = is_array($rules[0]) ? $rules[0] : [$rules[0]];
        $this->validateData['rule'] = $rules[1];
        $this->validateData['settings'] = isset($rules[2]) ? array_slice($rules, 2) : [];
    }

    /**
     * @param string $rule
     * @param mixed $value
     * @param array $settings
     * @return void
     */
    private function validateField(string $rule, mixed $value, array $settings = []): void
    {
        $customClassRule = __NAMESPACE__ . '\\' . ucfirst($rule) . 'Validator';

        if (class_exists($customClassRule) === false) {
            throw new ('Класс ' . $customClassRule . ' не найден');
        }

        $customRule = new $customClassRule($settings);

        $customRule->validate($value);
    }
}
