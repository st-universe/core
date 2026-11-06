<?php

declare(strict_types=1);

namespace Stu\Component\Player\Password;

class PasswordGenerator implements PasswordGeneratorInterface
{
    private const string CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    #[\Override]
    public function generatePassword(int $length = 10): string
    {
        $password = '';
        $max = strlen(self::CHARACTERS) - 1;

        for ($i = 0; $i < $length; ++$i) {
            $password .= self::CHARACTERS[random_int(0, $max)];
        }

        return $password;
    }
}
