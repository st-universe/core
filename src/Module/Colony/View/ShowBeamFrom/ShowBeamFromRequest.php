<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBeamFrom;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBeamFromRequest implements ShowBeamFromRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    public function getShipId(): int
    {
        return $this->queryParameter('target')->int()->required();
    }

}