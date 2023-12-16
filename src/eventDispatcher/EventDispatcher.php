<?php

namespace trinity\eventDispatcher;

use trinity\contracts\eventsContracts\EventDispatcherInterface;
use trinity\contracts\ObserverInterface;
use trinity\exception\baseException\LogicException;
use UnitEnum;

class EventDispatcher implements EventDispatcherInterface
{
    private array $eventObservers = [];

    /**
     * Конфигурирует EventDispatcher с использованием предоставленного массива конфигурации
     *
     * @param array $config Массив конфигурации, где каждый элемент представляет собой массив [UnitEnum $event, ObserverInterface $observer]
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
     * @param UnitEnum $event Событие, к которому присоединяется наблюдатель
     * @param ObserverInterface $observer Наблюдатель, который будет присоединен
     */
    public function attach(UnitEnum $event, ObserverInterface $observer): void
    {
        $this->eventObservers[$event->value] = $observer;
    }

    /**
     * Отписывает наблюдателя от определенного события
     *
     * @param UnitEnum $event Событие, от которого отсоединяется наблюдатель
     */
    public function detach(UnitEnum $event): void
    {
        if (isset($this->eventObservers[$event->value]) === false) {
            return;
        }

        unset($this->eventObservers[$event->value]);
    }

    /**
     * Запускает событие и уведомляет соответствующего наблюдателя с переданным сообщением
     *
     * @param UnitEnum $event Событие, которое будет запущено
     * @param Message $message Сообщение, передаваемое наблюдателю
     */
    public function trigger(UnitEnum $event, Message $message): void
    {
        if (isset($this->eventObservers[$event->value]) === false) {
            return;
        }

        $message->eventName = $eventName;

        $this->eventObservers[$event->value]->observe($message);
    }
}