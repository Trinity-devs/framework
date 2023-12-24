<?php

namespace trinity\api\responses;

readonly class AuthResponse
{
    /**
     * @param array|null $data
     */
    public function __construct(public readonly array|null $data = null)
    {
    }
}