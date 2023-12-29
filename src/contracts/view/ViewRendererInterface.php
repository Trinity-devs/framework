<?php

namespace trinity\contracts\view;

interface ViewRendererInterface
{
    public function render(string $view, array $params = []): string;
}