<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSystem;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSystemRequest implements ShowSystemRequestInterface
{
    use CustomControllerHelperTrait;

    public function getSystemId(): int
    {
        return $this->queryParameter('sysid')->int()->required();
    }
}