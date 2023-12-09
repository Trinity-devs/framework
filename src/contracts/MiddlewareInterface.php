<?php

namespace trinity\contracts;

interface MiddlewareInterface
{
    public function handle(): void;
}