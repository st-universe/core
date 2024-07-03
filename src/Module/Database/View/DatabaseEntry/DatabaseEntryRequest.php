<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DatabaseEntryRequest implements DatabaseEntryRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getCategoryId(): int
    {
        return $this->queryParameter('cat')->int()->required();
    }

    #[Override]
    public function getEntryId(): int
    {
        return $this->queryParameter('ent')->int()->required();
    }
}
