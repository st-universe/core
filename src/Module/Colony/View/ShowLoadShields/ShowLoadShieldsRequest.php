<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowLoadShields;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowLoadShieldsRequest implements ShowLoadShieldsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
