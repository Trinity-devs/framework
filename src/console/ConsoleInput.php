<?php

namespace trinity\console;

use RuntimeException;
use trinity\contracts\console\ConsoleCommandInterface;
use trinity\contracts\console\ConsoleInputInterface;
use trinity\exception\baseException\InvalidArgumentException;
use trinity\helpers\ArrayHelper;
use UnexpectedValueException;

class ConsoleInput implements ConsoleInputInterface
{
    private array $params;
    private array $arguments = [];
    private array $options = [];
    private Descriptor $descriptor;

    /**
     * @param array|null $argv
     */
    public function __construct(array $argv = null)
    {
        array_shift($argv);

        foreach ($argv as $item) {
            if (str_starts_with($item, '--')) {
                $this->options[] = new Option(substr($item, 2));
                continue;
            }
            $this->params[] = $item;
        }
    }

    /**
     * Возвращает имя команды из параметров командной строки
     *
     * @return string|null Имя команды или null, если имя отсутствует
     */
    public function getNameCommand(): string|null
    {
        return $this->params[0] ?? null;
    }

    /**
     * Присваивает определение команды к объекту ввода
     *
     * @param ConsoleCommandInterface $command Объект команды
     * @throws RuntimeException Если количество аргументов превышает ожидаемое
     */
    public function assignDescriptor(ConsoleCommandInterface $command): void
    {
        $this->descriptor = new Descriptor($command::getSignature());

        $this->parse();
    }

    /**
     * Парсит параметры командной строки и заполняет аргументы
     */
    private function parse(): void
    {
        foreach ($this->params as $key => $param) {
            if ($key === 0) {
                continue;
            }

            $this->parseArgument($param);
        }
    }

    /**
     * Парсит аргумент и устанавливает его значение
     *
     * @param string $value Сигнатура аргумента
     * @throws UnexpectedValueException Если количество аргументов превышает ожидаемое
     */
    private function parseArgument(string $value): void
    {
        foreach ($this->descriptor->getArguments() as $name) {
            if (isset($this->arguments[$name]) === true) {
                continue;
            }
            $this->setArgumentValue($name, $value);

            return;
        }

        throw new UnexpectedValueException('Агрументов слишком много');
    }

    /**
     * Устанавливает значение для указанного аргумента
     *
     * @param string $name Имя аргумента
     * @param null|string $value Значение аргумента
     */
    private function setArgumentValue(string $name, null|string $value): void
    {
        $this->arguments[$name] = is_numeric($value) === true ? (int)$value : $value;
    }

    /**
     * Проверяет, существует ли аргумент с указанным именем
     *
     * @param string $name Имя аргумента
     * @return bool true, если аргумент существует, иначе false
     */
    public function hasArgument(string $name): bool
    {
        return isset($this->arguments[$name]) === true;
    }

    /**
     * Возвращает значение аргумента по указанному имени
     *
     * @param string $name Имя аргумента
     * @return int|string Значение аргумента
     * @throws InvalidArgumentException Если аргумент с указанным именем не существует
     */
    public function getArgument(string $name): int|string
    {
        if (ArrayHelper::keyExists($name, $this->arguments) === false) {
            throw new InvalidArgumentException(sprintf('Аргумент "%s" не существует', $name));
        }

        return $this->arguments[$name];
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $optionName
     * @return bool
     */
    public function hasOption(string $optionName): bool
    {
        foreach ($this->options as $option) {
            if ($option->getOption() === $optionName) {
                return true;
            }
        }

        return false;
    }
}