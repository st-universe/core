<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipDisassembly;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShipDisassemblyRequest implements ShowShipDisassemblyRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
