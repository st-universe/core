<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAirfield;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowAirfieldRequest implements ShowAirfieldRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
