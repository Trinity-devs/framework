<?php

namespace trinity\contracts;

interface HttpKernelInterface
{
    public function handle(): ResponseInterface;
}