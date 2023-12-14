<?php

namespace trinity\apiResponses;

readonly class CreateResponse
{
    /**
     * @param array|null $data
     */
    public function __construct(public readonly array|null $data = null)
    {
    }
}