<?php

namespace trinity\contracts\router;

interface RouterInterface
{
    public function dispatch(): object;

    public function getTypeResponse(): string;
}