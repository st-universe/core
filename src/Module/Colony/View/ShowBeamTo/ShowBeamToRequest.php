<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBeamTo;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBeamToRequest implements ShowBeamToRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    #[Override]
    public function getShipId(): int
    {
        return $this->queryParameter('target')->int()->required();
    }
}
