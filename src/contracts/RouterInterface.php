<?php

namespace src\contracts;

interface RouterInterface
{
    public function dispatch(): object;

    public function getControllerName(): string;
}