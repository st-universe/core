<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowCommoditiesLocations;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowCommoditiesLocationsRequest implements ShowCommoditiesLocationsRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getCommodityId(): int
    {
        return $this->parameter('commodityid')->int()->required();
    }
}
