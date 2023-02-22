<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptApplication;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class AcceptApplicationRequest implements AcceptApplicationRequestInterface
{
    use CustomControllerHelperTrait;

    public function getApplicationId(): int
    {
        return $this->queryParameter('aid')->int()->required();
    }
}
