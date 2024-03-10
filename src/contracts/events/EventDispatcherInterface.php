<?php

namespace trinity\contracts\events;

use BackedEnum;

interface EventDispatcherInterface
{
    public function attach(BackedEnum $event, ObserverInterface $observer): void;

    public function detach(BackedEnum $event): void;

    public function trigger(BackedEnum $event, MessageInterface $message): void;

    public function configure(array $config): void;
}
