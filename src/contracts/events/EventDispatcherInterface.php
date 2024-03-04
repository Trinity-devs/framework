<?php

namespace trinity\contracts\events;

use UnitEnum;

interface EventDispatcherInterface
{
    public function attach(UnitEnum $event, ObserverInterface $observer): void;

    public function detach(UnitEnum $event): void;

    public function trigger(UnitEnum $event, MessageInterface $message): void;

    public function configure(array $config): void;
}
