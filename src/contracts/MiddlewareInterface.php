<?php

namespace src\contracts;

interface MiddlewareInterface
{
    public function handle(): void;
}