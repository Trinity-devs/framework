<?php

namespace src\console\options;

class Option
{
    private string $option;

    private bool $isEnabled = false;

    /**
     * @param string $inputOptions
     */
    public function __construct(string $inputOptions)
    {
        $this->option = $inputOptions;
    }

    /**
     * @return void
     */
    public function turnOnOption()
    {
        $this->isEnabled = true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return string
     */
    public function getOption(): string
    {
        return $this->option;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->option;
    }
}