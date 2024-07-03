<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeclineApplication;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeclineApplicationRequest implements DeclineApplicationRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getApplicationId(): int
    {
        return $this->queryParameter('aid')->int()->required();
    }
}
