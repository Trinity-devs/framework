<?php

declare(strict_types=1);

namespace trinity\eventDispatcher;

use BackedEnum;
use trinity\contracts\events\{EventDispatcherInterface, MessageInterface, ObserverInterface};
use trinity\exception\baseException\LogicException;

final class EventDispatcher implements EventDispatcherInterface
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
     * @param BackedEnum $event Событие, к которому присоединяется наблюдатель
     * @param ObserverInterface $observer Наблюдатель, который будет присоединен
     */
    public function attach(BackedEnum $event, ObserverInterface $observer): void
    {
        $this->eventObservers[$event->value] = $observer;
    }

    /**
     * Отписывает наблюдателя от определенного события
     *
     * @param BackedEnum $event Событие, от которого отсоединяется наблюдатель
     */
    public function detach(BackedEnum $event): void
    {
        if (isset($this->eventObservers[$event->value]) === false) {
            return;
        }

        unset($this->eventObservers[$event->value]);
    }

    /**
     * Запускает событие и уведомляет соответствующего наблюдателя с переданным сообщением
     *
     * @param BackedEnum $event Событие, которое будет запущено
     * @param Message $message Сообщение, передаваемое наблюдателю
     */
    public function trigger(BackedEnum $event, MessageInterface $message): void
    {
        if (isset($this->eventObservers[$event->value]) === false) {
            return;
        }

        $message->eventName = $event->value;

        $this->eventObservers[$event->value]->observe($message);
    }
}
