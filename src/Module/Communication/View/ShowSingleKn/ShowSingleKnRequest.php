<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowSingleKn;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSingleKnRequest implements ShowSingleKnRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getKnId(): int
    {
        return $this->parameter('knid')->int()->required();
    }
}
