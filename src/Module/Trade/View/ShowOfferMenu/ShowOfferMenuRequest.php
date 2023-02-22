<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferMenu;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowOfferMenuRequest implements ShowOfferMenuRequestInterface
{
    use CustomControllerHelperTrait;

    public function getStorageId(): int
    {
        return $this->queryParameter('storid')->int()->required();
    }
}
