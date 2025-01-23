<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferMenu;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowOfferMenuRequest implements ShowOfferMenuRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getStorageId(): int
    {
        return $this->parameter('storid')->int()->required();
    }
}
