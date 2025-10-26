<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseInfo;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowLicenseInfoRequest implements ShowLicenseInfoRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getTradePostId(): int
    {
        return $this->parameter('postid')->int()->required();
    }
}
