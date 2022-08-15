<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenceMenu;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowLicenceMenuRequest implements ShowLicenceMenuRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTradePostId(): int
    {
        return $this->queryParameter('tradpid')->int()->required();
    }
}