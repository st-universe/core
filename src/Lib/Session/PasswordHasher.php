<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

final class PasswordHasher implements PasswordHasherInterface
{
    public function __construct(private readonly ?int $cost = null) {}

    #[\Override]
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT, $this->cost === null ? [] : ['cost' => $this->cost]);
    }

    #[\Override]
    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
