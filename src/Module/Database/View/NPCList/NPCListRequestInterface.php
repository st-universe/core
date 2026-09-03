<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\NPCList;

interface NPCListRequestInterface
{
    public function getSortField(): string;

    public function getSortOrder(): string;

    public function getPagination(): int;
}
