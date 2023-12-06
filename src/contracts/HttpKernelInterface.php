<?php

namespace src\contracts;

interface HttpKernelInterface
{
    public function handle(): ResponseInterface;
}