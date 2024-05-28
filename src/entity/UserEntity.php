<?php

declare(strict_types=1);

namespace trinity\entity;

use trinity\contracts\entity\EntityInterface;

readonly final class UserEntity implements EntityInterface
{
    public function __construct(
        private int $id,
        private string $issuer,
        private string $externalId
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIssuer(): string
    {
        return $this->issuer;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }
}
