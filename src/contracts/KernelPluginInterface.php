<?php

namespace trinity\contracts;

interface KernelPluginInterface
{
    public function init(): void;

    public function getOptionSignature(): string;
}