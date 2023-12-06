<?php

namespace src\contracts;

interface ViewRendererInterface
{
    public function render(string $view, array $params = []): string;
}