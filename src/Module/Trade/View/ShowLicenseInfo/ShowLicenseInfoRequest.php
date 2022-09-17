<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseInfo;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowLicenseInfoRequest implements ShowLicenseInfoRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTradePostId(): int
    {
        return $this->queryParameter('postid')->int()->required();
    }
}
