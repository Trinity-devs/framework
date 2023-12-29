<?php

namespace trinity\contracts\validator;

interface ValidatorRuleInterface
{
    public function validateRule(mixed $value): void;
}