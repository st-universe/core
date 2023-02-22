<?php

namespace Stu\Module\Database\View\DatabaseEntry;

interface DatabaseEntryRequestInterface
{
    public function getCategoryId(): int;

    public function getEntryId(): int;
}
