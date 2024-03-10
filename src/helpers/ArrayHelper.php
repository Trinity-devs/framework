<?php

declare(strict_types=1);

namespace trinity\helpers;

use ArrayAccess;
use Closure;
use Throwable;
use trinity\exception\baseException\InvalidArgumentException;

final class ArrayHelper
{
    /**
     * Получить ключ из массива
     *
     * @param array|object $array
     * @param array|object|string $key
     * @param mixed|null $default
     * @return mixed
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public static function getValue(array|object $array, array|object|string $key, mixed $default = null): mixed
    {
        if ($key instanceof Closure) {
            return $key($array, $default);
        }

        if (is_array($key) === true) {
            $lastKey = array_pop($key);

            foreach ($key as $keyPart) {
                $array = static::getValue($array, $keyPart);
            }

            $key = $lastKey;
        }

        if (is_object($array) === true && property_exists($array, $key) === true) {
            return $array->$key;
        }

        if (static::keyExists($key, $array)) {
            return $array[$key];
        }

        if ($key && ($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (static::keyExists($key, $array)) {
            return $array[$key];
        }

        if (is_object($array)) {
            try {
                return $array->$key;
            } catch (Throwable $e) {
                if ($array instanceof ArrayAccess) {
                    return $default;
                }

                throw $e;
            }
        }

        return $default;
    }

    /**
     * @param array $array
     * @param Closure|string $from
     * @param Closure|string $to
     * @return array
     * @throws Throwable
     */
    public static function map(
        array $array,
        Closure|string $from,
        Closure|string $to,
    ): array {
        $result = [];
        foreach ($array as $element) {
            $key = static::getValue($element, $from);
            $value = static::getValue($element, $to);

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Проверяет есть ли указанный ключ в массиве
     *
     * @param int|string $key
     * @param array|ArrayAccess $array
     * @param bool $caseSensitive
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function keyExists(int|string $key, array|ArrayAccess $array, bool $caseSensitive = true): bool
    {
        if ($caseSensitive === true) {
            if (is_array($array) === true && array_key_exists($key, $array) === true) {
                return true;
            }

            return $array instanceof ArrayAccess && $array->offsetExists($key);
        }

        if ($array instanceof ArrayAccess) {
            throw new InvalidArgumentException('Второй параметр не может быть типа ArrayAccess, когда caseSensitive false');
        }

        foreach (array_keys($array) as $k) {
            if (strcasecmp($key, $k) === 0) {
                return true;
            }
        }

        return false;
    }
}
