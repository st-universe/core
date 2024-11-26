<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowEditKn;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowEditKnRequest implements ShowEditKnRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getKnId(): int
    {
        return $this->parameter('knid')->int()->required();
    }
}
