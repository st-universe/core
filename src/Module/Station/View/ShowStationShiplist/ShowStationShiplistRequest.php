<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationShiplist;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowStationShiplistRequest implements ShowStationShiplistRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getStationId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
