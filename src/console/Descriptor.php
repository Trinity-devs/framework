<?php

namespace src\console;

use InvalidArgumentException;

class Descriptor
{
    private array $arguments = [];

    /**
     * @param string $signature
     */
    public function __construct(string $signature)
    {
        $this->parseSignature($signature);
    }

    /**
     * Инициализирует определение команды на основе сигнатуры
     *
     * @param string $signature Сигнатура команды
     * @throws InvalidArgumentException Если имя команды не определено в сигнатуре
     */
    private function parseSignature(string $signature): void
    {
        if (preg_match('/^([\w\S]+)/', $signature, $matches) === false) {
            throw new InvalidArgumentException('Имя команды не определено');
        }

        preg_match_all('/{\s*(.*?)\s*}/', $signature, $matches);

        if (empty($matches[1])) {
            return;
        }

        foreach ($matches[1] as $argument) {
            if (preg_match('/--(.*)/', $argument, $matches)) {
                continue;
            }

            $this->parseArgument($argument);
        }
    }

    /**
     * Инициализирует информацию об аргументе на основе сигнатуры
     *
     * @param string $argument Сигнатура аргумента
     * @throws InvalidArgumentException Если аргумент уже был инициализирован ранее
     */
    private function parseArgument(string $argument): void
    {
        $key = strtok($argument, '?=:');

        if (isset($this->arguments[$key]) === true) {
            throw new InvalidArgumentException('Аргумент инициализирован ранее');
        }
        if (str_contains($argument, ':') === true) {
            [, $description] = explode(':', $argument, 2);
            $this->arguments[$key]= $description;
        }
    }

    /**
     * Возвращает массив с именами аргументов команды
     *
     * @return array Имена аргументов команды
     */
    public function getArguments(): array
    {
        return array_keys($this->arguments);
    }
}