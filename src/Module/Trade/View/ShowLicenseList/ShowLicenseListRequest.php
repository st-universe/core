<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseList;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowLicenseListRequest implements ShowLicenseListRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getTradePostId(): int
    {
        return $this->parameter('postid')->int()->required();
    }
}
