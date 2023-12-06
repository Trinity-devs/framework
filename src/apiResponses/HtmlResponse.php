<?php

namespace src\apiResponses;

readonly class HtmlResponse
{
    /**
     * @param string|null $data
     */
    public function __construct(public readonly string|null $data = null)
    {
    }
}