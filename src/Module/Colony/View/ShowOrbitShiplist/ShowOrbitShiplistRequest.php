<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitShiplist;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowOrbitShiplistRequest implements ShowOrbitShiplistRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
