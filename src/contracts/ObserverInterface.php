<?php

namespace src\contracts;

use src\eventDispatcher\Message;

interface ObserverInterface
{
    /**
     * Метод, вызываемый при получении уведомления о событии.
     *
     * @param Message $message Сообщение, содержащее информацию о событии.
     */
    function observe(Message $message): void;
}