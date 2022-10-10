<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowCommoditiesLocations;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowCommoditiesLocationsRequest implements ShowCommoditiesLocationsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCommodityId(): int
    {
        return $this->queryParameter('commodityId')->int()->required();
    }
}
