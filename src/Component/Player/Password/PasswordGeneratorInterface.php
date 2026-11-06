<?php

declare(strict_types=1);

namespace Stu\Component\Player\Password;

interface PasswordGeneratorInterface
{
    /**
     * Generate one password based on options.
     *
     * @return string password
     */
    public function generatePassword(int $length = 10): string;
}