<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DatabaseEntryRequest implements DatabaseEntryRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCategoryId(): int {
        return $this->queryParameter('cat')->int()->required();
    }

    public function getEntryId(): int {
        return $this->queryParameter('ent')->int()->required();
    }
}