<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\Overview;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class OverviewRequest implements OverviewRequestInterface
{
    use CustomControllerHelperTrait;

    public function getListOffset(): int
    {
        return $this->queryParameter('mark')->int()->defaultsTo(0);
    }

    public function getCategoryId(): int
    {
        return $this->queryParameter('pmcat')->int()->defaultsTo(0);
    }
}
