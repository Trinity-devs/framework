<?php

namespace trinity\contracts;

interface RouterInterface
{
    public function dispatch(): object;

    public function getControllerName(): string;
}