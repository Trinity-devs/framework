<?php

namespace trinity\validator;

class Validator
{
    private array $errors = [];

    private AbstractFormRequest $form;

    public function validate(AbstractFormRequest $form): bool
    {
        $this->form = $form;
        $rulesItems = $this->form->rules();

        foreach ($rulesItems as $ruleItem) {
            $fields = $ruleItem[0];
            $rule = $ruleItem[1];
            $params = [];

            if (isset($ruleItem[2]) === true) {
                $params = array_slice($ruleItem, 2);
            }

            if (is_string($fields) === true) {
                $this->validateField($fields, $rule, $params);

                continue;
            }

            foreach ($fields as $field) {
                if (is_callable($rule) === true) {
                    $this->validateCallbackRule($rule, $field, $params);

                    continue;
                }

                $this->validateField($field, $rule, $params);
            }
        }

        if (empty($this->errors) === true) {
            return true;
        }

        return false;
    }

    public function validateCallbackRule(callable $callback, string $field, array $params = []): void
    {
        $value = $this->getDataValue($field);

        if ($callback($value, $params) === false) {
            $this->addError($field, 'callback');
        }
    }

    public function getDataValues(): array|string
    {
        return $this->form->getData();
    }

    public function getDataValue(string $field): string
    {
        return $this->form->getData()[$field];
    }

    public function setDataValue(string $field, mixed $value): void
    {
        $data = $this->form->getData();
        $data[$field] = $value;
        $this->form->setData($data);
    }

    private function validateField(string $field, string $ruleItem, array $params = []): void
    {
        $classValidator = __NAMESPACE__ . '\\' . ucfirst($ruleItem) . 'Validator';

        if (class_exists($classValidator) === false) {
            throw new ('Класс ' . $classValidator . ' не найден');
        }

        $validator = new $classValidator();

        $validator->validate($field, $params, $this);
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function getErrors(): string
    {
        $answer = '';

        foreach ($this->errors as $key => $value) {
            $answer .= $key . ' - ' . $value . PHP_EOL;
        }

        return $answer;
    }
}
