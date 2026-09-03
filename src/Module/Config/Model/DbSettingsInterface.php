<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

interface DbSettingsInterface
{
    public function useSqlite(): bool;

    public function getSqliteDsn(): string;

    public function getDatabase(): string;
}
