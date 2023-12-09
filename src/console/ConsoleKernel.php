<?php

namespace src\console;

use src\DIContainer;
use InvalidArgumentException;
use ReflectionException;
use src\contracts\ConsoleInputInterface;
use src\contracts\ConsoleKernelInterface;
use src\contracts\ConsoleOutputInterface;
use src\contracts\eventsContracts\EventDispatcherInterface;
use src\Event;
use src\eventDispatcher\Message;
use src\exception\baseException\Exception;
use src\exception\consoleException\UnknownCommandException;

class ConsoleKernel implements ConsoleKernelInterface
{
    private string $defaultCommandName = 'list';
    private array $commandMap = [];

    /**
     * @param ConsoleInputInterface $input
     * @param ErrorHandler $errorHandler
     */
    public function __construct(
        private ConsoleInputInterface $input,
        private ConsoleOutputInterface $output,
        private ErrorHandler $errorHandler,
        private EventDispatcherInterface $eventDispatcher,
        private DIContainer $container,
    )
    {
        $this->errorHandler->register();
        $this->initializeDefaultCommands();
    }

    /**
     * Инициализирует зарегистрированные команды по умолчанию
     */
    private function initializeDefaultCommands(): void
    {
        $this->registerCommand(ListCommand::class);
    }

    /**
     * Регистрирует команду в массиве команд
     *
     * @param string $commandClassName Пространство имен команды для регистрации
     */
    private function registerCommand(string $commandClassName): void
    {
        preg_match('/[^\s]+/', $commandClassName::getSignature(), $matches);

        if (empty($matches) === true) {
            throw new InvalidArgumentException('Команда не содержит сигнатуру');
        }

        $this->commandMap[$matches[0]] = [
            'name' => $commandClassName,
            'description' => $commandClassName::getDescription(),
            'isHidden' => $commandClassName::getHidden()
        ];
    }

    /**
     * Регистрирует массив пространств имен для команд
     *
     * @param array $commandNameSpaces Массив пространств имен для регистрации команд
     */
    public function registerCommandNamespaces(array $commandNameSpaces): void
    {
        foreach ($commandNameSpaces as $nameSpace) {
            $this->registerCommandNamespace($nameSpace);
        }
    }

    /**
     * Регистрирует пространство имен для команд из указанного пути
     *
     * @param string $commandNameSpace Путь к пространству имен с командами
     */
    private function registerCommandNamespace(string $commandNameSpace): void
    {
        $paths = scandir($commandNameSpace);
        foreach ($paths as $path) {
            $file = $commandNameSpace . DIRECTORY_SEPARATOR . $path;

            if (is_file($file) === false) {
                continue;
            }

            $namespace = str_replace([PROJECT_ROOT, '/', '.php'], ['', '\\', ''], $file);

            $this->registerCommand($namespace);
        }
    }

    /**
     * Завершает выполнение приложения с указанным статусом
     *
     * @param int $exitStatus Статус завершения
     */
    public function terminate(int $exitStatus): void
    {
        exit($exitStatus);
    }

    /**
     * Обрабатывает входные данные и выполняет соответствующую команду
     *
     * @return int Код завершения выполнения команды
     *
     * @throws ReflectionException
     */
    public function handle(): int
    {
        try {

            $this->eventDispatcher->trigger(Event::CONSOLE_INPUT_READY->value, new Message($this->input));

            $commandName = $this->input->getNameCommand() ?? $this->defaultCommandName;
            $commandClassName = $this->commandMap[$commandName]['name']
                ?? throw new UnknownCommandException("Команда $commandName не найдена");

            $this->eventDispatcher->trigger(Event::CONSOLE_COMMAND_STARTED->value, new Message(''));
            $this->container->build($commandClassName)->execute();
            $this->eventDispatcher->trigger(Event::CONSOLE_COMMAND_DONE->value, new Message(''));

        } catch (Exception $exception) {

            $this->errorHandler->handleException($exception);

        }

        return 0;
    }

    /**
     * @return array
     */
    public function getCommandMap(): array
    {
        return $this->commandMap;
    }

    public function registerPlugins(array $plugins): void
    {
        foreach ($plugins as $pluginClassName) {
            (new $pluginClassName($this->eventDispatcher, $this, $this->input, $this->output))->init();
        }
    }
}
