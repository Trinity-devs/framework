<?php

namespace trinity\contracts;

use trinity\eventDispatcher\Message;

interface ObserverInterface
{
    /**
     * Метод, вызываемый при получении уведомления о событии.
     *
     * @param Message $message Сообщение, содержащее информацию о событии.
     */
    function observe(Message $message): void;
}