<?php

namespace trinity\contracts\eventsContracts;

use trinity\contracts\ObserverInterface;
use trinity\eventDispatcher\Message;
use UnitEnum;

interface EventDispatcherInterface
{
    public function attach(UnitEnum $event, ObserverInterface $observer): void;

    public function detach(UnitEnum $event): void;

    public function trigger(UnitEnum $event, Message $message): void;

    public function configure(array $config): void;
}