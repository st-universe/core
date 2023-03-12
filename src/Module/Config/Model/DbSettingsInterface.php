<?php

namespace Stu\Module\Config\Model;

interface DbSettingsInterface
{
    public function useSqlite(): bool;

    public function getDatabase(): string;

    public function getProxyNamespace(): string;
}
