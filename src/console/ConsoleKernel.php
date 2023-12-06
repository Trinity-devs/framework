<?php

namespace src\console;

use src\contracts\ConsoleInputInterface;
use src\contracts\ConsoleKernelInterface;
use src\contracts\KernelPluginInterface;
use src\DIContainer;
use src\exception\baseException\Exception;
use src\exception\consoleException\UnknownCommandException;
use InvalidArgumentException;
use ReflectionException;
use src\Event;

class ConsoleKernel implements ConsoleKernelInterface
{
    private string $defaultCommandName = 'list';
    private array $commandMap = [];
    private array $plugins = [];

    /**
     * @param ConsoleInputInterface $input
     * @param ErrorHandler $errorHandler
     */
    public function __construct(
        private readonly ConsoleInputInterface $input,
        private ErrorHandler $errorHandler,
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

            $commandName = $this->input->getNameCommand() ?? $this->defaultCommandName;
            $commandClassName = $this->commandMap[$commandName]['name']
                ?? throw new UnknownCommandException("Команда $commandName не найдена");

            $this->init(Event::COMMAND_START->value);
            $this->container->build($commandClassName)->execute();
            $this->init(Event::COMMAND_DONE->value);

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

    public function init(string $event): void
    {
        if (isset($this->plugins[$event]) === false) {
            return;
        }

        foreach ($this->plugins[$event] as $plugin) {
            $plugin->init();
        }
        $this->plugins[$event]->init();
    }

    public function addPlugin(string $event, KernelPluginInterface $plugin): void
    {
        foreach ($this->input->getOptions() as $optionInstance) {
            if ($optionInstance->getOption() !== $plugin->getOptionSignature()) {
                throw new \Exception('Неизвестная опция' . $optionInstance->getOption());
            }

            if ($optionInstance->getOption() === $plugin->getOptionSignature()) {
                $this->plugins[$event] = $plugin;
            }
        }
    }

    public function registerPlugins(array $plugins): void
    {
        foreach ($plugins as $value) {
            $this->addPlugin(...$value);
        }
    }
}
