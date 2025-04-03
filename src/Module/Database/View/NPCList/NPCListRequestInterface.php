<?php

namespace Stu\Module\Database\View\NPCList;

interface NPCListRequestInterface
{
    public function getSortField(): string;

    public function getSortOrder(): string;

    public function getPagination(): int;
}