<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseList;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowLicenseListRequest implements ShowLicenseListRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getTradePostId(): int
    {
        return $this->queryParameter('postid')->int()->required();
    }
}
