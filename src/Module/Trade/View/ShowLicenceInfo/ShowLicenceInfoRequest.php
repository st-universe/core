<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenceInfo;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowLicenceInfoRequest implements ShowLicenceInfoRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTradePostId(): int
    {
        return $this->queryParameter('postid')->int()->required();
    }
}