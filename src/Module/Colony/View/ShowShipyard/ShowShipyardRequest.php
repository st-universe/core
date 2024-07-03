<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipyard;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShipyardRequest implements ShowShipyardRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
