<?php

namespace src\contracts\eventsContracts;

use src\contracts\ObserverInterface;
use src\eventDispatcher\Message;

interface EventDispatcherInterface
{
    public function attach(string $eventName, ObserverInterface $observer): void;

    public function detach(string $eventName): void;

    public function trigger(string $eventName, Message $message): void;

    public function configure(array $config): void;
}