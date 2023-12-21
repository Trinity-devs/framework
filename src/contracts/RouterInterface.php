<?php

namespace trinity\contracts;

interface RouterInterface
{
    public function dispatch(): object;

    public function getTypeResponse(): string;
}