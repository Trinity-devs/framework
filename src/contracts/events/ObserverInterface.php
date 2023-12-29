<?php

namespace trinity\contracts\events;

interface ObserverInterface
{
    /**
     * Метод, вызываемый при получении уведомления о событии.
     *
     * @param MessageInterface $message Сообщение, содержащее информацию о событии.
     */
    function observe(MessageInterface $message): void;
}