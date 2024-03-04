<?php

namespace trinity\eventDispatcher;

use trinity\contracts\events\MessageInterface;

class Message implements MessageInterface
{
    public string $eventName;

    /**
     * @param mixed $message
     */
    public function __construct(readonly public mixed $message)
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
