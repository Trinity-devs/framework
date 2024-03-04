<?php

namespace trinity\validator;

use Throwable;
use trinity\contracts\validator\ValidatorInterface;
use trinity\exception\baseException\InvalidArgumentException;

class Validator implements ValidatorInterface
{
    private array $validateData = [];
    private array $ruleFields = [];

    /**
     * @param AbstractFormRequest $form
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(AbstractFormRequest $form): void
    {
        foreach ($form->rules() as $ruleItem) {
            $this->prepareValidatableRules($ruleItem);

            foreach ($this->validateData['field'] as $ruleField) {
                $this->ruleFields[] = $ruleField;

                try {
                    if (is_callable($this->validateData['rule']) === true) {
                        $this->validateData['rule']();

                        continue;
                    }

                    $value = $form->getAttribute($ruleField);

                    if ($form->hasAttribute($ruleField) === false && $this->validateData['rule'] === 'required') {
                        $form->addError($ruleField, 'Поля ' . $ruleField . ' нет в передаваемой форме, либо не настроена метка формы');

                        continue;
                    }

                    if ($value === null) {
                        continue;
                    }

                    $this->validateField(
                        $this->validateData['rule'],
                        $value,
                        $this->validateData['settings']
                    );
                } catch (Throwable $e) {
                    $form->addError($ruleField, $e->getMessage());
                }
            }
        }

        if ($form->getErrors() === null) {
            $form->deleteAttributes($this->searchBadAttributes($form->getAttributes()));
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

    private function searchBadAttributes(array $attributes): array
    {
        $uniqueRuleFields = array_unique($this->ruleFields);

        return array_diff_key($attributes, array_flip($uniqueRuleFields));
    }
}
