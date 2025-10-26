<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowTorpedoFab;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowTorpedoFabRequest implements ShowTorpedoFabRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
