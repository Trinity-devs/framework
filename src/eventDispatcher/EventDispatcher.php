<?php

namespace src\eventDispatcher;

use src\contracts\eventsContracts\EventDispatcherInterface;
use src\contracts\ObserverInterface;
use src\exception\baseException\LogicException;

class EventDispatcher implements EventDispatcherInterface
{
    private array $eventObservers = [];

    /**
     * Конфигурирует EventDispatcher с использованием предоставленного массива конфигурации
     *
     * @param array $config Массив конфигурации, где каждый элемент представляет собой массив [Event $event, ObserverInterface $observer]
     * @throws LogicException Если EventDispatcher уже был сконфигурирован ранее
     */
    public function configure(array $config): void
    {
        if (empty($this->eventObservers) === false) {
            throw new LogicException('EventDispatcher нельзя конфигурировать повторно.');
        }

        foreach ($config as $value) {
            $this->attach(...$value);
        }
    }

    /**
     * Подписывает наблюдателя к определенному событию
     *
     * @param string $eventName Имя события, к которому присоединяется наблюдатель
     * @param ObserverInterface $observer Наблюдатель, который будет присоединен
     */
    public function attach(string $eventName, ObserverInterface $observer): void
    {
        $this->eventObservers[$eventName] = $observer;
    }

    /**
     * Отписывает наблюдателя от определенного события
     *
     * @param string $eventName имя события, от которого отсоединяется наблюдатель
     */
    public function detach(string $eventName): void
    {
        if (isset($this->eventObservers[$eventName]) === false) {
            return;
        }

        unset($this->eventObservers[$eventName]);
    }

    /**
     * Запускает событие и уведомляет соответствующего наблюдателя с переданным сообщением
     *
     * @param string $eventName Имя события, которое будет запущено
     * @param Message $message Сообщение, передаваемое наблюдателю
     */
    public function trigger(string $eventName, Message $message): void
    {
        if (isset($this->eventObservers[$eventName]) === false) {
            return;
        }

        $this->eventObservers[$eventName]->observe($message);
    }
}