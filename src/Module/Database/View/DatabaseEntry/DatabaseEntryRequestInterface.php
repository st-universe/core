<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

interface DatabaseEntryRequestInterface
{
    public function getCategoryId(): int;

    public function getEntryId(): int;
}
