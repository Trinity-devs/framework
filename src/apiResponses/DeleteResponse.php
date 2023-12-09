<?php

namespace trinity\apiResponses;

readonly class DeleteResponse
{
    /**
     * @param array|null $data
     */
    public function __construct(public readonly array|null $data = null)
    {
    }
}