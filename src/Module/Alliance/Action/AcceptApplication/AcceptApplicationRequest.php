<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptApplication;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class AcceptApplicationRequest implements AcceptApplicationRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getApplicationId(): int
    {
        return $this->parameter('aid')->int()->required();
    }
}
