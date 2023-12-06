<?php

namespace src\eventDispatcher;

use src\contracts\eventsContracts\MessageInterface;

readonly class Message implements MessageInterface
{
    /**
     * @param mixed $message
     */
    public function __construct(public mixed $message)
    {
    }

    /**
     * @return mixed
     */
    public function getMessage(): mixed
    {
        return $this->message;
    }
}