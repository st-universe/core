<?php

declare(strict_types=1);

namespace Stu\Lib;

final class AccountToken
{
    public const string PASSWORD_RESET = 'password-reset';
    public const string ACCOUNT_DELETION = 'account-deletion';

    public static function create(string $purpose): string
    {
        return sprintf('%s.%d.%s', $purpose, time() + 3600, bin2hex(random_bytes(32)));
    }

    public static function isValid(string $token, string $purpose): bool
    {
        return preg_match('/^' . preg_quote($purpose, '/') . '\\.(\\d{10})\\.[a-f0-9]{64}$/D', $token, $matches) === 1
            && (int) $matches[1] > time();
    }
}
