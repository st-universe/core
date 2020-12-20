<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowGoodsLocations;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowGoodsLocationsRequest implements ShowGoodsLocationsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCommodityId(): int
    {
        return $this->queryParameter('commodityId')->int()->required();
    }

}