<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseMenu;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowLicenseMenuRequest implements ShowLicenseMenuRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTradePostId(): int
    {
        return $this->queryParameter('postid')->int()->required();
    }
}
