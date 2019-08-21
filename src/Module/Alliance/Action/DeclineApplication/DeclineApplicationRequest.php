<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeclineApplication;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeclineApplicationRequest implements DeclineApplicationRequestInterface
{
    use CustomControllerHelperTrait;

    public function getApplicationId(): int
    {
        return $this->queryParameter('aid')->int()->required();
    }
}