<?php

namespace trinity\console;

use trinity\{contracts\console\ConsoleCommandInterface,
    contracts\console\ConsoleKernelInterface,
    contracts\console\ConsoleOutputInterface};

class ListCommand implements ConsoleCommandInterface
{
    private static string $signature = 'list';
    private static string $description = 'Вывод информации о доступных командах';
    private static bool $hidden = true;

    /**
     * @param ConsoleKernelInterface $kernel
     * @param ConsoleOutputInterface $output
     */
    public function __construct(
        private readonly ConsoleKernelInterface $kernel,
        private readonly ConsoleOutputInterface $output,
    )
    {
    }

    /**
     * @return string
     */
    public static function getSignature(): string
    {
        return self::$signature;
    }

    /**
     * @return string
     */
    public static function getDescription(): string
    {
        return self::$description;
    }

    /**
     * @return bool
     */
    public static function getHidden(): bool
    {
        return self::$hidden;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->printFrameworkInfo();
        $this->printListCommands();
//        $this->printListOptions();
    }

    /**
     * @return void
     */
    private function printListCommands(): void
    {
        $this->output->ansiFormat('Доступные команды:', ConsoleColors::GREEN);
        $this->output->writeLn();

        $commands = $this->kernel->getCommandMap();

        foreach ($commands as $key => $command) {
            if ($command['isHidden'] === false) {
                $this->output->ansiFormat(
                    str_pad("\t$key", 30),
                    ConsoleColors::YELLOW
                );

                $this->output->stdout("{$command['description']}");
                $this->output->writeLn(2);
            }
        }

    }

//    private function printListOptions(): void
//    {
//          $this->output->ansiFormat('Доступные опции:', ConsoleColors::GREEN);
//          $this->output->writeLn();
//
//        $options = require PROJECT_ROOT . 'config/options.php';
//
//        foreach ($options['available-options'] as $key => $list) {
//              $this->output->ansiFormat(
//                str_pad("\t--$key", 30),
//                ConsoleColors::YELLOW
//            );
//              $this->output->stdout("$list[1]");
//              $this->output->writeLn();
//        }
//    }

    /**
     * @return void
     */
    private function printFrameworkInfo(): void
    {
          $this->output->ansiFormat('Trinity-devs 0.1.3', ConsoleColors::CYAN);
          $this->output->writeLn(2);
          $this->output->ansiFormat(
            'Фреймворк создан разработчиками компании ЭФКО Цифровые решения.',
            ConsoleColors::YELLOW
        );
          $this->output->writeLn();
          $this->output->ansiFormat(
            'Является платформой для изучения базового поведения приложения созданного на PHP.',
            ConsoleColors::YELLOW
        );
          $this->output->writeLn();
          $this->output->ansiFormat(
            'Фреймворк не является production-ready реализацией и не предназначен для коммерческого использования.',
            ConsoleColors::YELLOW
        );
          $this->output->writeLn(2);
          $this->output->ansiFormat('Вызов:', ConsoleColors::GREEN);
          $this->output->writeLn();
          $this->output->stdout("\tкоманда [аргументы] [опции]");
          $this->output->writeLn(2);
    }
}
