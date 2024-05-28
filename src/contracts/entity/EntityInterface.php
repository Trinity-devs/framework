<?php

declare(strict_types=1);

namespace trinity\contracts\entity;

interface EntityInterface
{
    public function getExternalId(): string;
    public function getIssuer(): string;
    public function getId(): int;
}
