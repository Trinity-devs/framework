<?php

namespace trinity\api\responses;

readonly class JsonResponse
{
    /**
     * @param array|null $data
     */
    public function __construct(public readonly array|null $data = null)
    {
    }
}
