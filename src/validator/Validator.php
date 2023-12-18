<?php

namespace trinity\validator;

class Validator
{
    private array $errors = [];

    private AbstractFormRequest $form;

    public function validate(AbstractFormRequest $form): bool
    {
        $this->form = $form;
        $rules = $this->form->rules();

        foreach ($rules as $field => $rule) {
            foreach ($rule as $ruleItem) {
                if (is_callable($ruleItem) === true) {
                    $value = $form->getDataValue($field);

                    if (call_user_func($ruleItem, $value, $form) === false) {
                        $this->addError($field, $ruleItem[1]);

                        return false;
                    }
                }

                $ruleName = $ruleItem['rule'];
                $params = array_slice($ruleItem, 1);

                if ($this->validateField($field, $ruleName, $params) === false) {
                    $this->addError($field, $ruleName . ' - ' . $ruleItem[1]);

                    return false;
                }
            }
        }

        return true;
    }

    public function getDataValue(): string|array
    {
        return $this->form->getDataValue();
    }

    public function setDataValue(string $field, mixed $value): void
    {
        $this->form->setDataValue($field, $value);
    }

    private function validateField(string $field, string $ruleName, array $params): bool
    {
        $classValidator = __NAMESPACE__ . '\\' . ucfirst($ruleName) . 'Validator';

        if (class_exists($classValidator) === false) {
            throw new ('Класс ' . $classValidator . ' не найден');
        }

        $validator = new $classValidator();

        return $validator->validate($field, $params, $this);
    }

    public function addError($field, $message): void
    {
        if (isset($this->errors[$field]) === false) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

}