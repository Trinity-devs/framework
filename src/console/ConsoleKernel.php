<?php

namespace trinity\console;

use InvalidArgumentException;
use ReflectionException;
use trinity\{contracts\container\ContainerInterface,
    contracts\handlers\error\ErrorHandlerConsoleInterface,
    contracts\console\ConsoleInputInterface,
    contracts\console\ConsoleKernelInterface,
    contracts\console\ConsoleOutputInterface,
    contracts\events\EventDispatcherInterface,
    eventDispatcher\Event,
    eventDispatcher\Message,
    exception\consoleException\UnknownCommandException};
use Throwable;

class ConsoleKernel implements ConsoleKernelInterface
{
    private string $defaultCommandName = 'list';
    private array $commandMap = [];

    /**
     * @param ConsoleInputInterface $input
     * @param ConsoleOutputInterface $output
     * @param ErrorHandlerConsoleInterface $errorHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param ContainerInterface $container
     */
    public function __construct(
        private ConsoleInputInterface $input,
        private ConsoleOutputInterface $output,
        private EventDispatcherInterface $eventDispatcher,
        private ErrorHandlerConsoleInterface $errorHandler,
        private ContainerInterface $container,
    ) {
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
            $this->eventDispatcher->trigger(Event::CONSOLE_INPUT_READY, new Message($this->input));

            $commandName = $this->input->getNameCommand() ?? $this->defaultCommandName;
            $commandClassName = $this->commandMap[$commandName]['name']
                ?? throw new UnknownCommandException("Команда $commandName не найдена");

            $this->eventDispatcher->trigger(Event::CONSOLE_COMMAND_STARTED, new Message(''));
            $this->container->build($commandClassName)->execute();
            $this->eventDispatcher->trigger(Event::CONSOLE_COMMAND_DONE, new Message(''));
        } catch (Throwable $exception) {
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

    /**
     * @param array $plugins
     * @return void
     */
    public function registerPlugins(array $plugins): void
    {
        foreach ($plugins as $pluginClassName) {
            (new $pluginClassName($this->eventDispatcher, $this, $this->input, $this->output))->init();
        }
    }
}
