<?php

namespace trinity\contracts\middleware;

interface MiddlewareInterface
{
    public function handle(): void;
}