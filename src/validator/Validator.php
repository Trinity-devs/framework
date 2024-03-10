<?php

namespace trinity\validator;

use Throwable;
use trinity\contracts\validator\ValidatorInterface;
use trinity\exception\baseException\InvalidArgumentException;

class Validator implements ValidatorInterface
{
    private array $validatableRuleData = [];

    /**
     * @param AbstractFormRequest $form
     * @return void
     */
    public function validate(AbstractFormRequest $form): void
    {
        foreach ($form->rules() as $ruleItem) {

            $this->prepareValidatableRules($ruleItem);

            try {

                $this->search($form);

            } catch (Throwable $e) {

                $form->addError('Произошла непредвиденная ошибка при выполнении валидации формы', $e->getMessage());

            }
        }
    }

    /**
     * @param AbstractFormRequest $form
     * @return void
     * @throws InvalidArgumentException
     */
    private function search(AbstractFormRequest $form): void
    {
        foreach ($this->validatableRuleData['field'] as $ruleField) {

            if (is_callable($this->validatableRuleData['rule']) === true) {
                $this->validatableRuleData['rule']();

                continue;
            }

            $formAttribute = $form->getAttribute($ruleField);

            if ($form->hasAttribute($ruleField) === false && $this->validatableRuleData['rule'] === 'required') {
                $form->addError($ruleField, 'Поля ' . $ruleField . ' нет в передаваемой форме, либо не настроена метка формы');

                continue;
            }

            if ($formAttribute === null) {
                continue;
            }

            $this->validateField(
                rule: $this->validatableRuleData['rule'],
                value: $formAttribute,
                settings: $this->validatableRuleData['settings']
            );
        }
    }

    /**
     * @param array $ruleItem
     * @return void
     */
    private function prepareValidatableRules(array $ruleItem): void
    {
        $this->validatableRuleData['field'] = is_array($ruleItem[0]) ? $ruleItem[0] : [$ruleItem[0]];
        $this->validatableRuleData['rule'] = $ruleItem[1];
        $this->validatableRuleData['settings'] = isset($ruleItem[2]) ? array_slice($ruleItem, 2)[0] : [];
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
