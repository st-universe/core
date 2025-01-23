<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowFighterShipyard;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowFighterShipyardRequest implements ShowFighterShipyardRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
