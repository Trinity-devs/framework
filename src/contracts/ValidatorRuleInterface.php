<?php

namespace trinity\contracts;

interface ValidatorRuleInterface
{
    public function validateRule(mixed $value): void;
}