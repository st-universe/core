<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowFighterShipyard;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowFighterShipyardRequest implements ShowFighterShipyardRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
