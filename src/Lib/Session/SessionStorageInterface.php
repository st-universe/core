<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

interface SessionStorageInterface
{
    public function storeSessionData(string $key, mixed $value, bool $isSingleValue = false): void;

    public function deleteSessionData(string $key, mixed $value = null): void;

    public function hasSessionValue(string $key, mixed $value): bool;

    public function getSessionValue(string $key): mixed;
}
