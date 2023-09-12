<?php

use Stu\Exception\InvalidParamException;

class request
{
    /** @var array<string, mixed> */
    private static ?array $mockVars = null;

    /**
     * @return array<string, mixed>
     */
    public static function getvars(): array
    {
        if (self::$mockVars !== null) {
            return self::$mockVars;
        }

        global $_GET;
        return $_GET;
    }

    /**
     * @return array<string, mixed>
     */
    public static function postvars(): array
    {
        global $_POST;
        return $_POST;
    }

    public static function isRequest(): bool
    {
        return array_key_exists('REQUEST_METHOD', $_SERVER);
    }

    public static function isPost(): bool
    {
        if (!static::isRequest()) {
            return false;
        }

        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public static function has(string $key): bool
    {
        return self::getvars()[$key] ?? self::postvars()[$key] ?? false;
    }

    /**
     * @param array<int|string, mixed> $method
     *
     * @return mixed
     */
    public static function getVarByMethod(array $method, string $var, bool $fatal = false)
    {
        if (!@array_key_exists($var, $method)) {
            if ($fatal === true) {
                throw new InvalidParamException($var);
            }
            return false;
        }
        return $method[$var];
    }

    public static function getInt(string $var, int $std = 0): int
    {
        $int = self::getVarByMethod(self::getvars(), $var);
        if (strlen($int) == 0) {
            return $std;
        }
        return self::returnInt($int);
    }

    public static function getIntFatal(string $var): int
    {
        $int = self::getVarByMethod(self::getvars(), $var, true);
        return self::returnInt($int);
    }

    public static function postInt(string $var): int
    {
        $int = self::getVarByMethod(self::postvars(), $var);
        return self::returnInt($int);
    }

    public static function postIntFatal(string $var): int
    {
        $int = self::getVarByMethod(self::postvars(), $var, true);
        return self::returnInt($int);
    }

    public static function getString(string $var): string|false
    {
        return self::getVarByMethod(self::getvars(), $var);
    }

    public static function postString(string $var): string|false
    {
        return self::getVarByMethod(self::postvars(), $var);
    }

    public static function indString(string $var): string|false
    {
        $value = self::getVarByMethod(self::postvars(), $var);
        if ($value) {
            return $value;
        }
        return self::getVarByMethod(self::getvars(), $var);
    }

    public static function indInt(string $var): int
    {
        $value = self::getVarByMethod(self::postvars(), $var);
        if ($value) {
            return self::returnInt($value);
        }
        return self::returnInt(self::getVarByMethod(self::getvars(), $var));
    }

    public static function postStringFatal(string $var): string
    {
        return self::getVarByMethod(self::postvars(), $var, true);
    }

    public static function getStringFatal(string $var): string
    {
        return self::getVarByMethod(self::getvars(), $var, true);
    }

    /**
     * @return array<int|string, mixed>
     */
    public static function postArrayFatal(string $var): array
    {
        return self::returnArray(self::getVarByMethod(self::postvars(), $var, true));
    }

    /**
     * @return array<int|string, mixed>
     */
    public static function postArray(string $var): array
    {
        return self::returnArray(self::getVarByMethod(self::postvars(), $var));
    }

    public static function returnInt($result): int
    {
        if (
            !$result
            || $result < 0
        ) {
            return 0;
        }
        return (int) $result;
    }

    /**
     * @param mixed $result
     *
     * @return array<int|string, mixed>
     */
    public static function returnArray($result): array
    {
        if (!is_array($result)) {
            return [];
        }
        return $result;
    }

    /**
     * @param mixed $value
     */
    public static function setVar(string $var, $value): void
    {
        global $_GET, $_POST;
        $_GET[$var] = $value;
        $_POST[$var] = $value;
    }

    public static function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /** @param array<string, mixed> $mockVars */
    public static function setMockVars(?array $mockVars): void
    {
        self::$mockVars = $mockVars;
    }
}
