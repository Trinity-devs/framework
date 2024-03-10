<?php

namespace trinity\contracts\http;

interface HttpKernelInterface
{
    public function handle(): ResponseInterface;
}
