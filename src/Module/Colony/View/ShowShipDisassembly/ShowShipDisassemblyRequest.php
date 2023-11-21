<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipDisassembly;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShipDisassemblyRequest implements ShowShipDisassemblyRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
