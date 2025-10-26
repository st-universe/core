<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DatabaseEntryRequest implements DatabaseEntryRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCategoryId(): int
    {
        return $this->parameter('cat')->int()->required();
    }

    #[\Override]
    public function getEntryId(): int
    {
        return $this->parameter('ent')->int()->required();
    }
}
