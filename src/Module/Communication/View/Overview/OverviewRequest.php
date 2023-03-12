<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\Overview;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class OverviewRequest implements OverviewRequestInterface
{
    use CustomControllerHelperTrait;

    public function getKnOffset(): int
    {
        return $this->queryParameter('mark')->int()->defaultsTo(0);
    }

    public function startAtUserMark(): bool
    {
        return $this->queryParameter('user_mark')->boolean()->defaultsTo(false);
    }
}
