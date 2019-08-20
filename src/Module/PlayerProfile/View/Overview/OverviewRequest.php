<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\View\Overview;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class OverviewRequest implements OverviewRequestInterface
{
    use CustomControllerHelperTrait;

    public function getProfileId(): int
    {
        return $this->queryParameter('uid')->int()->required();
    }
}